<?php

namespace App\Utils;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;

class FetchEmails
{
    private EntityManagerInterface $entityManager;
    private string $imapServerAddress;
    private string $imapUser;
    private string $imapPassword;
    private string $smsApiSender;

    public function __construct(string $imapServerAddress, string $imapUser, string $imapPassword, string $smsApiSender, EntityManagerInterface $entityManager)
    {
        $this->imapServerAddress = $imapServerAddress;
        $this->imapUser = $imapUser;
        $this->imapPassword = $imapPassword;
        $this->entityManager = $entityManager;
        $this->smsApiSender = $smsApiSender;
    }

    public function fetchEmails(): void
    {
        $mbox = imap_open($this->imapServerAddress,$this->imapUser,$this->imapPassword)
            or die ('Problem z połączeniem z serwerem: ' . imap_last_error());
        $emails = imap_search($mbox,'UNSEEN');

        if($emails) {
            rsort($emails);

            foreach ($emails as $email) {
                $content = imap_body($mbox, $email);
                $overview = imap_fetch_overview($mbox, $email, 0);

                $date = \DateTime::createFromFormat('D, d M Y H:i:s O T', $overview[0]->date);
                if (!$date) {
                    $date = \DateTime::createFromFormat('D, d M Y H:i:s O', $overview[0]->date);
                }
                $structure = imap_fetchstructure($mbox, $email);
                $from = strstr($overview[0]->from, $this->smsApiSender, false);
                $message = new Message();

                if (isset($structure->parts) || ($from !== $this->smsApiSender)){
                    $message->setContent('Problem z wiadomością, sprawdź serwer poczty');
                    $message->setDate($date);
                    $message->setSender('0');
                } else {
                    $sender = strstr($content, '<strong>Odbiorca:', true);
                    $sender = trim(str_replace('<strong>Nadawca:</strong>', '', $sender), ' ');
                    $messageBody = stristr($content, 'iron', false);
                    $messageBody = trim(str_ireplace('iron', '', $messageBody), ' ');

                    $message->setContent($messageBody);
                    $message->setDate($date);
                    $message->setSender($sender);
                }
                $this->entityManager->persist($message);
                $this->entityManager->flush();
            }
        }
    }
}
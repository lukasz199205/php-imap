<?php

namespace App\Command;

use App\Utils\FetchEmails;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fetch-emails',
    description: 'Run service responisble for download email messages from mail server.',
)]
class FetchEmailsCommand extends Command
{
    private FetchEmails $emails;

    public function __construct(FetchEmails $emails, string $name = null)
    {
        parent::__construct($name);
        $this->emails = $emails;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->emails->fetchEmails();
        $io->success('Wiadomości zostały pomyślnie pobrane z serwera.');

        return Command::SUCCESS;
    }
}
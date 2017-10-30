<?php

namespace App;

use Exception;
use Carbon\Carbon;
use Faker\Factory;
use RuntimeException;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateEmailsCommand extends Command {

    /**
     * Configure the command.
     */
    public function configure()
    {
        $this->setName('emails')
             ->setDescription('Generate Emails')
             ->addArgument('total', InputArgument::REQUIRED)
             ->addArgument('filename', InputArgument::OPTIONAL, 'Filename to use (without extension)', 'emails')
             ->addOption('batch', null, InputOption::VALUE_OPTIONAL, 'Batch emails into number', null);
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $total = (int) $input->getArgument('total');
        $filename = $input->getArgument('filename');
        $batch = $input->getOption('batch');

        $emails = $this->makeEmails($total);
        $this->saveEmails($emails, $filename);

        $output->writeln('Complete - Generated '.$total.' emails');
    }

    protected function makeEmails($total)
    {
        $emails = new Collection;
        $factory = Factory::create('en_GB');

        foreach ( range(1, $total) as $index ) {
            $emails->push($factory->email);
        }

        return $emails;
    }

    protected function saveEmails(Collection $emails, $filename, $batch = null)
    {
        $path = __DIR__.'/../';

        if ( $batch === null ) {
            $this->saveEmailsToFile($emails, $path.$filename.'.txt');
            return;
        }

        foreach ( $emails->chunk($batch) as $index => $chunk ) {
            $suffix = '_' . ( $index + 1 ) . '.txt';
            $this->saveEmailsToFile($chunk, $path.$filename.$suffix);
        }
    }

    protected function saveEmailsToFile(Collection $emails, $path)
    {
        file_put_contents($path, $emails->implode("\n"));
    }

}

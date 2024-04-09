<?php
/**
 * Created by PhpStorm.
 * User: DINARI
 * Date: 08/04/2024
 * Time: 04:40
 */

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\ImportCsvMessage;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Input\InputArgument;
use SplFileObject;

class ImportCsvCommand extends Command
{
    private $messageBus;
    private $kernel;

    public function __construct(MessageBusInterface $messageBus, KernelInterface $kernel)
    {
        parent::__construct();

        $this->messageBus = $messageBus;
        $this->kernel = $kernel;
    }

    protected function configure()
    {
        $this
            ->setName('app:import-csv')
            ->setDescription('Import CSV file')
            ->addArgument('file', InputArgument::REQUIRED, 'Path to the CSV file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $csvFilePath = $input->getArgument('file');

        if (!file_exists($csvFilePath)) {
            $output->writeln("Le fichier CSV $csvFilePath n'existe pas.");
            return Command::FAILURE;
        }

        $csvData = $this->readCsvFile($csvFilePath);

        // Ici pour envoyer les données CSV au bus de messages
        $this->messageBus->dispatch(new ImportCsvMessage($csvData));

        $output->writeln('Importation du fichier CSV terminée avec succès.');

        return Command::SUCCESS;
    }

    private function readCsvFile(string $csvFilePath): array
    {
        $csvData = [];
        $file = new SplFileObject($csvFilePath, 'r');

        while (!$file->eof()) {
            $csvData[] = $file->fgetcsv();
        }

        return $csvData;
    }
}


<?php
/**
 * Created by PhpStorm.
 * User: DINARI
 * Date: 08/04/2024
 * Time: 04:40
 */
namespace App\MessageHandler;

use App\Message\ImportCsvMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ImportCsvMessageHandler implements MessageHandlerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(ImportCsvMessage $message)
    {
        $csvData = $message->getCsvData();

        // TODO Traitement des données du fichier CSV et insertion dans la base de données

    }
}
<?php
/**
 * Created by PhpStorm.
 * User: DINARI
 */
namespace App\Message;

class ImportCsvMessage
{
    private $csvData;

    public function __construct(array $csvData)
    {
        $this->csvData = $csvData;
    }

    public function getCsvData(): array
    {
        return $this->csvData;
    }
}

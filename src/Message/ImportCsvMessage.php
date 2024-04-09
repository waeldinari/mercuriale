<?php
/**
 * Created by PhpStorm.
 * User: DINARI
 * Date: 08/04/2024
 * Time: 04:40
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

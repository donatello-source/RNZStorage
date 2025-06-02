<?php

namespace App\Tests\Service;

use App\Entity\Quote;
use App\Entity\Company;
use App\Entity\QuoteDate;
use App\Entity\QuoteTable;
use App\Entity\QuoteTableEquipment;
use App\Entity\Equipment;
use App\Service\QuoteExportService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPUnit\Framework\TestCase;

class QuoteExportServiceTest extends TestCase
{
    public function testGenerateXlsxCreatesFileWithBasicData(): void
    {
        $company = new Company();
        $company->setNazwa('Test Company');

        $quote = new Quote();
        $quote->setCompany($company);
        $quote->setProjekt('Test Projekt');
        $quote->setLokalizacja('Warszawa');
        $quote->setGlobalDiscount(5);

        $date = new QuoteDate();
        $date->setValue('2024-06-01');
        $date->setComment('Test data');

        $equipment = new Equipment();
        $equipment->setName('Laptop');
        $equipment->setPrice(1000);
        $equipment->setPricingInfo('Cena za dzień');

        $table = new QuoteTable();
        $table->setLabel('Sala A');
        $table->setDiscount(10);

        $qte = new QuoteTableEquipment();
        $qte->setEquipment($equipment);
        $qte->setCount(2);
        $qte->setDays(3);
        $qte->setDiscount(5);
        $qte->setShowComment(true);


        $outputPath = sys_get_temp_dir() . '/test_quote.xlsx';

        $service = new QuoteExportService();
        $service->generateXlsx($quote, $outputPath);

        $this->assertFileExists($outputPath);

        $spreadsheet = IOFactory::load($outputPath);
        $sheet = $spreadsheet->getActiveSheet();

        $this->assertEquals('ZAMAWIAJĄCY', $sheet->getCell('B1')->getValue());
        $this->assertEquals('Test Company', $sheet->getCell('E1')->getValue());
        $this->assertEquals('Test Projekt', $sheet->getCell('E2')->getValue());
        $this->assertEquals('Warszawa', $sheet->getCell('E3')->getValue());

        unlink($outputPath);
    }
}
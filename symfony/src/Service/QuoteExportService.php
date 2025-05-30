<?php
namespace App\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Entity\Quote;

class QuoteExportService
{
    public function generateXlsx(Quote $quote, string $outputPath): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Style
        $blueFill = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => '1976D2'],
            ],
            'font' => [
                'color' => ['rgb' => 'FFFFFF'],
                'bold' => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ];
        $redFill = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => 'D32F2F'],
            ],
            'font' => [
                'color' => ['rgb' => 'FFFFFF'],
                'bold' => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ];
        $border = [
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ];

        // A1:A? - białe, bez obramowania (nie musisz ustawiać, domyślnie są białe)

        // B1:D1 - ZAMAWIAJĄCY
        $sheet->mergeCells('B1:D1');
        $sheet->setCellValue('B1', 'ZAMAWIAJĄCY');
        $sheet->getStyle('B1:D1')->applyFromArray($blueFill);
        $sheet->setCellValue('E1', $quote->getCompany()?->getNazwa() ?? '');
        $sheet->getStyle('E1')->applyFromArray($border);

        // B2:D2 - Projekt
        $sheet->mergeCells('B2:D2');
        $sheet->setCellValue('B2', 'Projekt');
        $sheet->getStyle('B2:D2')->applyFromArray($blueFill);
        $sheet->setCellValue('E2', $quote->getProjekt());
        $sheet->getStyle('E2')->applyFromArray($border);

        // B3:D3 - Lokalizacja
        $sheet->mergeCells('B3:D3');
        $sheet->setCellValue('B3', 'LOKALIZACJA');
        $sheet->getStyle('B3:D3')->applyFromArray($blueFill);
        $sheet->setCellValue('E3', $quote->getLokalizacja());
        $sheet->getStyle('E3')->applyFromArray($border);

        // Daty
        $row = 4;
        foreach ($quote->getDates() as $date) {
            $sheet->mergeCells("B{$row}:D{$row}");
            $sheet->setCellValue("B{$row}", 'DATA');
            $sheet->getStyle("B{$row}:D{$row}")->applyFromArray($blueFill);
            $sheet->setCellValue("E{$row}", $date->getValue() . ($date->getComment() ? ' - ' . $date->getComment() : ''));
            $sheet->getStyle("E{$row}")->applyFromArray($border);
            $row++;
        }

        // Przerwa
        $sheet->getStyle("A{$row}:K{$row}")->getFill()->setFillType('none');
        $row++;

        // Pasek z nazwą projektu
        $sheet->mergeCells("B{$row}:K{$row}");
        $sheet->setCellValue("B{$row}", $quote->getProjekt());
        $sheet->getStyle("B{$row}:K{$row}")->applyFromArray($blueFill);
        $row++;

        // Przerwa
        $sheet->getStyle("B{$row}:K{$row}")->getFill()->setFillType('none');
        $row++;

        // Nagłówek tabeli sprzętu
        $sheet->setCellValue("B{$row}", 'Kategoria');
        $sheet->setCellValue("C{$row}", 'Lp.');
        $sheet->setCellValue("D{$row}", '');
        $sheet->setCellValue("E{$row}", 'Opis');
        $sheet->setCellValue("F{$row}", 'Liczba');
        $sheet->setCellValue("G{$row}", 'Dni');
        $sheet->setCellValue("H{$row}", 'Cena jedn.');
        $sheet->setCellValue("I{$row}", 'Łącznie');
        $sheet->setCellValue("J{$row}", 'Rabat');
        $sheet->setCellValue("K{$row}", 'Koszt');
        $sheet->getStyle("B{$row}:K{$row}")->applyFromArray($blueFill);
        $row++;

        // Tabelki sprzętu
        $lp = 1;
        $sumNetto = 0;
        $sumNettoPoRabacie = 0;
        $sumBrutto = 0;
        foreach ($quote->getTables() as $table) {
            $tableSum = 0;
            foreach ($table->getEquipments() as $item) {
                $price = $item->getEquipment()->getPrice();
                $count = $item->getCount();
                $days = $item->getDays();
                $discountItem = $item->getDiscount() ?? 0;
                $discountTable = $table->getDiscount() ?? 0;

                $total = $price * $count * $days;
                $cost = $total * (1 - $discountItem / 100) * (1 - $discountTable / 100);

                $sheet->setCellValue("B{$row}", $table->getLabel());
                $sheet->getStyle("B{$row}")->applyFromArray($border);
                $sheet->setCellValue("C{$row}", $lp++);
                $sheet->getStyle("C{$row}")->applyFromArray($border);
                $sheet->setCellValue("E{$row}", $item->getEquipment()->getName());
                $sheet->getStyle("E{$row}")->applyFromArray($border);
                $sheet->setCellValue("F{$row}", $count);
                $sheet->getStyle("F{$row}")->applyFromArray($border);
                $sheet->setCellValue("G{$row}", $days);
                $sheet->getStyle("G{$row}")->applyFromArray($border);
                $sheet->setCellValue("H{$row}", $price);
                $sheet->getStyle("H{$row}")->applyFromArray($border);
                $sheet->setCellValue("I{$row}", $total);
                $sheet->getStyle("I{$row}")->applyFromArray($border);
                $sheet->setCellValue("J{$row}", $discountItem ? $discountItem . '%' : '');
                $sheet->setCellValue("K{$row}", $cost);
                $sheet->getStyle("K{$row}")->applyFromArray($border);

                $tableSum += $cost;
                $row++;
            }
            // Podsumowanie tabelki
            $sheet->mergeCells("B{$row}:I{$row}");
            $sheet->setCellValue("J{$row}", $table->getDiscount() ? $table->getDiscount() . '%' : '');
            $sheet->setCellValue("K{$row}", $tableSum);
            $sheet->getStyle("B{$row}:K{$row}")->applyFromArray($blueFill);
            $row++;
            // Przerwa
            $sheet->getStyle("B{$row}:K{$row}")->getFill()->setFillType('none');
            $row++;

            $sumNetto += $tableSum;
        }

        // Rabat całościowy
        $globalDiscount = $quote->getGlobalDiscount() ?? 0;
        $sumNettoPoRabacie = $sumNetto * (1 - $globalDiscount / 100);
        $sumBrutto = $sumNettoPoRabacie * 1.23;

        // Przerwa i rabat całościowy
        $sheet->getStyle("B{$row}:K{$row}")->getFill()->setFillType('none');
        if ($globalDiscount > 0) {
            $sheet->mergeCells("I{$row}:J{$row}");
            $sheet->setCellValue("I{$row}", "RABAT");
            $sheet->getStyle("I{$row}:J{$row}")->applyFromArray($redFill);
            $sheet->setCellValue("K{$row}", $globalDiscount . '%');
            $sheet->getStyle("K{$row}")->applyFromArray($border);
        }
        $row++;

        // Suma netto
        $sheet->mergeCells("I{$row}:J{$row}");
        $sheet->setCellValue("I{$row}", "NETTO");
        $sheet->getStyle("I{$row}:J{$row}")->applyFromArray($redFill);
        $sheet->setCellValue("K{$row}", $sumNetto);
        $sheet->getStyle("K{$row}")->applyFromArray($border);
        $row++;

        // Suma netto po rabacie lub netto z VAT
        $sheet->mergeCells("I{$row}:J{$row}");
        if ($globalDiscount > 0) {
            $sheet->setCellValue("I{$row}", "NETTO PO RABACIE");
            $sheet->getStyle("I{$row}:J{$row}")->applyFromArray($redFill);
            $sheet->setCellValue("K{$row}", $sumNettoPoRabacie);
            $sheet->getStyle("K{$row}")->applyFromArray($border);
            $row++;
            // Suma brutto
            $sheet->mergeCells("I{$row}:J{$row}");
            $sheet->setCellValue("I{$row}", "NETTO Z VAT");
            $sheet->getStyle("I{$row}:J{$row}")->applyFromArray($redFill);
            $sheet->setCellValue("K{$row}", $sumBrutto);
            $sheet->getStyle("K{$row}")->applyFromArray($border);
            $row++;
        } else {
            $sheet->setCellValue("I{$row}", "NETTO Z VAT");
            $sheet->getStyle("I{$row}:J{$row}")->applyFromArray($redFill);
            $sheet->setCellValue("K{$row}", $sumBrutto);
            $sheet->getStyle("K{$row}")->applyFromArray($border);
            $row++;
        }

        // Przerwa
        $sheet->getStyle("B{$row}:K{$row}")->getFill()->setFillType('none');
        $row++;

        // DODATKOWE INFORMACJE
        $sheet->setCellValue("B{$row}", "DODATKOWE INFORMACJE");
        $row++;
        // Wyświetl tylko sprzęty z widocznością showComment = true i pricing_info
        $infoRow = $row;
        foreach ($quote->getTables() as $table) {
            foreach ($table->getEquipments() as $item) {
                if ($item->isShowComment() && $item->getEquipment()->getPricingInfo()) {
                    $sheet->setCellValue("B{$infoRow}", $item->getEquipment()->getName() . ' - ' . $item->getEquipment()->getPricingInfo());
                    $infoRow++;
                }
            }
        }

        // Zapisz plik
        $writer = new Xlsx($spreadsheet);
        $writer->save($outputPath);
    }
}
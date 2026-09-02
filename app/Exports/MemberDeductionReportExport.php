<?php

namespace App\Exports;

use App\Models\Branch;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class MemberDeductionReportExport implements FromView, ShouldAutoSize, WithEvents
{
    public function __construct(
        protected array $report,
        protected Branch $branch,
        protected int $month,
        protected int $year
    ) {
    }

    public function view(): View
    {
        return view('reports.member-deductions.excel', [
            'report' => $this->report,
            'branch' => $this->branch,
            'month' => $this->month,
            'year' => $this->year,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $sheet->freezePane('C6');
                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A3)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 5);
                $sheet->getPageMargins()
                    ->setTop(0.35)
                    ->setRight(0.2)
                    ->setBottom(0.35)
                    ->setLeft(0.2)
                    ->setHeader(0.15)
                    ->setFooter(0.15);
                $sheet->getHeaderFooter()->setOddFooter('&L' . config('app.name', 'Koperasi') . '&RHalaman &P dari &N');

                $sheet->getStyle("A1:S{$lastRow}")->getFont()->setName('Arial')->setSize(9);
                $sheet->getStyle('A1:S3')->getFont()->setBold(true);
                $sheet->getStyle('A1:S3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('A4:S5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 8],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E2E8F0'],
                    ],
                ]);

                $sheet->getStyle("A4:S{$lastRow}")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('64748B');
                $sheet->getStyle("C6:S{$lastRow}")->getNumberFormat()
                    ->setFormatCode('#,##0');
                $sheet->getStyle("A6:S{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A6:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("H6:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("N6:N{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                foreach (['A' => 6, 'B' => 28, 'C' => 13, 'D' => 13, 'E' => 13,
                    'F' => 15, 'G' => 13, 'H' => 7, 'I' => 13, 'J' => 15, 'K' => 15,
                    'L' => 15, 'M' => 13, 'N' => 7, 'O' => 13, 'P' => 15, 'Q' => 15,
                    'R' => 16, 'S' => 18] as $column => $width) {
                    $sheet->getColumnDimension($column)->setAutoSize(false)->setWidth($width);
                }

                $sheet->getRowDimension(4)->setRowHeight(30);
                $sheet->getRowDimension(5)->setRowHeight(30);
                $sheet->getSheetView()->setZoomScale(75);
            },
        ];
    }
}

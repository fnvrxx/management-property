<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InvoicesSummaryExport implements WithMultipleSheets
{
    public function __construct(
        private ?array $ids = null,
        private ?int   $year = null,
    ) {}

    public function sheets(): array
    {
        return [
            new InvoicesExport($this->ids, $this->year),
            new InvoicesSummarySheetExport($this->ids, $this->year),
        ];
    }
}

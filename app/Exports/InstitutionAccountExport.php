<?php

namespace App\Exports;

use App\Models\Account;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InstitutionAccountExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Account::query()
            ->join("institutions", "accounts.institution_id", '=', "institutions.id")
            ->select("accounts.id", 'institution_name', "balance", "accounts.created_at", "accounts.updated_at")
            ->orderBy("accounts.updated_at", "DESC")
            ->get();
    }

    public function headings(): array
    {
        return [
            "ID",
            "Institution",
            "Available Balance",
            "Created At",
            "Updated At"
        ];
    }
}

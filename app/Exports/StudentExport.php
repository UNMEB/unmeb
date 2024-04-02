<?php

namespace App\Exports;

use App\Models\Student;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Support\Facades\DB;

class StudentExport implements FromQuery, ShouldQueue
{
    use Exportable;

    protected $institutionId;

    public function __construct($institutionId)
    {
        $this->institutionId = $institutionId;
    }

    public function query()
    {
        // return Student::withoutGlobalScopes()
        //     ->where('institution_id', $this->institutionId);

        $query = Student::withoutGlobalScopes();

        if ($this->institutionId !== null) {
            $query->where('institution_id', $this->institutionId);
        }

        return $query;
    }
}

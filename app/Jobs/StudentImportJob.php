<?php

namespace App\Jobs;

use App\Events\StudentImportCompletedEvent;
use App\Imports\StudentImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;

class StudentImportJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    private $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function handle()
    {
        Excel::import(new StudentImport, $this->filePath, null, ExcelExcel::CSV);
    }
}

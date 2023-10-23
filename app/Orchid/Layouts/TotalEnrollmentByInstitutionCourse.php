<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Layouts\Chart;

class TotalEnrollmentByInstitutionCourse extends Chart
{
    /**
     * Available options:
     * 'bar', 'line',
     * 'pie', 'percentage'.
     *
     * @var string
     */
    protected $type = 'bar';

    /**
     * Determines whether to display the export button.
     *
     * @var bool
     */
    protected $export = true;
}

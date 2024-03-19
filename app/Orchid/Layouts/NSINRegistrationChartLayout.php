<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Layouts\Chart;

class NSINRegistrationChartLayout extends Chart
{
    /**
     * Available options:
     * 'bar', 'line',
     * 'pie', 'percentage'.
     *
     * @var string
     */
    protected $type = self::TYPE_BAR;

    /**
     * Determines whether to display the export button.
     *
     * @var bool
     */
    protected $export = true;
}

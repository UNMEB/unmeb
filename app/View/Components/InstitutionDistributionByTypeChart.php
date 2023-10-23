<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InstitutionDistributionByTypeChart extends Component
{

    public $institution_distribution_by_type;

    /**
     * Create a new component instance.
     */
    public function __construct($institution_distribution_by_type)
    {
        $this->institution_distribution_by_type = $institution_distribution_by_type;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.institution-distribution-by-type-chart');
    }
}

<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InstitutionDistributionByCategoryChart extends Component
{

    public $institution_distribution_by_category;

    /**
     * Create a new component instance.
     */
    public function __construct($institution_distribution_by_category)
    {
        $this->institution_distribution_by_category = $institution_distribution_by_category;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.institution-distribution-by-category-chart');
    }
}

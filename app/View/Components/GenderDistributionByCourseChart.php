<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class GenderDistributionByCourseChart extends Component
{
    public $gender_distribution_by_course;

    /**
     * Create a new component instance.
     */
    public function __construct($gender_distribution_by_course)
    {
        $this->gender_distribution_by_course = $gender_distribution_by_course;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.gender-distribution-by-course-chart');
    }
}

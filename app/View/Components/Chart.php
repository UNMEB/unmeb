<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Chart extends Component
{
    public $categories;
    public $series;

    public function __construct($categories, $series)
    {
        $this->series = $series;
        $this->categories = $categories;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return <<<'blade'
                    <livewire:chart :categories="$categories" :series="$series" />
                    blade;
    }
}

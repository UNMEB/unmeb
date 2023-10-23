<?php

namespace App\Livewire;

use Livewire\Component;

class Chart extends Component
{

    public $categories;
    public $series;

    public function mount($categories, $series)
    {
        $this->categories = $categories;
        $this->series = $series;
    }

    public function render()
    {
        return view('livewire.chart');
    }
}

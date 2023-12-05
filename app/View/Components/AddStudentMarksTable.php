<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AddStudentMarksTable extends Component
{
    public $students;
    public $paper_type;

    /**
     * Create a new component instance.
     */
    public function __construct($students, $paper_type)
    {
        $this->students = $students;
        $this->paper_type = $paper_type;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.add-student-marks-table');
    }
}

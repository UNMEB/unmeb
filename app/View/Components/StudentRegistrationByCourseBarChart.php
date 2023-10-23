<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StudentRegistrationByCourseBarChart extends Component
{

    public $student_registration_by_course;

    /**
     * Create a new component instance.
     */
    public function __construct($student_registration_by_course)
    {
        $this->student_registration_by_course = $student_registration_by_course;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.student-registration-by-course-bar-chart');
    }
}

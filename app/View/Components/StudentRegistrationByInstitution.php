<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StudentRegistrationByInstitution extends Component
{
    public $student_registration_by_institution;
    /**
     * Create a new component instance.
     */
    public function __construct($student_registration_by_institution)
    {
        $this->student_registration_by_institution = $student_registration_by_institution;
    }


    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.student-registration-by-institution');
    }
}

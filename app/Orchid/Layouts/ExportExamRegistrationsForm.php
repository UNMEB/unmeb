<?php

namespace App\Orchid\Layouts;

use Illuminate\Http\Request;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;

class ExportExamRegistrationsForm extends Listener
{
    public $courses = [];
    public $papers = [];

    public $institution = null;
    public $course;




    /**
     * List of field names for which values will be listened.
     *
     * @var string[]
     */
    protected $targets = [];

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    protected function layouts(): iterable
    {
        return [];
    }

    /**
     * Update state
     *
     * @param \Orchid\Screen\Repository $repository
     * @param \Illuminate\Http\Request  $request
     *
     * @return \Orchid\Screen\Repository
     */
    public function handle(Repository $repository, Request $request): Repository
    {
        return $repository;
    }
}

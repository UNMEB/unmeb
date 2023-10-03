<?php

namespace App\Orchid\Layouts;

use App\Models\ExamRegistration;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ApproveExamRegistrationListener extends Listener
{
    /**
     * List of field names for which values will be listened.
     *
     * @var string[]
     */
    protected $targets = [
        'fields'
    ];

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    protected function layouts(): iterable
    {
        return [
            Layout::table('registrations', [
                TD::make()
                    ->render(
                        fn (ExamRegistration $data) => CheckBox::make('fields[]')
                            ->value($data->id)
                            ->checked(false)
                    )
                    ->width(50),
            ]),
        ];
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
        dd($request->all());

        return $repository;
    }
}

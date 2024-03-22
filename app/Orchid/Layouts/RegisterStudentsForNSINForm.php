<?php

namespace App\Orchid\Layouts;

use App\Models\Student;
use Illuminate\Http\Request;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class RegisterStudentsForNSINForm extends Listener
{

    public $students;

    public function __construct(Request $request)
    {
    }

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
        $user = auth()->user();
        $institution = $user->institution; // Assuming 'institution' is the method defining the BelongsTo relationship

        if ($institution) {
            $balance = $institution->account->balance;
        } else {
            // Handle the case where user is not associated with any institution
            $balance = null; // Or provide a default value
        }

        return [
            Layout::view('components.register-students-for-n-s-i-n-form', [
                'students' => $this->students,
                'balance' => "UGX " . number_format($balance, 0),
                'institution' => $institution->institution_name
            ])
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
        return $repository;
    }
}

<?php

namespace App\Orchid\Screens;

use App\Models\RegistrationReport;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

use Orchid\Platform\Http\Middleware\Turbo;


class DemoReportScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $data = RegistrationReport::query()
            ->paginate();

        dd($data);

        return [];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'DemoReportScreen';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        $data = [
            [
                'Code' => '1',
                'Center' => 'UNMEB',
                'Course1' => ['P1' => 78, 'P2' => 45, 'P3' => 76],
                'Course2' => ['P1' => 21, 'P2' => 44, 'P3' => 99],
                'Course3' => ['P1' => 23, 'P2' => 66, 'P3' => 11],
            ],
            [
                'Code' => '2',
                'Center' => 'UNMC',
                'Course1' => ['P1' => 56, 'P2' => 45, 'P3' => 11],
                'Course2' => ['P1' => 21, 'P2' => 22, 'P3' => 55],
                'Course3' => ['P1' => 0, 'P2' => 67, 'P3' => 11],
            ],
            // Add more data as needed
        ];


        return [
            Layout::view('demo_report', [
                'data' => $data
            ])
        ];
    }
}

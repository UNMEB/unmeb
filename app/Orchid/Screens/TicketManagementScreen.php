<?php

namespace App\Orchid\Screens;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Illuminate\Http\Request;

class TicketManagementScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        // Get total tickets count
        $totalTicketsCount = Ticket::count();

        // Get open tickets count
        $openTicketsCount = Ticket::where('status_id', function ($query) {
            $query->select('id')
                ->from('ticket_statuses')
                ->where('name', 'open');
        })->count();

        // Get closed tickets count
        $closedTicketsCount = Ticket::where('status_id', function ($query) {
            $query->select('id')
                ->from('ticket_statuses')
                ->where('name', 'closed');
        })->count();

        // Calculate percentage difference
        $openTicketsDiff = 0; // Default value if denominator is zero
        if ($closedTicketsCount != 0) {
            $openTicketsDiff = ($openTicketsCount - $closedTicketsCount) / $closedTicketsCount * 100;
        }

        return [
            'tickets' => Ticket::paginate(),
            'ticket_statuses' => TicketStatus::paginate(),
            'ticket_priorities' => TicketPriority::paginate(),
            'ticket_categories' => TicketCategory::paginate(),
            'metrics' => [
                'total_tickets' => ['value' => number_format($totalTicketsCount), 'diff' => $openTicketsDiff],
                'open_tickets' => ['value' => number_format($openTicketsCount), 'diff' => $openTicketsDiff],
                'closed_tickets' => ['value' => number_format($closedTicketsCount), 'diff' => 0],
            ],
        ];
    }


    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Ticket Manager';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            ModalToggle::make('New Status')
                ->modal('newStatusModal')
                ->modalTitle('Create New Status')
                ->class('btn btn-sm btn-primary')
                ->method('save', [
                    'action' => 'status'
                ]),

            ModalToggle::make('New Category')
                ->modal('newCategoryModal')
                ->modalTitle('Create New Category')
                ->class('btn btn-sm btn-primary')
                ->method('save', [
                    'action' => 'category'
                ]),

            ModalToggle::make('New Priority')
                ->modal('newPriorityModal')
                ->modalTitle('Create New Priority')
                ->class('btn btn-sm btn-primary')
                ->method('save', [
                    'action' => 'priority'
                ]),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::metrics([
                'Total Tickets' => 'metrics.total_tickets',
                'Open Tickets' => 'metrics.open_tickets',
                'Closed Tickets' => 'metrics.closed_tickets'
            ]),
            Layout::tabs([
                'Support Requests' => [

                    Layout::rows([

                    ]),
                    Layout::table('tickets', [
                        TD::make('id', 'ID'),
                        TD::make('subject', 'Subject'),
                        TD::make('status', 'Status'),
                        TD::make('priority', 'Priority'),
                        TD::make('updated_at', 'Last Updated At'),
                    ])
                ],
                'Configurations' => [
                    Layout::table('ticket_statuses', [
                        TD::make('id', 'ID'),
                        TD::make('name', 'Name'),
                        TD::make('color', 'Color'),
                        TD::make('created_at', 'Created At')
                            ->usingComponent(DateTimeSplit::class),
                        TD::make('updated_at', 'Updated At')
                            ->usingComponent(DateTimeSplit::class),
                        TD::make('actions', 'Action'),

                    ])->title('Ticket Statuses'),

                    Layout::table('ticket_categories', [
                        TD::make('id', 'ID'),
                        TD::make('name', 'Name'),
                        TD::make('color', 'Color'),
                        TD::make('created_at', 'Created At')
                            ->usingComponent(DateTimeSplit::class),
                        TD::make('updated_at', 'Updated At')
                            ->usingComponent(DateTimeSplit::class),
                        TD::make('actions', 'Action'),
                    ])->title('Ticket Categories'),

                    Layout::table('ticket_priorities', [
                        TD::make('id', 'ID'),
                        TD::make('name', 'Name'),
                        TD::make('color', 'Color'),
                        TD::make('created_at', 'Created At')
                            ->usingComponent(DateTimeSplit::class),
                        TD::make('updated_at', 'Updated At')
                            ->usingComponent(DateTimeSplit::class),
                        TD::make('actions', 'Action'),
                    ])->title('Ticket Priorities')

                    ,
                ]
            ]),

            Layout::modal('newStatusModal', Layout::rows([
                Input::make('name')->title('Status Name')->type('text'),

                Input::make('color')->title('Status Color')->type('color'),

            ]))->applyButton('Save'),

            Layout::modal('newCategoryModal', Layout::rows([
                Input::make('name')->title('Category Name')->type('text'),

                Input::make('color')->title('Category Color')->type('color'),
            ]))->applyButton('Save'),

            Layout::modal('newPriorityModal', Layout::rows([
                Input::make('name')->title('Priority Name')->type('text'),
                Input::make('color')->title('Priority Color')->type('color'),
            ]))->applyButton('Save'),
        ];
    }

    public function save(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'color' => 'required',
        ]);

        $action = $request->get('action');

        if ($action == 'status') {
            TicketStatus::create($request->only('name', 'color'));

            Alert::success('Ticket Status Saved');
        } else if ($action == 'category') {
            TicketCategory::create($request->only('name', 'color'));

            Alert::success('Ticket Category Saved');
        } else if ($action == 'priority') {
            TicketPriority::create($request->only('name', 'color'));

            Alert::success('Ticket Priority Saved');
        } else {
            Alert::error("Unable to complete request at the moment");
        }
    }
}

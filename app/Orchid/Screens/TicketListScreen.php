<?php

namespace App\Orchid\Screens;

use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class TicketListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = Ticket::query();
        return [
            'tickets' => $query->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Support Ticket';
    }


    public function description(): string|null
    {
        return 'View support queries, follow up and issue new support requests or comments';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            Link::make('Request Support')
                ->class('btn btn-sm btn-success')
                ->route('platform.tickets.new')
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
            Layout::rows([
                Group::make([

                ])
            ]),

            Layout::table('tickets', [
                TD::make('id', 'ID'),
                TD::make('subject', 'Subject'),
                TD::make('status', 'Status'),
                TD::make('priority', 'Priority'),
                TD::make('updated_at', 'Last Updated At'),
                TD::make('actions', 'Actions')
                    ->render(
                        fn(Ticket $ticket) => DropDown::make()
                            ->icon('bs.three-dots-vertical')
                            ->list([
                                Link::make(__('Details'))
                                    ->route('platform.tickets.response', $ticket->id)
                                    ->icon('bs.eye'),

                            ])
                    ),
            ])
        ];
    }

    public function newRequest(Request $request): RedirectResponse
    {
        $url = route('platform.tickets.new', []);
        return redirect()->to($url);
    }


}

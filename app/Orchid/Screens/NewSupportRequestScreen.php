<?php

namespace App\Orchid\Screens;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use Auth;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use RealRashid\SweetAlert\Facades\Alert;

class NewSupportRequestScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'New Support Request';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
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
        return [
            Layout::block([
                Layout::rows([

                    Input::make('subject')
                        ->title('Subject')
                        ->type('text')
                        ->help('Subject of your issue'),

                    Quill::make('content')
                        ->toolbar(["text", "color", "header", "list", "format", "media"])
                        ->title('Content')
                        ->help('How may we assist you'),

                    Relation::make('priority_id')
                        ->fromModel(TicketPriority::class, 'name')
                        ->title('Select priority'),

                    Relation::make('category_id')
                        ->fromModel(TicketCategory::class, 'name')
                        ->title('Select category')


                ]),
            ])
                ->title('New Support Request')
                ->description('Use the form provided here to log support requests for any issue regarding the UNMEB System')
                ->commands(
                    Button::make('Save')
                        ->class('btn btn-sm btn-success')
                        ->icon('bs.check-circle')
                        ->method('save')
                )
        ];
    }

    public function save(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'subject' => 'required|string|max:255|min:3',
            'content' => 'required|string|min:10',
            'priority_id' => 'required|exists:ticket_priorities,id',
            'category_id' => 'required|exists:ticket_categories,id',
        ]);

        $ticket = new Ticket();
        $ticket->subject = $request->subject;
        $ticket->priority_id = $request->priority_id;
        $ticket->category_id = $request->category_id;
        $ticket->status_id = TicketStatus::firstWhere('name', 'Pending');
        $ticket->content = $request->content;
        $ticket->user_id = auth()->user()->id;

        // Set the agent ID
        $ticket->autoSelectAgent();

        $ticket->save();

        Alert::success('Action Completed', 'Your support ticket has been logged. You\'ll be notifed when its resolved by an admin');

        redirect()->back();
    }
}


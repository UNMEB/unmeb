<?php

namespace App\Orchid\Screens;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use RealRashid\SweetAlert\Facades\Alert;

class TicketResponseScreen extends Screen
{

    /**
     * @var Ticket
     */
    public $ticket;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Ticket $ticket): iterable
    {
        $ticket->load(['comments']);
        return [
            'ticket' => $ticket,
            'statuses' => TicketStatus::all(),
            'priorities' => TicketPriority::all(),
            'categories' => TicketCategory::all()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->ticket->exists ? 'Support Request #' . $this->ticket->id : 'New Support Request';
    }


    public function description(): string|null
    {
        return $this->ticket->exists ? $this->ticket->subject : null;
    }


    public function permission(): array|\Traversable|null
    {
        return [];
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            ModalToggle::make('Assign User')
                ->modalTitle('Assign ticket to Agent')
                ->modal('assignUser')
                ->method('assign', [
                    'ticket' => $this->ticket->id,
                ])
                ->canSee($this->ticket->exists)
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
            Layout::view('support_ticket_comments'),
            Layout::modal('assignUser', [
                Layout::rows([
                    Relation::make('user_id')
                        ->title('Assign Agent')
                        ->placeholder('Select User to assign this ticket')
                        ->fromModel(User::class, 'name')
                        ->applyScope('userCanResolveTickets')
                        ->required()
                ])
            ])
        ];
    }

    public function submit(Request $request)
    {
        if ($this->ticket->exists) {

            // Add a new comment for this particular ticket

        } else {
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
            $ticket->status_id = TicketStatus::where('name', 'Pending')->first()->id;
            $ticket->content = $request->content;
            $ticket->user_id = auth()->user()->id;

            // Set the agent ID
            $ticket->autoSelectAgent();

            $ticket->save();


            // Alert::success('Action Completed', 'Your support ticket has been logged. You\'ll be notifed when its resolved by an admin')->persistent(true, false);

            return redirect(route('platform.tickets.response', $ticket->id))->with('success', 'Your support ticket has been logged. You\'ll be notifed when its resolved by an admin');
        }


    }

    public function assign(Request $request, Ticket $ticket)
    {
        if (!$ticket->exists) {
            return;
        }

        dd($request->all());

        $agentId = $request->get('user_id');

        $ticket->agent_id = $agentId;
        $ticket->save();
    }
}

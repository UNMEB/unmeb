<?php

namespace App\Orchid\Screens;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

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
            Layout::view('support_ticket_comments'),
        ];
    }

    public function submit(Request $request)
    {
        $request->validate([
            'reply' => 'required'
        ]);
    }
}

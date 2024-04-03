<?php

namespace App\Orchid\Screens;

use App\Models\Ticket;
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
            'ticket' => $ticket
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Support Request #' . $this->ticket->id;
    }


    public function description(): string|null
    {
        return $this->ticket ? $this->ticket->subject : null;
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

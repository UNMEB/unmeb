<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;

class AutoAssignAgentObserver
{
    /**
     * Handle the Ticket "created" event
     */
    public function created(Ticket $ticket)
    {
        $category = TicketCategory::find($ticket->category_id);

        if ($category) {
            $agents = $category->agents()->withCount('agentOpenTickets')->get();

            $selectedAgent = null;
            $lowestTicketCount = PHP_INT_MAX;

            foreach ($agents as $agent) {
                $ticketCount = $agent->agentOpenTickets_count;

                if ($ticketCount < $lowestTicketCount) {
                    $lowestTicketCount = $ticketCount;
                    $selectedAgent = $agent;
                }
            }

            if ($selectedAgent) {
                $ticket->agent_id = $selectedAgent->id;
                $ticket->save();
            } else {
                // If no suitable agent found, you might want to handle this case accordingly
            }
        } else {
            // If no category found for the ticket, you might want to handle this case accordingly
        }
    }
}

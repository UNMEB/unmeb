<?php

namespace App\Models;

use App\Models\Scopes\InstitutionScope;
use Date;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Platform\Models\Role;
use Orchid\Screen\AsSource;

class Ticket extends Model
{
    use AsSource;
    protected $fillable = [
        'subject',
        'content',
        'status_id',
        'priority_id',
        'user_id',
        'agent_id',
        'category_id'
    ];

    protected $table = 'ticket';

    /**
     * Sets the agent with the lowest tickets assigned in specific category.
     *
     * @return Ticket
     */
    public function autoSelectAgent()
    {
        $category = $this->category_id;

        // Retrieve all agents who have access to respond to tickets
        $allAgents = User::byAccess("platform.tickets.respond")->get();

        // Count the number of tickets assigned to each agent in the specified category
        $agentTicketCounts = $allAgents->map(function ($agent) use ($category) {
            return [
                'agent_id' => $agent->id,
                'ticket_count' => Ticket::where('agent_id', $agent->id)
                    ->where('category_id', $category)
                    ->count()
            ];
        });

        // Find the agent with the minimum number of tickets assigned
        $selectedAgent = $agentTicketCounts->sortBy('ticket_count')->first();

        // Assign the ticket to the selected agent
        if ($selectedAgent) {
            $this->agent_id = $selectedAgent['agent_id'];
            $this->save();
        }

        return $this;
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class);
    }

    public function status()
    {
        return $this->belongsTo(TicketStatus::class);
    }

    public function priority()
    {
        return $this->belongsTo(TicketPriority::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        $user = auth()->user() ?? null;
        static::addGlobalScope(new InstitutionScope($user));
    }

}

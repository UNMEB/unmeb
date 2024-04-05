<div class="container-fluid mt-4 bg-white rounded d-flex flex-column">
    <div class="row">
        <div class="col-md-12">
            @if ($ticket->exists)
                <h4 class="my-4">Support Ticket Details</h4>

                <!-- Ticket Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        Ticket #{{ $ticket->id }} - {{ $ticket->subject }}
                    </div>
                    <div class="card-body">
                        <p><strong>Status:</strong> {{ optional($ticket->status)->name }}</p>
                        <p><strong>Created:</strong> {{ $ticket->created_at->format('F j, Y g:i A') }}</p>
                        <p><strong>Submitted By:</strong> {{ $ticket->user->name }}</p>
                        <p><strong>Description:</strong></p>
                        <p>{!! $ticket->content !!}</p>
                    </div>
                </div>

                <!-- Previous Replies -->
                <div class="card mb-4">
                    <div class="card-header">
                        Previous Replies
                    </div>
                    <ul class="list-group list-group-flush">
                        @foreach ($ticket->comments as $comment)
                            <li class="list-group-item">
                                <p><strong>Reply #{{ $loop->iteration }}</strong></p>
                                <p>{{ $comment->content }}</p>
                                <p class="text-muted">Replied by {{ $comment->user->name }} on
                                    {{ $comment->created_at->format('F j, Y g:i A') }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Response Form -->
                <div class="card">
                    <div class="card-header">
                        Add Response
                    </div>
                    <div class="card-body">
                        <div class="card-body">
                            {!! \Orchid\Screen\Fields\Quill::make('reply') !!}
                            @include('submit_button')
                        </div>
                    </div>
                </div>
            @else
                <!-- New Ticket Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        New Ticket
                    </div>
                    <div class="card-body">
                        {!! \Orchid\Screen\Fields\Input::make('subject')->title('Subject')->required() !!}

                        {!! \Orchid\Screen\Fields\Quill::make('content')->toolbar(['text', 'color', 'header', 'list', 'format', 'media'])->title('Content')->help('How may we assist you')->required() !!}

                        {!! \Orchid\Screen\Fields\Relation::make('priority_id')->fromModel(\App\Models\TicketPriority::class, 'name')->title('Select priority')->empty('None Selected')->required() !!}

                        {!! \Orchid\Screen\Fields\Relation::make('category_id')->fromModel(\App\Models\TicketCategory::class, 'name')->title('Select Category')->empty('None Selected')->required() !!}

                        @include('submit_button')

                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

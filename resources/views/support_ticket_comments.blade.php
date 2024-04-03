<div class="container mt-3">
    <div class="row">
        <div class="col-md-3">
            <!-- Vertical Menu -->
            <div class="list-group">
                <a href="#" class="list-group-item list-group-item-action">Mark as resolved</a>
                <a href="#" class="list-group-item list-group-item-action">Archive Ticket</a>
                <a href="#" class="list-group-item list-group-item-action">Delete Ticket</a>
            </div>
        </div>
        <div class="col-md-9">
            {{-- <h2 class="mb-4">Support Ticket Details</h2> --}}

            <!-- Ticket Details -->
            <div class="card mb-4">
                <div class="card-header">
                    Ticket #{{ $ticket->id ?? '' }} - {{ $ticket->subject ?? '' }}
                </div>
                <div class="card-body">
                    <p><strong>Status:</strong> {{ $ticket->status->name ?? '' }}</p>
                    <p><strong>Created:</strong>
                        {{ $ticket->created_at ? $ticket->created_at->format('F j, Y g:i A') : '' }}</p>
                    <p><strong>Submitted By:</strong> {{ $ticket->user->name ?? '' }}</p>
                    <p><strong>Description:</strong></p>
                    <p>{!! $ticket->content ?? '' !!}</p>
                </div>
            </div>

            <!-- Previous Replies -->
            <div class="card mb-4">
                <div class="card-header">
                    Previous Replies
                </div>
                <ul class="list-group list-group-flush">
                    @if ($ticket->comments)
                        @foreach ($ticket->comments as $comment)
                            <li class="list-group-item">
                                <p><strong>Reply #{{ $loop->iteration }}</strong></p>
                                <p>{{ $comment->content ?? '' }}</p>
                                <p class="text-muted">Replied by {{ $comment->user->name ?? '' }} on
                                    {{ $comment->created_at ? $comment->created_at->format('F j, Y g:i A') : '' }}</p>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>

            <!-- Response Form -->
            <div class="card">
                <div class="card-header">
                    Add Response
                </div>
                <div class="card-body">
                    {!! \Orchid\Screen\Fields\Quill::make('reply') !!}
                    @include('submit_button')
                </div>
            </div>

        </div>
    </div>
</div>

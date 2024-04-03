<div class="container-fluid mt-4 bg-white rounded d-flex flex-column">
    <div class="row">
        <div class="col-md-12">
            @if ($ticket->exists)
                <h2 class="mb-4">Support Ticket Details</h2>

                <!-- Ticket Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        Ticket #{{ $ticket->id }} - {{ $ticket->subject }}
                    </div>
                    <div class="card-body">
                        <p><strong>Status:</strong> {{ $ticket->status->name }}</p>
                        <p><strong>Created:</strong> {{ $ticket->created_at->format('F j, Y g:i A') }}</p>
                        <p><strong>Submitted By:</strong> {{ $ticket->user->name }}</p>
                        <p><strong>Description:</strong></p>
                        <p>{{ $ticket->content }}</p>
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
                        <form method="post" action="#">
                            @csrf
                            <div class="form-group">
                                <label for="subject">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" required>
                            </div>
                            <div class="form-group">
                                <label for="content">Content</label>
                                <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
                            </div>
                            {{-- <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status" required>
                                    <!-- Populate status options dynamically from database -->
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                                    @endforeach
                                </select>
                            </div> --}}
                            <div class="form-group">
                                <label for="priority">Priority</label>
                                <select class="form-control" id="priority" name="priority" required>
                                    <!-- Populate priority options dynamically from database -->
                                    @foreach ($priorities as $priority)
                                        <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="category">Category</label>
                                <select class="form-control" id="category" name="category" required>
                                    <!-- Populate category options dynamically from database -->
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Create Ticket</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

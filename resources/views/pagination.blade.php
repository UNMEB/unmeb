<footer class="pb-3 w-100 v-md-center px-4 d-flex flex-wrap justify-content-between align-items-center">
    <div class="col-auto overflow-auto flex-shrink-1 mt-3 mt-sm-0">
        @if ($paginator instanceof \Illuminate\Contracts\Pagination\CursorPaginator)
            {!! $paginator->appends(request()->except(['page', '_token']))->links('platform::partials.pagination') !!}
        @elseif($paginator instanceof \Illuminate\Contracts\Pagination\Paginator)
            {!! $paginator->appends(request()->except(['page', '_token']))->onEachSide($onEachSide ?? 3)->links('platform::partials.pagination') !!}
        @endif
    </div>
    <div class="col-auto">
        {!! \Orchid\Screen\Actions\Button::make(__('Submit Data'))->method('submit')->class('btn btn-success') !!}
    </div>
</footer>

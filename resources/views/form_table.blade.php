
@empty(!$title)
    <fieldset>
        <div class="col p-0 px-3">
            <legend class="text-black text-black mt-2 mx-2">
                {{ $title }}
            </legend>
        </div>
    </fieldset>
@endempty

<div class="bg-white rounded shadow-sm my-3" data-controller="table" data-table-slug="{{ $slug }}">

    <div class="table-responsive">
        <table @class([
            'table',
            'table-compact' => $compact,
            'table-striped' => $striped,
            'table-bordered' => $bordered,
            'table-hover' => $hoverable,
        ])>

            @if ($showHeader)
                <thead>
                    <tr>
                        @foreach ($columns as $column)
                            {!! $column->buildTh() !!}
                        @endforeach
                    </tr>
                </thead>
            @endif

            <tbody>

                @foreach ($rows as $source)
                    <tr>
                        @foreach ($columns as $column)
                            {!! $column->buildTd($source, $loop->parent) !!}
                        @endforeach
                    </tr>
                @endforeach

                @if ($total->isNotEmpty())
                    <tr>
                        @foreach ($total as $column)
                            {!! $column->buildTd($repository, $loop) !!}
                        @endforeach
                    </tr>
                @endif

            </tbody>

        </table>
    </div>

    @if ($rows->isEmpty())
        <div class="d-md-flex align-items-center px-md-0 px-2 pt-4 pb-5 w-100 text-md-start text-center">

            @isset($iconNotFound)
                <div class="col-auto mx-md-4 mb-3 mb-md-0">
                    <x-orchid-icon :path="$iconNotFound" class="block h1" />
                </div>
            @endisset

            <div>
                <h3 class="fw-light">
                    {!! $textNotFound !!}
                </h3>

                {!! $subNotFound !!}
            </div>
        </div>
    @else
        {{-- @include('platform::layouts.pagination', [
            'paginator' => $rows,
            'columns' => $columns,
            'onEachSide' => $onEachSide,
        ]) --}}

        <!-- @include('pagination', [
            'paginator' => $rows,
            'columns' => $columns,
            'onEachSide' => $onEachSide,
        ]) -->

        <footer class="pb-3 w-100 v-md-center px-4 d-flex flex-wrap justify-content-between align-items-center">
            <div class="col-auto overflow-auto flex-shrink-1 mt-3 mt-sm-0">
                @if ($rows instanceof \Illuminate\Contracts\Pagination\CursorPaginator)
                    {!! $rows->appends(request()->except(['page', '_token']))->links('platform::partials.pagination') !!}
                @elseif($rows instanceof \Illuminate\Contracts\Pagination\Paginator)
                    {!! $rows->appends(request()->except(['page', '_token']))->onEachSide($onEachSide ?? 3)->links('platform::partials.pagination') !!}
                @endif
            </div>
            <div class="col-auto">
                <!--  -->

                <div class="d-flex justify-content-end">
                    {!! \Orchid\Screen\Actions\Button::make(__('Submit Data'))->method('submit')->class('btn btn-success') !!}
                </div>
            </div>
        </footer>

    @endif


</div>

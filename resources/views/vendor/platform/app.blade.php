<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-controller="html-load" dir="{{ \Orchid\Support\Locale::currentDir() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no">
    <title>
        @yield('title', config('app.name'))
        @hasSection('title')
            - {{ config('app.name') }}
        @endif
    </title>
    <meta name="csrf_token" content="{{ csrf_token() }}" id="csrf_token">
    <meta name="auth" content="{{ Auth::check() }}" id="auth">
    @if (\Orchid\Support\Locale::currentDir(app()->getLocale()) == 'rtl')
        <link rel="stylesheet" type="text/css" href="{{ mix('/css/orchid.rtl.css', 'vendor/orchid') }}">
    @else
        <link rel="stylesheet" type="text/css" href="{{ mix('/css/orchid.css', 'vendor/orchid') }}">
    @endif

    @stack('head')

    <meta name="turbo-root" content="{{ Dashboard::prefix() }}">
    <meta name="dashboard-prefix" content="{{ Dashboard::prefix() }}">

    @if (!config('platform.turbo.cache', false))
        <meta name="turbo-cache-control" content="no-cache">
    @endif

    <script src="{{ mix('/js/manifest.js', 'vendor/orchid') }}" type="text/javascript"></script>
    <script src="{{ mix('/js/vendor.js', 'vendor/orchid') }}" type="text/javascript"></script>
    <script src="{{ mix('/js/orchid.js', 'vendor/orchid') }}" type="text/javascript"></script>

    @foreach (Dashboard::getResource('stylesheets') as $stylesheet)
        <link rel="stylesheet" href="{{ $stylesheet }}">
    @endforeach

    @stack('stylesheets')

    @foreach (Dashboard::getResource('scripts') as $scripts)
        <script src="{{ $scripts }}" defer type="text/javascript"></script>
    @endforeach

    @if (!empty(config('platform.vite', [])))
        @vite(config('platform.vite'))
    @endif



    <link rel='stylesheet' href='https://pivottable.js.org/dist/pivot.css'>

    <!-- <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests"> -->



</head>

<body class="{{ \Orchid\Support\Names::getPageNameClass() }}" data-controller="pull-to-refresh">

    <div class="container-fluid" data-controller="@yield('controller')" @yield('controller-data')>

        <div class="row d-md-flex h-100">
            @yield('aside')

            <div class="col-xxl col-xl-9 col-12">
                @yield('body')
            </div>
        </div>


        @include('platform::partials.toast')
    </div>

    {{-- <script src="https://code.highcharts.com/highcharts.js"></script> --}}
    {{-- <script src="https://code.highcharts.com/modules/exporting.js"></script> --}}
    {{-- <script src="https://code.highcharts.com/modules/export-data.js"></script> --}}
    {{-- <script src="https://code.highcharts.com/modules/accessibility.js"></script> --}}

    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/TableExport/5.2.0/js/tableexport.min.js"></script>
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Blob.js/1.1.2/Blob.min.js"></script>
    <script src="
    https://cdn.jsdelivr.net/npm/blobjs@1.1.1/Blob.min.js
    "></script> --}}

    @include('sweetalert::alert')

    @stack('scripts')


</body>

</html>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="initial-scale=1, width=device-width" />
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset($constants['CSSFILEPATH'] . 'semantic.min.css') }}" />
    <link href="https://unpkg.com/tailwindcss@^2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link rel="shortcut icon" href="{{ asset($constants['IMAGEFILEPATH'] . 'logo.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset($constants['CSSFILEPATH'] . 'custom.css') }}" />
    <link rel="stylesheet" href="{{ asset($constants['CSSFILEPATH'] . 'root.css') }}" />
    <link rel="stylesheet" href="{{ asset($constants['CSSFILEPATH'] . 'common.css') }}" />
    <!-- <link rel="stylesheet" href="{{ asset($constants['CSSFILEPATH'] . 'cs.css') }}" /> -->
    <script>
        var base_url = "{{ url('/') }}";
    </script>

    @yield('styles')

</head>

<body class="w-full h-screen">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('notice.notification')
    <!-- Taskbar -->
    <div class="navbar navbarhead h-16 flex items-center w-full absolute">
        <div class="flex justify-center ml-10 w-full relative h-full" id="toolbar">
            <div class="bg absolute w-full  bottom-0">
                <img id="shelf" class="w-full" src="{{ asset($constants['IMAGEFILEPATH'] . 'shelf.png') }}"
                    alt="">
            </div>
            <header id="iframeheaders" class="mt-1">
                <div class="flex space-x-4" id="sortable-apps">
                </div>
            </header>
        </div>
        <div class="flex items-center gap-8 w-48 justify-end pr-5 relative">
            <i id="search-icon" class="ri-search-line icon-color"></i>
            <i id="pinned" class="ri-pushpin-line icon-color"></i>
            <i id="notification-icon" class="ri-notification-3-line icon-color"></i>
            <button class="icon-trigger-dropdown cursor-default">
                <i class="ri-question-line icon-color"></i>
            </button>
            <div class="taskbar-dropdown-menu">
                <a href="https://desktop2.sizaf.com/docs">
                    <button id="doc-button" type="button" value="Documentation" tabindex="0" class="taskbar-dropdown-item flex items-center gap-2">
                        <i class="ri-book-marked-line"></i>
                    </button>
                </a>
                <button id="guide-button" type="button" value="OnScreen" tabindex="0" class="taskbar-dropdown-item flex items-center gap-2">
                    <i class="ri-guide-line"></i>
                </button>
            </div>
            <div id="doc-tooltip" class="hidden absolute py-1 px-2 text-start text-xs top-9 right-12 z-10 bg-white border rounded-md border-c-yellow font-normal">
                Documentation
            </div>
            <div id="guide-tooltip" class="hidden absolute py-1 px-2 text-start text-xs right-12 z-10 bg-white border rounded-md border-c-yellow font-normal" style="top: 4.3rem;">
                Guide
            </div>
        </div>
    </div>
    <!-- Taskbar End -->


    <!-- <header id="iframeheaders" class="transparent p-2 text-white flex justify-center items-center fixed top-0 left-0 right-0 mainiframeiconheader mainscreen"> -->

    <div class="notification-container">
        <div id="NotiContainer" class="Notification h-80 absolute right-5 top-16 hidden overflow-hidden">
            <div class="h-16 border-b-2 border-c-gray py-4 px-4 flex items-center justify-between">
                <h1 class="text-sm sm:text-lg text-c-black font-normal">Notification Center</h1>
                @if (Auth::user()->notifications()->whereNull('read_at')->count() > 0)
                    <h1 class="text-sm sm:text-lg text-c-yellow font-medium cursor-pointer" id="MarkAllRead">Mark all as
                        read</h1>
                @endif
            </div>
            <div class="scrollbar-div overflow-y-auto" style="height: calc(100% - 64px);">
                <ul id="ULNoti">
                    @if (Auth::user()->notifications()->whereNull('read_at')->count() > 0)
                        @foreach (Auth::user()->notifications()->whereNull('read_at')->pluck('data', 'id') as $id => $data)
                            @php
                                // Check if $data is a string and needs decoding
                                $notification = is_string($data) ? json_decode($data, true) : $data;
                            @endphp
                            <li class="border-b-2 border-c-gray px-4 py-2.5">
                                <div class="flex items-start justify-between gap-20">
                                    <p class="text-sm text-c-black font-normal">
                                        {{ $notification['title'] ?? 'No Title' }}
                                    </p>
                                    <i class="ri-close-circle-fill ri-1x cursor-pointer ReadThisNoti"
                                        data-id="{{ $id }}"></i>
                                </div>
                                <span
                                    class="text-c-time font-normal text-sm">{{ \Carbon\Carbon::parse($notification['time'] ?? now())->diffForHumans(['short' => true]) }}</span>
                            </li>
                        @endforeach
                    @else
                        <li class="text-center mt-3">
                            No new notifications
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <!--///// iframe -->
    <div id="alliframelist">

    </div>

    <!--///// Context Menu -->
    <div id="context-menu" class="context-menu context-menulist hidden bg-c-white">

    </div>
    <div id="app-contextmenu" class="context-menu context-menulist hidden bg-c-white">
    </div>
    <!--//// Context Menu End-->

    <!-- Upload popup -->
    <div id="popupuploadfiles"
        class="fixed inset-0 flex z-20 items-center justify-center bg-gray-800 bg-opacity-50 hidden">
        <div class="popup-content bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl">
            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Local Upload</h2>
                <button id="close-popup" class="text-2xl">
                    <i class="ri-close-line"></i>
                </button>
            </div>

            <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold mb-4">File Upload</h2>
                <div id="dropzone" class="border-2 border-dashed border-gray-300 p-6 rounded-lg text-center">
                    Drag and drop files here or click to upload
                </div>
                <input type="file" id="file-input" class="hidden" multiple>
                <div id="file-list-container" class="mt-4 space-y-2 hidden">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 text-left">Name</th>
                                <th class="py-2 px-4 text-left">Size</th>
                                <th class="py-2 px-4 text-left">Progress</th>
                            </tr>
                        </thead>
                        <tbody id="file-list"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>



    @yield('content')

    <div id="shareFilesFolderModal"></div>

    <!--end here -->



    <script>
        const desktopapp = @json(route('desktopapp'));
        const contextmenu = @json(route('contextmenu'));
        const createFolderRoute = @json(route('createfolder'));
        const createFileRoute = @json(route('createfile'));
        const showFileDetail = @json(route('showpathdetail'));
        const renameroute = @json(route('renamefile'));
        const deleteRoute = @json(route('deletefile'));
        const copyRoute = @json(route('copyfile'));
        const pasteRoute = @json(route('pastefile'));
        const closeIframeRoute = @json(route('closeiframe'));
        const openIframeRoute = @json(route('openiframe'));
        const uploadRoute = @json(route('upload'));
        const leftArrowClick = @json(route('leftarrowclick'));
        const rightArrowClick = @json(route('rightarrowclick'));
        const shareRoute = @json(route('getUrl'));
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/toastr.min.js"></script>

    <script src="{{ asset($constants['JSFILEPATH'] . 'animation.js') }}"></script>

    <script src="{{ asset($constants['JSFILEPATH'] . 'common.js') }}"></script>
    <script src="{{ asset($constants['JSFILEPATH'] . 'taskbar.js') }}"></script>

    @yield('scripts')
    <script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.2.13/dist/semantic.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/habibmhamadi/multi-select-tag@3.0.1/dist/js/multi-select-tag.js"></script>
    <!-- <script src="{{ asset($constants['JSFILEPATH'] . 'taskbar.js') }}"></script> -->
    <script>
     $('#doc-button').hover(
                function() {
                    $('#doc-tooltip').stop(true, true).fadeIn();
                },
                function() {
                    $('#doc-tooltip').stop(true, true).fadeOut();
                }
            );

            $('#guide-button').hover(
                function() {
                    $('#guide-tooltip').stop(true, true).fadeIn();
                },
                function() {
                    $('#guide-tooltip').stop(true, true).fadeOut();
                }
            );
    </script>
</body>

</html>

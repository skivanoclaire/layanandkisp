{{-- resources/views/operator/tik/schedule.blade.php --}}
@extends($layout)

@section('title', 'Jadwal Video Konferensi')

@section('content')
    <div class="max-w-7xl mx-auto p-4">
        <h1 class="text-2xl font-semibold mb-4">Jadwal Video Konferensi</h1>

        <div class="rounded-lg overflow-hidden shadow bg-white">
            <div class="aspect-video w-full">
                <iframe width="900" height="675"
                    src="https://lookerstudio.google.com/embed/reporting/f610feda-9bba-4275-a674-78d41b9c8ab8/page/8dm9C"
                    frameborder="0" style="border:0; width:100%; height:80vh;" allowfullscreen
                    sandbox="allow-storage-access-by-user-activation allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox">
                </iframe>
            </div>
        </div>
    </div>
@endsection

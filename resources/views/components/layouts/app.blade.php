<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ asset('brand/amr7/favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('brand/amr7/amr7-app-icon-180.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        [wire\:cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #1FA7A2; }
    </style>

    @livewireStyles
    @filamentStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased min-h-screen flex flex-col bg-slate-50 text-slate-800 font-['Tajawal'] overflow-x-hidden selection:bg-[#1FA7A2] selection:text-white">

    @include('partials.header')

    <main class="flex-grow w-full">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    @include('partials.footer')

    <div class="fixed bottom-6 left-6 z-[9999] flex flex-col gap-4">
        <a href="tel:+966505336956"
           class="w-14 h-14 rounded-full bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 focus:outline-none focus:ring-4 focus:ring-blue-300"
           aria-label="Call Us">
            <i class="fas fa-phone-alt text-xl"></i>
        </a>

        <a href="https://wa.me/966505336956"
           target="_blank"
           rel="noopener noreferrer"
           class="w-14 h-14 rounded-full bg-[#25D366] hover:bg-[#20b858] text-white flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 relative group"
           aria-label="WhatsApp">
            <span class="absolute inline-flex h-full w-full rounded-full bg-[#25D366] opacity-75 animate-ping group-hover:animate-none"></span>
            <span class="relative inline-flex rounded-full h-14 w-14 bg-[#25D366] items-center justify-center">
                <i class="fab fa-whatsapp text-3xl"></i>
            </span>
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @livewireScripts
    @filamentScripts
    @livewire('notifications')

    <script>
        document.addEventListener('livewire:init', () => {
            const Toast = Swal.mixin({
                toast: true,
                position: document.dir === 'rtl' ? 'top-start' : 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#fff',
                color: '#1e293b',
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            });

            Livewire.on('toast', (data) => {
                Toast.fire({
                    icon: data.type || 'success',
                    title: data.message
                });
            });

            Livewire.on('open-whatsapp', ({ url }) => {
                if (url) window.open(url, '_blank', 'noopener,noreferrer');
            });
        });
    </script>

    @stack('scripts')
</body>
</html>

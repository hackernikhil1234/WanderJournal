<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'WanderJournal'))</title>

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,300;0,400;0,700;1,400&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Caveat:wght@400;600&display=swap" rel="stylesheet">
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Sortable.js -->
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            .paper-bg {
                background-color: #FDFBF7;
                background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100' height='100' filter='url(%23noise)' opacity='0.08'/%3E%3C/svg%3E");
            }
            .stamp-border {
                border: 2px dashed #E8E1D5;
                padding: 4px;
            }
            .wax-seal {
                position: absolute;
                top: -15px;
                right: 20px;
                width: 40px;
                height: 40px;
                background: #8B1A1A;
                border-radius: 50%;
                box-shadow: inset 0 0 10px rgba(0,0,0,0.5), 0 2px 5px rgba(0,0,0,0.3);
                display: flex;
                align-items: center;
                justify-content: center;
                color: #D4AF37;
                font-family: serif;
                font-size: 20px;
                transform: rotate(15deg);
                z-index: 10;
            }
            [x-cloak] { display: none !important; }
        </style>
        
        @stack('styles')
    </head>
    <body class="font-sans antialiased text-journal-dark paper-bg min-h-screen flex flex-col"
          x-data="{ pageLoaded: false }" 
          x-init="window.addEventListener('load', () => { setTimeout(() => pageLoaded = true, 500) })"
          :class="pageLoaded ? '' : 'overflow-hidden'">
          
        <!-- Vintage Loading Screen -->
        <div x-show="!pageLoaded" 
             x-transition:leave="transition ease-in-out duration-1000"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[100] bg-journal-paper flex flex-col items-center justify-center">
            <i class="fa-regular fa-compass text-6xl text-journal-gold compass-spin mb-4 drop-shadow-md"></i>
            <h2 class="font-serif text-2xl text-journal-dark tracking-widest uppercase mt-4">WanderJournal</h2>
            <p class="text-journal-light italic mt-2 font-script text-2xl">Preparing your journey...</p>
        </div>
        
        <!-- Main Navigation -->
        <nav class="bg-journal-paper border-b border-journal-border shadow-sm relative z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-20">
                    <div class="flex items-center">
                        <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                            <i class="fa-solid fa-compass text-journal-accent text-3xl group-hover:rotate-45 transition-transform duration-500"></i>
                            <span class="font-serif text-2xl font-bold tracking-wider text-journal-dark">{{ config('app.name', 'WanderJournal') }}</span>
                        </a>
                        
                        <div class="hidden sm:flex sm:ml-10 space-x-8">
                            <button onclick="window.history.back()" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-journal-light hover:text-journal-dark hover:border-journal-border text-sm font-medium transition duration-150 gap-2 cursor-pointer">
                                <i class="fa-solid fa-arrow-left text-xs"></i> Go Back
                            </button>
                            <a href="{{ route('destinations.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('destinations.*') ? 'border-journal-accent text-journal-dark' : 'border-transparent text-journal-light hover:text-journal-dark hover:border-journal-border' }} text-sm font-medium transition duration-150">
                                Destinations
                            </a>
                            <a href="{{ route('trips.create') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('trips.create') ? 'border-journal-accent text-journal-dark' : 'border-transparent text-journal-light hover:text-journal-dark hover:border-journal-border' }} text-sm font-medium transition duration-150">
                                Plan a Trip
                            </a>
                        </div>
                    </div>
                    
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        @auth
                            <div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
                                <div @click="open = ! open">
                                    <button class="flex items-center gap-2 text-sm font-medium text-journal-dark hover:text-journal-accent focus:outline-none transition duration-150 ease-in-out">
                                        <img class="h-8 w-8 rounded-full border border-journal-border object-cover" src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" />
                                        <div>{{ Auth::user()->name }}</div>
                                        <i class="fa-solid fa-chevron-down text-xs ml-1"></i>
                                    </button>
                                </div>

                                <div x-show="open"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                                     x-cloak>
                                    
                                    <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-journal-paper">Dashboard</a>
                                    <a href="{{ route('trips.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-journal-paper">My Trips</a>
                                    <a href="{{ route('bookings.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-journal-paper">Bookings</a>
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-journal-paper border-t border-gray-100">Profile</a>
                                    
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                            Log Out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="text-journal-dark hover:text-journal-accent px-3 py-2 rounded-md text-sm font-medium transition">Log in</a>
                            <a href="{{ route('register') }}" class="ml-4 bg-journal-dark text-white hover:bg-journal-accent px-4 py-2 rounded-sm text-sm font-medium transition shadow-sm">Register</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Flash Messages -->
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="bg-journal-olive text-white px-4 py-3 shadow-md" role="alert">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-check-circle"></i>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false"><i class="fa-solid fa-times"></i></button>
                </div>
            </div>
        @endif
        
        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" class="bg-red-600 text-white px-4 py-3 shadow-md" role="alert">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-exclamation-triangle"></i>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                    <button @click="show = false"><i class="fa-solid fa-times"></i></button>
                </div>
            </div>
        @endif

        <!-- Page Content -->
        <main class="flex-grow">
            @yield('content')
            
            {{ $slot ?? '' }}
        </main>

        <!-- Footer -->
        <footer class="bg-journal-dark text-journal-paper mt-auto border-t-4 border-journal-accent">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="col-span-1 md:col-span-2">
                        <a href="/" class="flex items-center gap-2 text-2xl font-serif font-bold mb-4">
                            <i class="fa-solid fa-compass text-journal-accent"></i>
                            WanderJournal
                        </a>
                        <p class="text-gray-400 text-sm leading-relaxed max-w-md">
                            Crafting unforgettable journeys, one page at a time. Plan, organize, and cherish your travel memories with our vintage-inspired smart itinerary planner.
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold tracking-wider uppercase mb-4 text-journal-gold">Explore</h3>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li><a href="{{ route('destinations.index') }}" class="hover:text-white transition">All Destinations</a></li>
                            <li><a href="{{ route('destinations.index', ['category' => 'cultural']) }}" class="hover:text-white transition">Cultural Tours</a></li>
                            <li><a href="{{ route('destinations.index', ['category' => 'island']) }}" class="hover:text-white transition">Island Escapes</a></li>
                            <li><a href="{{ route('trips.create') }}" class="hover:text-white transition">Plan a Trip</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold tracking-wider uppercase mb-4 text-journal-gold">Connect</h3>
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-400 hover:text-white transition"><i class="fa-brands fa-instagram text-xl"></i></a>
                            <a href="#" class="text-gray-400 hover:text-white transition"><i class="fa-brands fa-twitter text-xl"></i></a>
                            <a href="#" class="text-gray-400 hover:text-white transition"><i class="fa-brands fa-pinterest text-xl"></i></a>
                        </div>
                        <p class="mt-4 text-sm text-gray-400">
                            &copy; {{ date('Y') }} WanderJournal.<br>All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </footer>

        @stack('scripts')
    </body>
</html>

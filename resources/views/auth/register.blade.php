@extends('layouts.app')

@section('title', 'Register - WanderJournal')

@section('content')
<div class="py-12 max-w-lg mx-auto px-4 sm:px-6 lg:px-8 min-h-[70vh] flex items-center justify-center">
    <div class="bg-journal-paper p-8 md:p-10 shadow-postcard border border-journal-border relative w-full">
        <div class="wax-seal" style="top: -20px; right: 20px;">R</div>
        
        <div class="text-center mb-8">
            <h1 class="text-3xl font-serif font-bold text-journal-dark mb-2">Begin Your Story</h1>
            <p class="text-journal-light text-sm">Join WanderJournal to start planning your next grand adventure.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-6 bg-white p-6 border border-journal-border shadow-sm stamp-border">
            @csrf

            <!-- Name -->
            <div>
                <label for="name" class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-2">Full Name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" 
                       class="w-full border-journal-border rounded-sm focus:ring-journal-accent focus:border-journal-accent bg-journal-bg shadow-sm" placeholder="e.g. Amelia Earhart">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-2">Email Address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" 
                       class="w-full border-journal-border rounded-sm focus:ring-journal-accent focus:border-journal-accent bg-journal-bg shadow-sm">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Password -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-2">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password" 
                           class="w-full border-journal-border rounded-sm focus:ring-journal-accent focus:border-journal-accent bg-journal-bg shadow-sm">
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-2">Confirm</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" 
                           class="w-full border-journal-border rounded-sm focus:ring-journal-accent focus:border-journal-accent bg-journal-bg shadow-sm">
                    @error('password_confirmation') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="pt-4 border-t border-journal-border border-dashed">
                <button type="submit" class="w-full bg-journal-dark hover:bg-journal-accent text-white font-serif font-bold py-3 px-4 shadow-md transition-all flex items-center justify-center gap-2 text-lg">
                    <i class="fa-solid fa-feather-pointed text-journal-gold"></i> Create Journal
                </button>
            </div>
            
            <div class="text-center mt-6">
                <p class="text-sm text-journal-light">
                    Already have a journal? 
                    <a href="{{ route('login') }}" class="text-journal-accent hover:text-journal-dark font-bold underline transition-colors">Log in</a>
                </p>
            </div>
        </form>
    </div>
</div>
@endsection

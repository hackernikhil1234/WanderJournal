@extends('layouts.app')

@section('title', 'Log In - WanderJournal')

@section('content')
<div class="py-16 max-w-md mx-auto px-4 sm:px-6 lg:px-8 min-h-[70vh] flex items-center justify-center">
    <div class="bg-journal-paper p-8 md:p-10 shadow-postcard border border-journal-border relative w-full">
        <div class="wax-seal" style="top: -20px; right: 20px;">L</div>
        
        <div class="text-center mb-8">
            <h1 class="text-3xl font-serif font-bold text-journal-dark mb-2">Welcome Back</h1>
            <p class="text-journal-light text-sm">Continue your journey with WanderJournal.</p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-3 rounded-sm border border-green-200">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-6 bg-white p-6 border border-journal-border shadow-sm stamp-border">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-2">Email Address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" 
                       class="w-full border-journal-border rounded-sm focus:ring-journal-accent focus:border-journal-accent bg-journal-bg shadow-sm">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-bold text-journal-dark uppercase tracking-wider mb-2">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password" 
                       class="w-full border-journal-border rounded-sm focus:ring-journal-accent focus:border-journal-accent bg-journal-bg shadow-sm">
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                    <input id="remember_me" type="checkbox" name="remember" class="rounded border-journal-border text-journal-accent shadow-sm focus:ring-journal-accent cursor-pointer">
                    <span class="ms-2 text-sm text-journal-dark group-hover:text-journal-accent transition-colors">Remember me</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-xs text-journal-olive hover:text-journal-dark font-bold uppercase tracking-wider underline transition-colors" href="{{ route('password.request') }}">
                        Forgot password?
                    </a>
                @endif
            </div>

            <div class="pt-4 border-t border-journal-border border-dashed">
                <button type="submit" class="w-full bg-journal-dark hover:bg-journal-accent text-white font-serif font-bold py-3 px-4 shadow-md transition-all flex items-center justify-center gap-2 text-lg">
                    <i class="fa-solid fa-key text-journal-gold"></i> Log In
                </button>
            </div>
            
            <div class="text-center mt-6">
                <p class="text-sm text-journal-light">
                    Don't have an account? 
                    <a href="{{ route('register') }}" class="text-journal-accent hover:text-journal-dark font-bold underline transition-colors">Begin here</a>
                </p>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Travel Community — WanderJournal')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">

    {{-- Header --}}
    <div class="text-center mb-12">
        <span class="font-script text-3xl text-journal-accent block mb-2">Share your journey</span>
        <h1 class="text-4xl font-serif font-bold text-journal-dark mb-4">Travel Inspiration</h1>
        <p class="text-journal-light max-w-xl mx-auto">Discover stories from explorers around the world. Share your own adventures with the community.</p>
        @auth
        <a href="{{ route('community.create') }}" class="mt-6 inline-flex items-center gap-2 bg-journal-accent hover:bg-journal-dark text-white font-bold py-3 px-8 transition shadow-sm uppercase tracking-wider text-sm">
            <i class="fa-solid fa-pen-nib"></i> Share Your Story
        </a>
        @endauth
    </div>

    {{-- Featured Travelers --}}
    @if($featuredTravelers->isNotEmpty())
    <div class="mb-12">
        <h2 class="text-xl font-serif font-bold text-journal-dark mb-4">Top Travelers</h2>
        <div class="flex gap-4 overflow-x-auto pb-2">
            @foreach($featuredTravelers as $traveler)
            <a href="{{ route('community.profile', $traveler) }}" class="flex-shrink-0 flex flex-col items-center gap-2 group">
                <div class="w-16 h-16 rounded-full border-2 border-journal-border group-hover:border-journal-accent overflow-hidden transition">
                    <img src="{{ $traveler->avatar_url }}" alt="{{ $traveler->name }}" class="w-full h-full object-cover">
                </div>
                <span class="text-xs font-bold text-journal-dark text-center max-w-[64px] truncate">{{ explode(' ', $traveler->name)[0] }}</span>
                <span class="text-xs text-journal-light">{{ $traveler->travel_posts_count }} posts</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Posts Grid --}}
    @if($posts->isEmpty())
    <div class="text-center py-20">
        <i class="fa-solid fa-camera-retro text-6xl text-journal-light mb-4"></i>
        <h3 class="text-2xl font-serif text-journal-dark mb-2">No stories yet</h3>
        <p class="text-journal-light">Be the first to share your travel adventure!</p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($posts as $post)
        <article class="bg-white border border-journal-border shadow-postcard overflow-hidden group polaroid">
            <div class="tape{{ $loop->iteration % 2 === 0 ? '-alt' : '' }}"></div>

            {{-- Cover Photo --}}
            @if($post->cover_photo)
            <a href="{{ route('community.show', $post) }}" class="block overflow-hidden h-52">
                <img src="{{ $post->cover_photo }}" alt="{{ $post->title }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500 filter sepia-[.1] group-hover:sepia-0">
            </a>
            @elseif($post->trip?->destination)
            <a href="{{ route('community.show', $post) }}" class="block overflow-hidden h-52">
                <img src="{{ $post->trip->destination->cover_image_url }}" alt="{{ $post->title }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500 filter sepia-[.2]">
            </a>
            @endif

            <div class="p-5">
                {{-- Author --}}
                <div class="flex items-center gap-3 mb-3">
                    <img src="{{ $post->user->avatar_url }}" alt="{{ $post->user->name }}" class="w-8 h-8 rounded-full border border-journal-border">
                    <div>
                        <a href="{{ route('community.profile', $post->user) }}" class="text-sm font-bold text-journal-dark hover:text-journal-accent">{{ $post->user->name }}</a>
                        @if($post->trip?->destination)
                        <div class="text-xs text-journal-light flex items-center gap-1">
                            <i class="fa-solid fa-map-pin"></i>
                            {{ $post->trip->destination->name }}, {{ $post->trip->destination->country }}
                        </div>
                        @endif
                    </div>
                    <span class="ml-auto text-xs text-journal-light">{{ $post->created_at->diffForHumans() }}</span>
                </div>

                <a href="{{ route('community.show', $post) }}">
                    <h2 class="font-serif font-bold text-xl text-journal-dark mb-2 hover:text-journal-accent transition line-clamp-2">{{ $post->title }}</h2>
                </a>
                <p class="text-journal-light text-sm line-clamp-2 mb-4">{{ $post->excerpt }}</p>

                <div class="flex items-center gap-4 text-sm text-journal-light">
                    <span class="flex items-center gap-1"><i class="fa-solid fa-heart text-red-400"></i> {{ $post->likes_count }}</span>
                    <span class="flex items-center gap-1"><i class="fa-solid fa-comment text-journal-olive"></i> {{ $post->comments_count }}</span>
                    <span class="flex items-center gap-1"><i class="fa-solid fa-eye text-gray-400"></i> {{ $post->views_count }}</span>
                    <a href="{{ route('community.show', $post) }}" class="ml-auto text-xs font-bold text-journal-accent hover:text-journal-dark">Read more →</a>
                </div>
            </div>
        </article>
        @endforeach
    </div>
    <div class="mt-10">{{ $posts->links() }}</div>
    @endif

</div>
@endsection

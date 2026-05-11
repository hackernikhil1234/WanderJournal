<?php

namespace App\Http\Controllers;

use App\Models\TravelPost;
use App\Models\User;
use App\Models\Follow;
use App\Models\PostLike;
use App\Models\PostComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommunityController extends Controller
{
    /**
     * Public travel inspiration feed.
     */
    public function feed()
    {
        $posts = TravelPost::where('visibility', 'public')
            ->with(['user', 'trip.destination'])
            ->withCount(['likes', 'comments'])
            ->latest()
            ->cursorPaginate(12);

        $featuredTravelers = User::withCount(['travelPosts' => fn($q) => $q->where('visibility', 'public')])
            ->having('travel_posts_count', '>', 0)
            ->orderByDesc('travel_posts_count')
            ->limit(6)
            ->get();

        return view('community.feed', compact('posts', 'featuredTravelers'));
    }

    /**
     * Show a single travel post.
     */
    public function show(TravelPost $post)
    {
        if ($post->visibility === 'private' && $post->user_id !== Auth::id()) abort(403);

        $post->increment('views_count');
        $post->load(['user', 'trip.destination', 'comments.user']);

        $relatedPosts = TravelPost::where('visibility', 'public')
            ->where('id', '!=', $post->id)
            ->when($post->trip_id, fn($q) => $q->whereHas('trip.destination', fn($dq) =>
                $dq->where('country', $post->trip->destination->country ?? '')
            ))
            ->latest()
            ->limit(3)
            ->get();

        return view('community.show', compact('post', 'relatedPosts'));
    }

    /**
     * Create travel post form.
     */
    public function create()
    {
        $trips = Auth::user()->trips()->with('destination')->latest()->get();
        return view('community.create', compact('trips'));
    }

    /**
     * Store a new travel post.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'body'       => 'required|string|min:10',
            'trip_id'    => 'nullable|exists:trips,id',
            'visibility' => 'required|in:public,followers,private',
            'photos'     => 'nullable|array|max:10',
            'photos.*'   => 'image|max:5120', // 5MB per photo
        ]);

        $photoUrls = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('travel-posts', 'public');
                $photoUrls[] = Storage::url($path);
            }
        }

        TravelPost::create([
            'user_id'    => Auth::id(),
            'trip_id'    => $validated['trip_id'] ?? null,
            'title'      => $validated['title'],
            'body'       => $validated['body'],
            'visibility' => $validated['visibility'],
            'photos'     => $photoUrls,
        ]);

        return redirect()->route('community.feed')->with('success', '✈️ Your travel story has been shared!');
    }

    /**
     * Toggle like on a post (AJAX).
     */
    public function like(TravelPost $post)
    {
        $user = Auth::user();
        $existing = PostLike::where('user_id', $user->id)->where('travel_post_id', $post->id)->first();

        if ($existing) {
            $existing->delete();
            $post->decrement('likes_count');
            $liked = false;
        } else {
            PostLike::create(['user_id' => $user->id, 'travel_post_id' => $post->id]);
            $post->increment('likes_count');
            $liked = true;
        }

        return response()->json(['liked' => $liked, 'count' => $post->fresh()->likes_count]);
    }

    /**
     * Add a comment to a post (AJAX).
     */
    public function comment(TravelPost $post, Request $request)
    {
        $request->validate(['body' => 'required|string|max:500']);

        $comment = PostComment::create([
            'user_id'       => Auth::id(),
            'travel_post_id' => $post->id,
            'body'          => $request->body,
        ]);

        $post->increment('comments_count');
        $comment->load('user');

        return response()->json([
            'comment' => [
                'id'         => $comment->id,
                'body'       => $comment->body,
                'user'       => ['name' => $comment->user->name, 'avatar' => $comment->user->avatar_url],
                'created_at' => $comment->created_at->diffForHumans(),
            ],
        ]);
    }

    /**
     * Follow / unfollow a user (AJAX).
     */
    public function follow(User $user)
    {
        if ($user->id === Auth::id()) {
            return response()->json(['error' => 'Cannot follow yourself'], 422);
        }

        $existing = Follow::where('follower_id', Auth::id())->where('following_id', $user->id)->first();

        if ($existing) {
            $existing->delete();
            $following = false;
        } else {
            Follow::create(['follower_id' => Auth::id(), 'following_id' => $user->id]);
            $following = true;
        }

        return response()->json([
            'following'       => $following,
            'followers_count' => Follow::where('following_id', $user->id)->count(),
        ]);
    }

    /**
     * Public traveler profile.
     */
    public function profile(User $user)
    {
        $posts = $user->travelPosts()
            ->where('visibility', 'public')
            ->with('trip.destination')
            ->latest()
            ->cursorPaginate(9);

        $stats = [
            'posts'     => $user->travelPosts()->where('visibility', 'public')->count(),
            'trips'     => $user->trips()->where('is_public', true)->count(),
            'followers' => Follow::where('following_id', $user->id)->count(),
            'following' => Follow::where('follower_id', $user->id)->count(),
        ];

        $isFollowing = Auth::check() ? Auth::user()->isFollowing($user) : false;

        return view('community.profile', compact('user', 'posts', 'stats', 'isFollowing'));
    }
}

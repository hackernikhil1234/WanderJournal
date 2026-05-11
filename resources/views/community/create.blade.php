@extends('layouts.app')
@section('title', 'Share Your Story — WanderJournal')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">
    <div class="text-center mb-8">
        <span class="font-script text-3xl text-journal-accent block mb-2">Your story awaits</span>
        <h1 class="text-3xl font-serif font-bold text-journal-dark">Share a Travel Journal</h1>
    </div>

    <div class="bg-white border border-journal-border shadow-postcard p-8">
        <form method="POST" action="{{ route('community.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-5">
                <label class="block text-xs font-bold uppercase tracking-wider text-journal-light mb-2">Story Title *</label>
                <input type="text" name="title" required placeholder="e.g. 7 Magical Days in Morocco..."
                    class="w-full border-b-2 border-journal-border bg-transparent py-2 text-journal-dark placeholder-gray-400 focus:outline-none focus:border-journal-accent text-xl font-serif">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-journal-light mb-2">Link to a Trip (Optional)</label>
                    <select name="trip_id" class="w-full border border-journal-border py-2 px-3 bg-white focus:outline-none focus:border-journal-accent">
                        <option value="">No trip linked</option>
                        @foreach($trips as $trip)
                        <option value="{{ $trip->id }}">{{ $trip->title }} — {{ $trip->destination->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-journal-light mb-2">Visibility</label>
                    <select name="visibility" class="w-full border border-journal-border py-2 px-3 bg-white focus:outline-none focus:border-journal-accent">
                        <option value="public">🌍 Public — visible to everyone</option>
                        <option value="followers">👥 Followers only</option>
                        <option value="private">🔒 Private — only you</option>
                    </select>
                </div>
            </div>

            <div class="mb-5">
                <label class="block text-xs font-bold uppercase tracking-wider text-journal-light mb-2">Your Story *</label>
                <textarea name="body" rows="10" required placeholder="Tell us about your adventure..."
                    class="w-full border border-journal-border py-3 px-4 bg-white focus:outline-none focus:border-journal-accent resize-y text-journal-dark font-serif leading-relaxed"></textarea>
            </div>

            <div class="mb-6">
                <label class="block text-xs font-bold uppercase tracking-wider text-journal-light mb-2">Travel Photos (up to 10)</label>
                <div class="border-2 border-dashed border-journal-border p-8 text-center hover:border-journal-accent transition cursor-pointer" onclick="document.getElementById('photoInput').click()">
                    <i class="fa-solid fa-camera text-3xl text-journal-light mb-2"></i>
                    <p class="text-journal-light text-sm">Click to upload photos (max 5MB each)</p>
                    <input type="file" id="photoInput" name="photos[]" multiple accept="image/*" class="hidden" onchange="previewPhotos(this)">
                </div>
                <div id="photoPreview" class="mt-3 grid grid-cols-4 gap-2 hidden"></div>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('community.feed') }}" class="border-2 border-journal-border text-journal-dark font-bold py-3 px-6 hover:bg-journal-paper transition">Cancel</a>
                <button type="submit" class="flex-1 bg-journal-accent hover:bg-journal-dark text-white font-bold py-3 px-8 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i> Publish Story
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewPhotos(input) {
    const preview = document.getElementById('photoPreview');
    preview.innerHTML = '';
    preview.classList.remove('hidden');
    for (const file of input.files) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const div = document.createElement('div');
            div.className = 'relative aspect-square overflow-hidden border border-journal-border';
            div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endpush

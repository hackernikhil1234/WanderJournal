{{-- AI WanderBot Chatbot --}}
<div x-data="wanderBot()" class="fixed bottom-6 right-6 z-50" id="wanderbot">

    {{-- Toggle Button --}}
    <button @click="open = !open"
        class="w-14 h-14 bg-journal-dark hover:bg-journal-accent rounded-full shadow-xl flex items-center justify-center transition-all duration-300 group"
        :class="open ? 'rotate-12' : ''">
        <i class="fa-solid fa-robot text-2xl text-white group-hover:scale-110 transition-transform"></i>
    </button>

    {{-- Chat Panel --}}
    <div x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute bottom-16 right-0 w-80 bg-white border border-journal-border shadow-2xl flex flex-col"
        style="height: 440px;">

        {{-- Header --}}
        <div class="bg-journal-dark text-white px-4 py-3 flex items-center gap-3">
            <div class="w-8 h-8 bg-journal-gold rounded-full flex items-center justify-center">
                <i class="fa-solid fa-robot text-journal-dark text-sm"></i>
            </div>
            <div>
                <div class="font-bold text-sm">WanderBot</div>
                <div class="text-xs text-gray-400">AI Travel Assistant</div>
            </div>
            <button @click="open = false" class="ml-auto text-gray-400 hover:text-white"><i class="fa-solid fa-times"></i></button>
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-journal-paper" id="chatMessages" x-ref="messages">
            {{-- Welcome --}}
            <div class="flex gap-2">
                <div class="w-7 h-7 bg-journal-dark rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-robot text-white text-xs"></i>
                </div>
                <div class="bg-white border border-journal-border rounded px-3 py-2 text-sm text-journal-dark max-w-[220px]">
                    👋 Hi! I'm WanderBot. Ask me anything about travel — destinations, tips, packing, weather, or planning!
                </div>
            </div>

            {{-- Dynamic messages --}}
            <template x-for="msg in messages" :key="msg.id">
                <div class="flex gap-2" :class="msg.role === 'user' ? 'flex-row-reverse' : ''">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0"
                        :class="msg.role === 'user' ? 'bg-journal-accent' : 'bg-journal-dark'">
                        <i class="text-white text-xs" :class="msg.role === 'user' ? 'fa-solid fa-user' : 'fa-solid fa-robot'"></i>
                    </div>
                    <div class="rounded px-3 py-2 text-sm max-w-[220px]"
                        :class="msg.role === 'user' ? 'bg-journal-accent text-white' : 'bg-white border border-journal-border text-journal-dark'"
                        x-html="msg.text.replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')"></div>
                </div>
            </template>

            {{-- Typing Indicator --}}
            <div x-show="typing" class="flex gap-2">
                <div class="w-7 h-7 bg-journal-dark rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-robot text-white text-xs"></i>
                </div>
                <div class="bg-white border border-journal-border rounded px-4 py-3">
                    <div class="flex gap-1">
                        <span class="w-2 h-2 bg-journal-light rounded-full animate-bounce" style="animation-delay:0ms"></span>
                        <span class="w-2 h-2 bg-journal-light rounded-full animate-bounce" style="animation-delay:150ms"></span>
                        <span class="w-2 h-2 bg-journal-light rounded-full animate-bounce" style="animation-delay:300ms"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Suggestions --}}
        <div x-show="messages.length === 0" class="px-3 py-2 border-t border-journal-border bg-white flex gap-2 overflow-x-auto text-xs">
            <button @click="sendMessage('What should I pack for a beach trip?')" class="flex-shrink-0 bg-journal-paper border border-journal-border px-3 py-1.5 rounded-full hover:bg-journal-accent hover:text-white hover:border-journal-accent transition font-medium">🏖️ Beach packing</button>
            <button @click="sendMessage('Best budget travel destinations 2025?')" class="flex-shrink-0 bg-journal-paper border border-journal-border px-3 py-1.5 rounded-full hover:bg-journal-accent hover:text-white hover:border-journal-accent transition font-medium">💰 Budget trips</button>
            <button @click="sendMessage('Tips for solo travel safety?')" class="flex-shrink-0 bg-journal-paper border border-journal-border px-3 py-1.5 rounded-full hover:bg-journal-accent hover:text-white hover:border-journal-accent transition font-medium">🧳 Solo tips</button>
        </div>

        {{-- Input --}}
        <div class="p-3 border-t border-journal-border bg-white flex gap-2">
            <input type="text" x-model="input" @keyup.enter="sendMessage()"
                placeholder="Ask about any destination..." :disabled="typing"
                class="flex-1 border border-journal-border px-3 py-2 text-sm focus:outline-none focus:border-journal-accent disabled:opacity-50">
            <button @click="sendMessage()" :disabled="typing || !input.trim()"
                class="bg-journal-accent text-white px-4 py-2 hover:bg-journal-dark transition disabled:opacity-50">
                <i class="fa-solid fa-paper-plane text-sm"></i>
            </button>
        </div>
    </div>
</div>

<script>
function wanderBot() {
    return {
        open: false,
        typing: false,
        input: '',
        messages: [],
        msgId: 0,
        async sendMessage(text = null) {
            const msg = text || this.input.trim();
            if (!msg) return;
            this.input = '';
            this.messages.push({ id: ++this.msgId, role: 'user', text: msg });
            this.typing = true;
            this.$nextTick(() => this.scrollToBottom());
            try {
                const r = await fetch('/api/ai/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ message: msg })
                });
                const data = await r.json();
                this.messages.push({ id: ++this.msgId, role: 'bot', text: data.response });
            } catch(e) {
                this.messages.push({ id: ++this.msgId, role: 'bot', text: 'Sorry, I had trouble connecting. Please try again!' });
            } finally {
                this.typing = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },
        scrollToBottom() {
            const el = document.getElementById('chatMessages');
            if (el) el.scrollTop = el.scrollHeight;
        }
    };
}
</script>

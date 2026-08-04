@extends('layouts.authenticated')
@section('title', '- Tanya Langsung Asisten SPBE')
@section('header-title', 'Tanya Langsung - Asisten SPBE')

@section('content')
<div class="container mx-auto px-4 max-w-5xl">

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-purple-700">Tanya Langsung</h1>
            <p class="text-gray-600 text-sm mt-1">Asisten SPBE Kalimantan Utara — konsultasi di dalam portal</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('user.konsultasi-spbe-ai.index') }}"
               class="inline-flex items-center px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                </svg>
                Kembali
            </a>
            @if ($riwayat->isNotEmpty())
                <form method="POST" action="{{ route('user.konsultasi-spbe-ai.clear') }}"
                      onsubmit="return confirm('Hapus seluruh riwayat percakapan Anda?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 text-sm border border-red-300 text-red-600 rounded-lg hover:bg-red-50">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Hapus Riwayat
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 text-sm bg-green-50 border border-green-300 text-green-800 rounded-lg">{{ session('status') }}</div>
    @endif

    @unless ($aiAktif)
        <div class="mb-4 flex items-start gap-2 rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm text-amber-800">
            <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span>
                <strong>Mode prototipe.</strong> Jawaban diambil dari daftar pertanyaan dan dokumen yang telah
                disiapkan admin. Untuk pertanyaan di luar daftar tersebut, silakan gunakan
                <a href="{{ route('user.konsultasi-spbe-ai.access') }}" target="_blank" class="underline font-semibold">Tanya via ChatGPT</a>.
            </span>
        </div>
    @endunless

    <div class="bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col" style="height: 70vh; min-height: 460px;">

        {{-- Header percakapan --}}
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-5 py-3 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                </svg>
            </div>
            <div class="text-white">
                <p class="font-semibold leading-tight">Asisten SPBE Kalimantan Utara</p>
                <p class="text-xs text-purple-100">
                    <span class="inline-block w-2 h-2 rounded-full bg-green-400 mr-1"></span>
                    {{ $aiAktif ? 'Aktif' : 'Mode prototipe' }}
                </p>
            </div>
        </div>

        {{-- Daftar pesan --}}
        <div id="chat-messages" class="flex-1 overflow-y-auto px-4 py-5 space-y-4 bg-gray-50">
            <div class="flex justify-start">
                <div class="max-w-[85%] bg-white border border-gray-200 rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm">
                    <p class="text-gray-800 text-sm leading-relaxed">
                        Selamat datang. Saya Asisten SPBE Kalimantan Utara. Silakan ajukan pertanyaan
                        seputar SPBE, regulasi digitalisasi pemerintahan, atau prosedur layanan digital
                        Diskominfo Provinsi Kalimantan Utara.
                    </p>
                </div>
            </div>

            @foreach ($riwayat as $chat)
                <div class="flex justify-end">
                    <div class="max-w-[85%] bg-purple-600 text-white rounded-2xl rounded-tr-sm px-4 py-3 shadow-sm">
                        <p class="text-sm leading-relaxed whitespace-pre-line">{{ $chat->pertanyaan }}</p>
                        <p class="text-[11px] text-purple-200 mt-1 text-right">{{ $chat->created_at->format('H:i') }}</p>
                    </div>
                </div>
                <div class="flex justify-start">
                    <div class="max-w-[85%] bg-white border border-gray-200 rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm">
                        <div class="text-gray-800 text-sm leading-relaxed prose-chat">
                            {!! \App\Services\KonsultasiAiService::formatToHtml($chat->jawaban) !!}
                        </div>
                        <p class="text-[11px] text-gray-400 mt-2">
                            {{ \App\Services\KonsultasiAiService::labelSumber($chat->sumber) }} &middot; {{ $chat->created_at->format('H:i') }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Contoh pertanyaan --}}
        @if ($contohPertanyaan->isNotEmpty())
            <div id="chat-suggestions" class="px-4 py-3 border-t border-gray-200 bg-white">
                <p class="text-xs font-semibold text-gray-500 mb-2">Contoh pertanyaan:</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($contohPertanyaan as $contoh)
                        <button type="button"
                                class="chat-suggestion text-xs px-3 py-1.5 rounded-full border border-purple-200 bg-purple-50 text-purple-700 hover:bg-purple-100 transition"
                                data-pertanyaan="{{ $contoh->pertanyaan }}">
                            {{ $contoh->pertanyaan }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Input --}}
        <div class="px-4 py-3 border-t border-gray-200 bg-white">
            <form id="chat-form" class="flex items-end gap-2">
                @csrf
                <textarea id="chat-input" rows="1" maxlength="2000" required
                          placeholder="Tulis pertanyaan Anda, lalu tekan Enter…"
                          class="flex-1 resize-none border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent"
                          style="max-height: 140px;"></textarea>
                <button type="submit" id="chat-send"
                        class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-purple-600 text-white hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                    </svg>
                </button>
            </form>
            <p class="text-[11px] text-gray-400 mt-2">
                Jawaban asisten bersifat membantu dan bukan pengganti ketentuan resmi. Jangan memasukkan data pribadi atau informasi rahasia.
            </p>
        </div>
    </div>
</div>

<style>
    .prose-chat strong { font-weight: 600; }
    .prose-chat em { font-style: italic; color: #6b7280; }
    .prose-chat ul { list-style: disc; padding-left: 1.25rem; margin: 0.5rem 0; }
    .prose-chat ol { list-style: decimal; padding-left: 1.25rem; margin: 0.5rem 0; }
    .prose-chat li { margin: 0.15rem 0; }
    .prose-chat p { margin: 0.35rem 0; }
    .prose-chat p:first-child { margin-top: 0; }
    .prose-chat p:last-child { margin-bottom: 0; }

    .typing-dot {
        width: 7px; height: 7px; border-radius: 9999px; background: #a78bfa;
        display: inline-block; animation: typing 1.2s infinite ease-in-out;
    }
    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }
    @keyframes typing {
        0%, 60%, 100% { opacity: 0.3; transform: translateY(0); }
        30% { opacity: 1; transform: translateY(-3px); }
    }
</style>

<script>
(function () {
    const askUrl   = @json(route('user.konsultasi-spbe-ai.ask'));
    const csrf     = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                     || document.querySelector('#chat-form input[name="_token"]')?.value;
    const messages = document.getElementById('chat-messages');
    const form     = document.getElementById('chat-form');
    const input    = document.getElementById('chat-input');
    const sendBtn  = document.getElementById('chat-send');

    const LABEL_SUMBER = {
        ai: 'Dijawab asisten AI',
        faq: 'Dari basis pengetahuan',
        dokumen: 'Dari dokumen knowledge base',
        fallback: 'Belum tersedia di basis pengetahuan',
    };

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Pemformatan ringan: tebal, miring, dan daftar berpoin/bernomor.
    function formatAnswer(text) {
        const blocks = escapeHtml(text).split(/\n\s*\n/);

        return blocks.map(function (block) {
            const lines = block.split('\n').map(l => l.trim()).filter(l => l !== '');
            if (lines.length === 0) return '';

            const inline = s => s
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                .replace(/(^|[\s(])_(.+?)_(?=[\s.,)]|$)/g, '$1<em>$2</em>');

            if (lines.every(l => /^[-*]\s+/.test(l))) {
                return '<ul>' + lines.map(l => '<li>' + inline(l.replace(/^[-*]\s+/, '')) + '</li>').join('') + '</ul>';
            }
            if (lines.every(l => /^\d+[.)]\s+/.test(l))) {
                return '<ol>' + lines.map(l => '<li>' + inline(l.replace(/^\d+[.)]\s+/, '')) + '</li>').join('') + '</ol>';
            }
            return '<p>' + inline(lines.join('<br>')) + '</p>';
        }).join('');
    }

    function scrollBottom() {
        messages.scrollTop = messages.scrollHeight;
    }

    function addUserMessage(text) {
        const wrap = document.createElement('div');
        wrap.className = 'flex justify-end';
        wrap.innerHTML =
            '<div class="max-w-[85%] bg-purple-600 text-white rounded-2xl rounded-tr-sm px-4 py-3 shadow-sm">' +
                '<p class="text-sm leading-relaxed whitespace-pre-line">' + escapeHtml(text) + '</p>' +
            '</div>';
        messages.appendChild(wrap);
        scrollBottom();
    }

    function addTyping() {
        const wrap = document.createElement('div');
        wrap.className = 'flex justify-start';
        wrap.id = 'chat-typing';
        wrap.innerHTML =
            '<div class="bg-white border border-gray-200 rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm">' +
                '<span class="typing-dot"></span> <span class="typing-dot"></span> <span class="typing-dot"></span>' +
            '</div>';
        messages.appendChild(wrap);
        scrollBottom();
        return wrap;
    }

    function addBotMessage(jawaban, sumber, waktu) {
        const wrap = document.createElement('div');
        wrap.className = 'flex justify-start';
        const meta = (LABEL_SUMBER[sumber] || 'Jawaban') + (waktu ? ' &middot; ' + escapeHtml(waktu) : '');
        wrap.innerHTML =
            '<div class="max-w-[85%] bg-white border border-gray-200 rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm">' +
                '<div class="text-gray-800 text-sm leading-relaxed prose-chat">' + formatAnswer(jawaban) + '</div>' +
                '<p class="text-[11px] text-gray-400 mt-2">' + meta + '</p>' +
            '</div>';
        messages.appendChild(wrap);
        scrollBottom();
    }

    function addErrorMessage(text) {
        const wrap = document.createElement('div');
        wrap.className = 'flex justify-start';
        wrap.innerHTML =
            '<div class="max-w-[85%] bg-red-50 border border-red-200 rounded-2xl rounded-tl-sm px-4 py-3">' +
                '<p class="text-red-700 text-sm">' + escapeHtml(text) + '</p>' +
            '</div>';
        messages.appendChild(wrap);
        scrollBottom();
    }

    async function kirim(pertanyaan) {
        addUserMessage(pertanyaan);
        input.value = '';
        input.style.height = 'auto';
        sendBtn.disabled = true;
        const typing = addTyping();

        try {
            const res = await fetch(askUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ pertanyaan: pertanyaan }),
            });

            typing.remove();

            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                const pesan = data?.errors?.pertanyaan?.[0]
                    || data?.message
                    || 'Terjadi kesalahan (kode ' + res.status + '). Silakan coba lagi.';
                addErrorMessage(pesan);
                return;
            }

            const data = await res.json();
            addBotMessage(data.jawaban, data.sumber, data.waktu);
        } catch (e) {
            typing.remove();
            addErrorMessage('Tidak dapat terhubung ke server. Periksa koneksi Anda lalu coba lagi.');
        } finally {
            sendBtn.disabled = false;
            input.focus();
        }
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const pertanyaan = input.value.trim();
        if (pertanyaan.length < 3 || sendBtn.disabled) return;
        kirim(pertanyaan);
    });

    // Enter mengirim, Shift+Enter membuat baris baru.
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.requestSubmit();
        }
    });

    // Textarea tumbuh mengikuti isi.
    input.addEventListener('input', function () {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 140) + 'px';
    });

    document.querySelectorAll('.chat-suggestion').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (sendBtn.disabled) return;
            kirim(btn.dataset.pertanyaan);
        });
    });

    scrollBottom();
    input.focus();
})();
</script>
@endsection

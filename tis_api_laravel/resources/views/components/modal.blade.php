@props([
    'id',
    'title' => '',
    'maxWidth' => 'md',
])

<div
    id="{{ $id }}"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50"
    onclick="if (event.target === this) closeModal('{{ $id }}')"
>
    <div
        class="relative mx-4 w-full max-w-{{ $maxWidth }} rounded-xl bg-white p-6 shadow-2xl dark:bg-zinc-800"
        onclick="event.stopPropagation()"
    >
        @if($title)
        <div class="mb-4 flex items-center justify-between border-b border-zinc-200 pb-4 dark:border-zinc-700">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $title }}</h3>
            <button
                type="button"
                onclick="closeModal('{{ $id }}')"
                class="rounded-lg p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-300"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endif

        {{ $slot }}
    </div>
</div>

@push('scripts')
<script>
if (typeof window.openModal !== 'function') {
    window.openModal = function(id) {
        const el = document.getElementById(id);
        if (el) { el.classList.remove('hidden'); el.classList.add('flex'); }
    };
    window.closeModal = function(id) {
        const el = document.getElementById(id);
        if (el) { el.classList.add('hidden'); el.classList.remove('flex'); }
    };
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.fixed.inset-0.z-50.flex').forEach(function(el) {
                window.closeModal(el.id);
            });
        }
    });
}
</script>
@endpush

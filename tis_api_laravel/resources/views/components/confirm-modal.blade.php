@props([
    'id',
    'title' => 'Confirm',
    'confirmText' => 'Yes',
    'cancelText' => 'Cancel',
    'confirmClass' => 'bg-blue-600 hover:bg-blue-700 text-white',
])

<x-modal :id="$id" :title="$title">
    <p id="{{ $id }}-message" class="text-sm text-zinc-600 dark:text-zinc-400"></p>

    <div id="{{ $id }}-form-wrap" class="hidden">
        <form id="{{ $id }}-form" method="POST">
            @csrf
            @method('DELETE')
        </form>
    </div>

    <div class="mt-6 flex justify-end gap-3">
        <button
            type="button"
            onclick="closeModal('{{ $id }}')"
            class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200"
        >
            {{ $cancelText }}
        </button>

        <a
            id="{{ $id }}-link"
            href="#"
            class="rounded-lg px-4 py-2 text-sm font-medium hidden {{ $confirmClass }}"
        >
            {{ $confirmText }}
        </a>

        <button
            id="{{ $id }}-btn"
            type="submit"
            form="{{ $id }}-form"
            class="rounded-lg px-4 py-2 text-sm font-medium hidden {{ $confirmClass }}"
        >
            {{ $confirmText }}
        </button>
    </div>
</x-modal>

@push('scripts')
<script>
if (typeof window.confirmAction !== 'function') {
    window.confirmAction = function(modalId, message, url, method) {
        method = (method || 'GET').toUpperCase();

        const el = document.getElementById(modalId);
        if (!el) return;

        el.querySelector('#' + modalId + '-message').textContent = message;

        const link = el.querySelector('#' + modalId + '-link');
        const btn  = el.querySelector('#' + modalId + '-btn');
        const wrap = el.querySelector('#' + modalId + '-form-wrap');

        if (method === 'GET') {
            link.href = url;
            link.classList.remove('hidden');
            btn.classList.add('hidden');
            wrap.classList.add('hidden');
        } else {
            el.querySelector('#' + modalId + '-form').action = url;
            link.classList.add('hidden');
            btn.classList.remove('hidden');
            wrap.classList.remove('hidden');
        }

        openModal(modalId);
    };
}
</script>
@endpush

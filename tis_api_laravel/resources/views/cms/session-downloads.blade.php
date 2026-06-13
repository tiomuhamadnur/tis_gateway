<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-3 sm:p-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Session Downloads</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Search for sessions and download CSV and PDF files from gateway uploads</p>
        </div>
    </div>

    @if(session()->has('success'))
    <div class="flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif

    <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex flex-wrap items-center gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
            <div class="relative flex-1 min-w-[240px]">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search session id / rake / file name..." class="w-full rounded-lg border border-zinc-200 bg-zinc-50 py-2 pl-9 pr-4 text-sm focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100 dark:placeholder-zinc-400">
            </div>

            <div class="min-w-[160px]">
                <input wire:model.live="from" type="date" class="w-full rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100">
            </div>

            <div class="min-w-[160px]">
                <input wire:model.live="to" type="date" class="w-full rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-100">
            </div>

            <button wire:click="clearFilters" class="inline-flex items-center rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-700">
                Reset
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Session Date</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Session Name</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Trainset</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Files</th>
                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                    @forelse($sessions as $session)
                    @php
                        $csvFile = $session->uploadedFiles->first(fn ($file) => strtolower(pathinfo($file->original_filename, PATHINFO_EXTENSION)) === 'csv');
                        $pdfFile = $session->uploadedFiles->first(fn ($file) => strtolower(pathinfo($file->original_filename, PATHINFO_EXTENSION)) === 'pdf');
                    @endphp
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                        <td class="px-5 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                            {{ $session->read_time?->format('d M Y H:i:s') ?? '-' }}
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $session->session_id }}</span>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $session->uploaded_files_count }} file</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                            TS-{{ str_pad((int) $session->rake_id, 2, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-5 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                            <div class="space-y-1">
                                <div>CSV: {{ $csvFile?->original_filename ?? '-' }}</div>
                                <div>PDF: {{ $pdfFile?->original_filename ?? '-' }}</div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-right text-sm">
                            <div class="inline-flex flex-wrap justify-end gap-2">
                                <a href="{{ route('sessions.download.csv', $session->session_id) }}" class="inline-flex items-center rounded-md border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700 hover:bg-green-100 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
                                    CSV
                                </a>
                                <a href="{{ route('sessions.download.pdf', $session->session_id) }}" class="inline-flex items-center rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
                                    PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-sm text-zinc-400">
                            @if($search || $from || $to)
                            No matching sessions.
                            @else
                            No session uploads yet.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-700">
            {{ $sessions->links() }}
        </div>
    </div>
</div>

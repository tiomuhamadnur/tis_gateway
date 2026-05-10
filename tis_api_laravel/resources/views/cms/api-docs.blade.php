<div x-data="apiDocs()" class="flex min-h-screen">

    @push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script>const _APP_URL = '{{ rtrim(config("app.url"), "/") }}';</script>
    @verbatim
    <script>
    function apiDocs() {
        return {
            active: 'post-failures',
            init() {
                document.querySelectorAll('pre code').forEach(el => hljs.highlightElement(el));
                const observer = new IntersectionObserver(entries => {
                    entries.forEach(e => { if (e.isIntersecting) this.active = e.target.id; });
                }, { rootMargin: '-30% 0px -60% 0px' });
                document.querySelectorAll('.endpoint-section').forEach(el => observer.observe(el));
            },
            copy(btn) {
                const text = btn.closest('.code-wrap').querySelector('code').innerText;
                navigator.clipboard.writeText(text).then(() => {
                    btn.textContent = 'Copied!';
                    setTimeout(() => btn.textContent = 'Copy', 1500);
                });
            },
            downloadPostman() {
                const baseUrl = _APP_URL;
                const collection = {
                    info: {
                        name: 'TIS Gateway API',
                        description: 'TIS Gateway REST API — semua endpoint membutuhkan Bearer token.',
                        schema: 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
                    },
                    variable: [
                        { key: 'base_url', value: baseUrl, type: 'string' },
                        { key: 'api_key',  value: 'your-api-key-here', type: 'string' }
                    ],
                    item: [
                        {
                            name: 'Failures',
                            item: [
                                {
                                    name: 'POST /failures — Kirim batch failure records',
                                    request: {
                                        method: 'POST',
                                        header: [
                                            { key: 'Authorization', value: 'Bearer {{api_key}}' },
                                            { key: 'Content-Type',  value: 'application/json' },
                                            { key: 'Accept',        value: 'application/json' }
                                        ],
                                        url: { raw: '{{base_url}}/api/v1/failures', host: ['{{base_url}}'], path: ['api','v1','failures'] },
                                        body: {
                                            mode: 'raw',
                                            raw: JSON.stringify({
                                                rake_id: 'KRL-001',
                                                records: [{
                                                    timestamp: '2025-05-08T09:30:00Z',
                                                    equipment_name: 'Traction Motor A',
                                                    fault_name: 'OVERHEAT_TM_A',
                                                    classification: 'heavy',
                                                    description: 'Temperature exceeded 120°C',
                                                    additional_data: { temperature_c: 134.2 }
                                                }]
                                            }, null, 2)
                                        }
                                    }
                                },
                                {
                                    name: 'GET /failures — Daftar session (paginated)',
                                    request: {
                                        method: 'GET',
                                        header: [
                                            { key: 'Authorization', value: 'Bearer {{api_key}}' },
                                            { key: 'Accept',        value: 'application/json' }
                                        ],
                                        url: {
                                            raw: '{{base_url}}/api/v1/failures?rake_id=&from=&to=&per_page=15&page=1',
                                            host: ['{{base_url}}'], path: ['api','v1','failures'],
                                            query: [
                                                { key: 'rake_id',  value: '',   description: 'Filter by rake' },
                                                { key: 'from',     value: '',   description: 'YYYY-MM-DD' },
                                                { key: 'to',       value: '',   description: 'YYYY-MM-DD' },
                                                { key: 'per_page', value: '15', description: 'Items per page' },
                                                { key: 'page',     value: '1',  description: 'Page number' }
                                            ]
                                        }
                                    }
                                },
                                {
                                    name: 'GET /failures/{sessionId} — Detail session',
                                    request: {
                                        method: 'GET',
                                        header: [
                                            { key: 'Authorization', value: 'Bearer {{api_key}}' },
                                            { key: 'Accept',        value: 'application/json' }
                                        ],
                                        url: { raw: '{{base_url}}/api/v1/failures/:sessionId', host: ['{{base_url}}'], path: ['api','v1','failures',':sessionId'], variable: [{ key: 'sessionId', value: '550e8400-e29b-41d4-a716-446655440000' }] }
                                    }
                                }
                            ]
                        },
                        {
                            name: 'Files',
                            item: [{
                                name: 'POST /files — Upload CSV atau PDF',
                                request: {
                                    method: 'POST',
                                    header: [{ key: 'Authorization', value: 'Bearer {{api_key}}' }],
                                    url: { raw: '{{base_url}}/api/v1/files', host: ['{{base_url}}'], path: ['api','v1','files'] },
                                    body: { mode: 'formdata', formdata: [{ key: 'rake_id', value: 'KRL-001', type: 'text' }, { key: 'file', type: 'file', src: '' }] }
                                }
                            }]
                        },
                        {
                            name: 'Analytics',
                            item: [
                                {
                                    name: 'GET /dashboard — Statistik keseluruhan',
                                    request: {
                                        method: 'GET',
                                        header: [{ key: 'Authorization', value: 'Bearer {{api_key}}' }, { key: 'Accept', value: 'application/json' }],
                                        url: { raw: '{{base_url}}/api/v1/dashboard', host: ['{{base_url}}'], path: ['api','v1','dashboard'] }
                                    }
                                },
                                {
                                    name: 'GET /analytics/trend — Tren failure per periode',
                                    request: {
                                        method: 'GET',
                                        header: [{ key: 'Authorization', value: 'Bearer {{api_key}}' }, { key: 'Accept', value: 'application/json' }],
                                        url: {
                                            raw: '{{base_url}}/api/v1/analytics/trend?from=2025-05-01&to=2025-05-08&group_by=day',
                                            host: ['{{base_url}}'], path: ['api','v1','analytics','trend'],
                                            query: [{ key: 'from', value: '2025-05-01' }, { key: 'to', value: '2025-05-08' }, { key: 'group_by', value: 'day' }, { key: 'rake_id', value: '', disabled: true }]
                                        }
                                    }
                                },
                                {
                                    name: 'GET /analytics/pareto — Analisis Pareto',
                                    request: {
                                        method: 'GET',
                                        header: [{ key: 'Authorization', value: 'Bearer {{api_key}}' }, { key: 'Accept', value: 'application/json' }],
                                        url: {
                                            raw: '{{base_url}}/api/v1/analytics/pareto?start_date=&end_date=',
                                            host: ['{{base_url}}'], path: ['api','v1','analytics','pareto'],
                                            query: [
                                                { key: 'start_date',   value: '', disabled: true },
                                                { key: 'end_date',     value: '', disabled: true },
                                                { key: 'start_time',   value: '', disabled: true },
                                                { key: 'end_time',     value: '', disabled: true },
                                                { key: 'failure_type', value: '', disabled: true },
                                                { key: 'rake_id',      value: '', disabled: true }
                                            ]
                                        }
                                    }
                                }
                            ]
                        },
                        {
                            name: 'Health',
                            item: [{
                                name: 'GET /health — Health check',
                                request: {
                                    method: 'GET',
                                    header: [{ key: 'Authorization', value: 'Bearer {{api_key}}' }],
                                    url: { raw: '{{base_url}}/api/v1/health', host: ['{{base_url}}'], path: ['api','v1','health'] }
                                }
                            }]
                        }
                    ]
                };
                const blob = new Blob([JSON.stringify(collection, null, 2)], { type: 'application/json' });
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = 'tis-gateway-api.postman_collection.json';
                a.click();
            }
        };
    }
    </script>
    @endverbatim
    <style>
        .method-get  { background:#14532d; color:#86efac; }
        .method-post { background:#1e3a5f; color:#93c5fd; }
        .method-badge { display:inline-flex; align-items:center; font-size:.68rem; font-weight:700;
                        letter-spacing:.06em; padding:2px 7px; border-radius:4px; min-width:48px;
                        justify-content:center; flex-shrink:0; }
        .nav-item.active { background: rgb(39 39 42); color: #fff; }
        .nav-item { transition: background .15s; border-radius: 6px; }
        .endpoint-section { scroll-margin-top: 24px; }
        .param-required { color: #f87171; font-size: .65rem; font-weight: 600; }
        .param-optional { color: #6b7280; font-size: .65rem; }
        pre { margin: 0; }
        .hljs { background: #0d1117 !important; border-radius: 0 0 6px 6px; font-size: .78rem; line-height: 1.6; }
        .code-header { background: #161b22; border-radius: 6px 6px 0 0; border-bottom: 1px solid #30363d; }
    </style>
    @endpush

    {{-- ── Left sticky nav ── --}}
    <aside class="hidden lg:flex flex-col gap-1 w-52 shrink-0 sticky top-0 self-start h-screen overflow-y-auto px-3 py-6 border-r border-zinc-700">
        <p class="text-[10px] font-semibold uppercase tracking-widest text-zinc-500 px-2 mb-1">Endpoints</p>

        <a href="#post-failures" x-on:click="active='post-failures'" :class="active==='post-failures' ? 'active' : ''" class="nav-item flex items-center gap-2 px-2 py-1.5 text-xs text-zinc-400 hover:text-white">
            <span class="method-badge method-post">POST</span> /failures
        </a>
        <a href="#get-failures" x-on:click="active='get-failures'" :class="active==='get-failures' ? 'active' : ''" class="nav-item flex items-center gap-2 px-2 py-1.5 text-xs text-zinc-400 hover:text-white">
            <span class="method-badge method-get">GET</span> /failures
        </a>
        <a href="#get-failure-id" x-on:click="active='get-failure-id'" :class="active==='get-failure-id' ? 'active' : ''" class="nav-item flex items-center gap-2 px-2 py-1.5 text-xs text-zinc-400 hover:text-white">
            <span class="method-badge method-get">GET</span> /failures/:id
        </a>
        <a href="#post-files" x-on:click="active='post-files'" :class="active==='post-files' ? 'active' : ''" class="nav-item flex items-center gap-2 px-2 py-1.5 text-xs text-zinc-400 hover:text-white">
            <span class="method-badge method-post">POST</span> /files
        </a>

        <p class="text-[10px] font-semibold uppercase tracking-widest text-zinc-500 px-2 mt-3 mb-1">Analytics</p>
        <a href="#get-dashboard" x-on:click="active='get-dashboard'" :class="active==='get-dashboard' ? 'active' : ''" class="nav-item flex items-center gap-2 px-2 py-1.5 text-xs text-zinc-400 hover:text-white">
            <span class="method-badge method-get">GET</span> /dashboard
        </a>
        <a href="#get-trend" x-on:click="active='get-trend'" :class="active==='get-trend' ? 'active' : ''" class="nav-item flex items-center gap-2 px-2 py-1.5 text-xs text-zinc-400 hover:text-white">
            <span class="method-badge method-get">GET</span> /trend
        </a>
        <a href="#get-pareto" x-on:click="active='get-pareto'" :class="active==='get-pareto' ? 'active' : ''" class="nav-item flex items-center gap-2 px-2 py-1.5 text-xs text-zinc-400 hover:text-white">
            <span class="method-badge method-get">GET</span> /pareto
        </a>

        <p class="text-[10px] font-semibold uppercase tracking-widest text-zinc-500 px-2 mt-3 mb-1">System</p>
        <a href="#get-health" x-on:click="active='get-health'" :class="active==='get-health' ? 'active' : ''" class="nav-item flex items-center gap-2 px-2 py-1.5 text-xs text-zinc-400 hover:text-white">
            <span class="method-badge method-get">GET</span> /health
        </a>
    </aside>

    {{-- ── Main content ── --}}
    <main class="flex-1 min-w-0 px-6 py-6 space-y-2">

        {{-- Page header --}}
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-zinc-100">API Reference</h1>
                <p class="text-xs text-zinc-500 mt-0.5">Base URL: <code class="bg-zinc-800 px-1.5 py-0.5 rounded text-zinc-300">{{ rtrim(config('app.url'), '/') }}/api/v1</code></p>
            </div>
            <button x-on:click="downloadPostman()" class="flex items-center gap-2 rounded-lg bg-orange-600 hover:bg-orange-500 px-3 py-2 text-xs font-semibold text-white transition shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm-1 14.41V7.59l5 4.41z"/></svg>
                Download Postman Collection
            </button>
        </div>

        {{-- Auth banner --}}
        <div class="flex items-center gap-3 rounded-lg border border-zinc-700 bg-zinc-800/50 px-4 py-3 mb-6">
            <svg class="w-4 h-4 text-yellow-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m9-7V8a7 7 0 10-14 0v2a2 2 0 00-2 2v5a2 2 0 002 2h14a2 2 0 002-2v-5a2 2 0 00-2-2z"/></svg>
            <p class="text-xs text-zinc-300">Semua endpoint wajib menyertakan header <code class="bg-zinc-700 px-1 rounded">Authorization: Bearer {TIS_API_KEY}</code></p>
        </div>

        {{-- ═══ POST /failures ═══ --}}
        <div id="post-failures" class="endpoint-section rounded-xl border border-zinc-700 bg-zinc-800/40 overflow-hidden">
            <div class="flex items-center gap-3 px-5 py-3 border-b border-zinc-700/60">
                <span class="method-badge method-post">POST</span>
                <code class="text-sm font-mono text-zinc-100 font-semibold">/api/v1/failures</code>
                <span class="ml-auto text-xs text-zinc-500">Kirim batch failure records dari TIS</span>
            </div>
            <div class="grid lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-zinc-700/60">
                {{-- Left: params --}}
                <div class="p-5 space-y-4">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-zinc-500 mb-2">Request Body (JSON)</p>
                        <div class="space-y-2">
                            @php $fields = [
                                ['rake_id','string','required','Identitas rake / trainset'],
                                ['records','array','required','Array objek failure'],
                                ['records[].timestamp','datetime','required','ISO 8601, mis. 2025-05-08T09:30:00Z'],
                                ['records[].equipment_name','string','required','Nama komponen'],
                                ['records[].fault_name','string','required','Kode fault'],
                                ['records[].classification','string','required','heavy | medium | light'],
                                ['records[].description','string','optional','Keterangan tambahan'],
                                ['records[].additional_data','any','optional','Data bebas (object/string/dll)'],
                            ]; @endphp
                            @foreach($fields as [$name, $type, $req, $desc])
                            <div class="flex items-start gap-3 text-xs">
                                <code class="shrink-0 font-mono text-blue-300">{{ $name }}</code>
                                <span class="text-zinc-500 shrink-0">{{ $type }}</span>
                                <span @class(['param-required' => $req==='required', 'param-optional' => $req==='optional', 'shrink-0'=>true])>{{ strtoupper($req) }}</span>
                                <span class="text-zinc-400">{{ $desc }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="rounded-lg border border-zinc-700/60 bg-zinc-900/60 px-3 py-2 text-xs text-zinc-400">
                        Response <span class="text-blue-400 font-mono font-semibold">201</span>: <code>session_id</code>, <code>received</code>, <code>status</code>
                    </div>
                </div>
                {{-- Right: example --}}
                <div class="p-5 space-y-3">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-zinc-500">Request Example</p>
                    <div class="code-wrap rounded-lg overflow-hidden border border-zinc-700/40">
                        <div class="code-header flex items-center justify-between px-3 py-1.5">
                            <span class="text-[10px] text-zinc-500">JSON</span>
                            <button x-on:click="copy($el)" class="text-[10px] text-zinc-400 hover:text-white transition">Copy</button>
                        </div>
                        <pre><code class="language-json">{
  "rake_id": "KRL-001",
  "records": [
    {
      "timestamp": "2025-05-08T09:30:00Z",
      "equipment_name": "Traction Motor A",
      "fault_name": "OVERHEAT_TM_A",
      "classification": "heavy",
      "description": "Temp 134°C, threshold 120°C",
      "additional_data": { "temperature_c": 134.2 }
    }
  ]
}</code></pre>
                    </div>
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-zinc-500">Response 201</p>
                    <div class="code-wrap rounded-lg overflow-hidden border border-zinc-700/40">
                        <div class="code-header flex items-center justify-between px-3 py-1.5">
                            <span class="text-[10px] text-zinc-500">JSON</span>
                            <button x-on:click="copy($el)" class="text-[10px] text-zinc-400 hover:text-white transition">Copy</button>
                        </div>
                        <pre><code class="language-json">{
  "session_id": "550e8400-e29b-41d4-a716-446655440000",
  "received": 1,
  "status": "success"
}</code></pre>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ GET /failures ═══ --}}
        <div id="get-failures" class="endpoint-section rounded-xl border border-zinc-700 bg-zinc-800/40 overflow-hidden">
            <div class="flex items-center gap-3 px-5 py-3 border-b border-zinc-700/60">
                <span class="method-badge method-get">GET</span>
                <code class="text-sm font-mono text-zinc-100 font-semibold">/api/v1/failures</code>
                <span class="ml-auto text-xs text-zinc-500">Daftar session failure (paginated)</span>
            </div>
            <div class="grid lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-zinc-700/60">
                <div class="p-5 space-y-3">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-zinc-500 mb-2">Query Parameters</p>
                    @php $params = [
                        ['rake_id','string','optional','Filter berdasarkan rake'],
                        ['from','date','optional','Tanggal awal YYYY-MM-DD (bersama to)'],
                        ['to','date','optional','Tanggal akhir YYYY-MM-DD (bersama from)'],
                        ['per_page','integer','optional','Jumlah per halaman, default 15'],
                        ['page','integer','optional','Nomor halaman, default 1'],
                    ]; @endphp
                    <div class="space-y-2">
                        @foreach($params as [$name, $type, $req, $desc])
                        <div class="flex items-start gap-3 text-xs">
                            <code class="shrink-0 font-mono text-green-300">{{ $name }}</code>
                            <span class="text-zinc-500 shrink-0">{{ $type }}</span>
                            <span class="param-optional shrink-0">OPT</span>
                            <span class="text-zinc-400">{{ $desc }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="p-5 space-y-3">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-zinc-500">Response 200 — paginated sessions</p>
                    <div class="code-wrap rounded-lg overflow-hidden border border-zinc-700/40">
                        <div class="code-header flex items-center justify-between px-3 py-1.5">
                            <span class="text-[10px] text-zinc-500">JSON</span>
                            <button x-on:click="copy($el)" class="text-[10px] text-zinc-400 hover:text-white transition">Copy</button>
                        </div>
                        <pre><code class="language-json">{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "session_id": "550e8400-...",
      "rake_id": "KRL-001",
      "download_date": "2025-05-08T09:30:00Z",
      "total_records": 2,
      "status": "completed",
      "failure_records": [ ... ]
    }
  ],
  "per_page": 15,
  "total": 48,
  "last_page": 4,
  "next_page_url": "...?page=2",
  "prev_page_url": null
}</code></pre>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ GET /failures/{sessionId} ═══ --}}
        <div id="get-failure-id" class="endpoint-section rounded-xl border border-zinc-700 bg-zinc-800/40 overflow-hidden">
            <div class="flex items-center gap-3 px-5 py-3 border-b border-zinc-700/60">
                <span class="method-badge method-get">GET</span>
                <code class="text-sm font-mono text-zinc-100 font-semibold">/api/v1/failures/{sessionId}</code>
                <span class="ml-auto text-xs text-zinc-500">Detail session beserta semua record</span>
            </div>
            <div class="grid lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-zinc-700/60">
                <div class="p-5 space-y-3">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-zinc-500 mb-2">Path Parameter</p>
                    <div class="flex items-start gap-3 text-xs">
                        <code class="shrink-0 font-mono text-green-300">sessionId</code>
                        <span class="text-zinc-500 shrink-0">UUID</span>
                        <span class="param-required shrink-0">REQ</span>
                        <span class="text-zinc-400">UUID session yang dikembalikan dari POST /failures</span>
                    </div>
                    <div class="rounded-lg border border-red-900/40 bg-red-900/10 px-3 py-2 text-xs text-red-300 mt-3">
                        Returns <strong>404</strong> jika sessionId tidak ditemukan.
                    </div>
                </div>
                <div class="p-5 space-y-3">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-zinc-500">Response 200</p>
                    <div class="code-wrap rounded-lg overflow-hidden border border-zinc-700/40">
                        <div class="code-header flex items-center justify-between px-3 py-1.5">
                            <span class="text-[10px] text-zinc-500">JSON</span>
                            <button x-on:click="copy($el)" class="text-[10px] text-zinc-400 hover:text-white transition">Copy</button>
                        </div>
                        <pre><code class="language-json">{
  "session": {
    "id": 1,
    "session_id": "550e8400-...",
    "rake_id": "KRL-001",
    "download_date": "2025-05-08T09:30:00Z",
    "total_records": 2,
    "status": "completed"
  },
  "records": [
    {
      "id": 1,
      "timestamp": "2025-05-08T09:30:00Z",
      "equipment_name": "Traction Motor A",
      "fault_name": "OVERHEAT_TM_A",
      "classification": "heavy",
      "description": "Temp 134°C",
      "additional_data": { "temperature_c": 134.2 }
    }
  ]
}</code></pre>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ POST /files ═══ --}}
        <div id="post-files" class="endpoint-section rounded-xl border border-zinc-700 bg-zinc-800/40 overflow-hidden">
            <div class="flex items-center gap-3 px-5 py-3 border-b border-zinc-700/60">
                <span class="method-badge method-post">POST</span>
                <code class="text-sm font-mono text-zinc-100 font-semibold">/api/v1/files</code>
                <span class="ml-auto text-xs text-zinc-500">Upload file CSV atau PDF (max 10 MB)</span>
            </div>
            <div class="grid lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-zinc-700/60">
                <div class="p-5 space-y-3">
                    <div class="flex items-center gap-2 rounded-md bg-amber-900/20 border border-amber-800/40 px-3 py-2 text-xs text-amber-300 mb-3">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/></svg>
                        Gunakan <strong>multipart/form-data</strong>, bukan JSON.
                    </div>
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-zinc-500 mb-2">Form Fields</p>
                    <div class="space-y-2">
                        <div class="flex items-start gap-3 text-xs">
                            <code class="shrink-0 font-mono text-blue-300">rake_id</code>
                            <span class="text-zinc-500 shrink-0">string</span>
                            <span class="param-required shrink-0">REQ</span>
                            <span class="text-zinc-400">Identitas rake / trainset</span>
                        </div>
                        <div class="flex items-start gap-3 text-xs">
                            <code class="shrink-0 font-mono text-blue-300">file</code>
                            <span class="text-zinc-500 shrink-0">file</span>
                            <span class="param-required shrink-0">REQ</span>
                            <span class="text-zinc-400">File CSV atau PDF, maks 10 MB</span>
                        </div>
                    </div>
                </div>
                <div class="p-5 space-y-3">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-zinc-500">Request (curl) & Response 201</p>
                    <div class="code-wrap rounded-lg overflow-hidden border border-zinc-700/40">
                        <div class="code-header flex items-center justify-between px-3 py-1.5">
                            <span class="text-[10px] text-zinc-500">bash</span>
                            <button x-on:click="copy($el)" class="text-[10px] text-zinc-400 hover:text-white transition">Copy</button>
                        </div>
                        <pre><code class="language-bash">curl -X POST {{ rtrim(config('app.url'),'/') }}/api/v1/files \
  -H "Authorization: Bearer {API_KEY}" \
  -F "rake_id=KRL-001" \
  -F "file=@report.csv"</code></pre>
                    </div>
                    <div class="code-wrap rounded-lg overflow-hidden border border-zinc-700/40">
                        <div class="code-header flex items-center justify-between px-3 py-1.5">
                            <span class="text-[10px] text-zinc-500">JSON</span>
                            <button x-on:click="copy($el)" class="text-[10px] text-zinc-400 hover:text-white transition">Copy</button>
                        </div>
                        <pre><code class="language-json">{
  "file_id": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
  "filename": "report.csv",
  "status": "uploaded"
}</code></pre>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ GET /dashboard ═══ --}}
        <div id="get-dashboard" class="endpoint-section rounded-xl border border-zinc-700 bg-zinc-800/40 overflow-hidden">
            <div class="flex items-center gap-3 px-5 py-3 border-b border-zinc-700/60">
                <span class="method-badge method-get">GET</span>
                <code class="text-sm font-mono text-zinc-100 font-semibold">/api/v1/dashboard</code>
                <span class="ml-auto text-xs text-zinc-500">Ringkasan statistik — tidak ada parameter</span>
            </div>
            <div class="p-5">
                <div class="code-wrap rounded-lg overflow-hidden border border-zinc-700/40">
                    <div class="code-header flex items-center justify-between px-3 py-1.5">
                        <span class="text-[10px] text-zinc-500">Response 200</span>
                        <button x-on:click="copy($el)" class="text-[10px] text-zinc-400 hover:text-white transition">Copy</button>
                    </div>
                    <pre><code class="language-json">{
  "total_sessions": 42,
  "total_records": 1850,
  "per_rake": [
    { "rake_id": "KRL-001", "count": 18 }
  ],
  "per_equipment": [
    { "equipment_name": "Traction Motor A", "count": 340 }
  ],
  "per_classification": [
    { "classification": "heavy",  "count": 95  },
    { "classification": "medium", "count": 420 },
    { "classification": "light",  "count": 1335 }
  ],
  "recent_heavy_faults": [
    {
      "id": 91,
      "timestamp": "2025-05-08T08:12:00Z",
      "equipment_name": "Traction Motor A",
      "fault_name": "OVERHEAT_TM_A",
      "classification": "heavy"
    }
  ]
}</code></pre>
                </div>
            </div>
        </div>

        {{-- ═══ GET /analytics/trend ═══ --}}
        <div id="get-trend" class="endpoint-section rounded-xl border border-zinc-700 bg-zinc-800/40 overflow-hidden">
            <div class="flex items-center gap-3 px-5 py-3 border-b border-zinc-700/60">
                <span class="method-badge method-get">GET</span>
                <code class="text-sm font-mono text-zinc-100 font-semibold">/api/v1/analytics/trend</code>
                <span class="ml-auto text-xs text-zinc-500">Tren jumlah failure per periode</span>
            </div>
            <div class="grid lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-zinc-700/60">
                <div class="p-5 space-y-2">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-zinc-500 mb-2">Query Parameters</p>
                    @php $p = [
                        ['from','date','required','Tanggal awal YYYY-MM-DD'],
                        ['to','date','required','Tanggal akhir YYYY-MM-DD'],
                        ['group_by','string','optional','day (default) | week | month'],
                        ['rake_id','string','optional','Filter untuk rake tertentu'],
                    ]; @endphp
                    @foreach($p as [$n,$t,$r,$d])
                    <div class="flex items-start gap-3 text-xs">
                        <code class="shrink-0 font-mono text-green-300">{{ $n }}</code>
                        <span class="text-zinc-500 shrink-0">{{ $t }}</span>
                        <span @class(['param-required'=>$r==='required','param-optional'=>$r==='optional','shrink-0'=>true])>{{ strtoupper(substr($r,0,3)) }}</span>
                        <span class="text-zinc-400">{{ $d }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="p-5 space-y-3">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-zinc-500">Response 200</p>
                    <div class="code-wrap rounded-lg overflow-hidden border border-zinc-700/40">
                        <div class="code-header flex items-center justify-between px-3 py-1.5">
                            <span class="text-[10px] text-zinc-500">JSON</span>
                            <button x-on:click="copy($el)" class="text-[10px] text-zinc-400 hover:text-white transition">Copy</button>
                        </div>
                        <pre><code class="language-json">[
  { "period": "2025-05-06", "count": 72 },
  { "period": "2025-05-07", "count": 91 },
  { "period": "2025-05-08", "count": 55 }
]</code></pre>
                    </div>
                    <p class="text-[10px] text-zinc-500">Format <code class="bg-zinc-700 px-1 rounded">period</code>: YYYY-MM-DD / YYYY-WW / YYYY-MM sesuai group_by</p>
                </div>
            </div>
        </div>

        {{-- ═══ GET /analytics/pareto ═══ --}}
        <div id="get-pareto" class="endpoint-section rounded-xl border border-zinc-700 bg-zinc-800/40 overflow-hidden">
            <div class="flex items-center gap-3 px-5 py-3 border-b border-zinc-700/60">
                <span class="method-badge method-get">GET</span>
                <code class="text-sm font-mono text-zinc-100 font-semibold">/api/v1/analytics/pareto</code>
                <span class="ml-auto text-xs text-zinc-500">Analisis Pareto — fault terbanyak + % kumulatif</span>
            </div>
            <div class="grid lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-zinc-700/60">
                <div class="p-5 space-y-2">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-zinc-500 mb-2">Query Parameters <span class="normal-case font-normal text-zinc-600">(semua opsional)</span></p>
                    @php $p = [
                        ['start_date','date','Tanggal awal filter (bersama end_date)'],
                        ['end_date','date','Tanggal akhir filter (bersama start_date)'],
                        ['start_time','time','Filter jam mulai HH:MM:SS (bersama end_time)'],
                        ['end_time','time','Filter jam selesai HH:MM:SS (bersama start_time)'],
                        ['failure_type','string','Filter berdasarkan equipment_name'],
                        ['rake_id','string','Filter berdasarkan rake'],
                    ]; @endphp
                    @foreach($p as [$n,$t,$d])
                    <div class="flex items-start gap-3 text-xs">
                        <code class="shrink-0 font-mono text-green-300">{{ $n }}</code>
                        <span class="text-zinc-500 shrink-0">{{ $t }}</span>
                        <span class="text-zinc-400">{{ $d }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="p-5 space-y-3">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-zinc-500">Response 200</p>
                    <div class="code-wrap rounded-lg overflow-hidden border border-zinc-700/40">
                        <div class="code-header flex items-center justify-between px-3 py-1.5">
                            <span class="text-[10px] text-zinc-500">JSON</span>
                            <button x-on:click="copy($el)" class="text-[10px] text-zinc-400 hover:text-white transition">Copy</button>
                        </div>
                        <pre><code class="language-json">[
  { "fault_name": "OVERHEAT_TM_A",      "frequency": 340, "cumulative_percentage": 18.38 },
  { "fault_name": "BRAKE_PRESSURE_LOW", "frequency": 210, "cumulative_percentage": 29.73 },
  { "fault_name": "DOOR_SENSOR_FAULT",  "frequency": 195, "cumulative_percentage": 40.27 }
]</code></pre>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ GET /health ═══ --}}
        <div id="get-health" class="endpoint-section rounded-xl border border-zinc-700 bg-zinc-800/40 overflow-hidden">
            <div class="flex items-center gap-3 px-5 py-3 border-b border-zinc-700/60">
                <span class="method-badge method-get">GET</span>
                <code class="text-sm font-mono text-zinc-100 font-semibold">/api/v1/health</code>
                <span class="ml-auto text-xs text-zinc-500">Health check — tidak ada parameter</span>
            </div>
            <div class="p-5">
                <div class="code-wrap rounded-lg overflow-hidden border border-zinc-700/40 max-w-xs">
                    <div class="code-header flex items-center justify-between px-3 py-1.5">
                        <span class="text-[10px] text-zinc-500">Response 200</span>
                        <button x-on:click="copy($el)" class="text-[10px] text-zinc-400 hover:text-white transition">Copy</button>
                    </div>
                    <pre><code class="language-json">{
  "status": "ok",
  "version": "1.0.0"
}</code></pre>
                </div>
            </div>
        </div>

        {{-- Error codes --}}
        <div class="rounded-xl border border-zinc-700 bg-zinc-800/40 overflow-hidden">
            <div class="px-5 py-3 border-b border-zinc-700/60">
                <p class="text-sm font-semibold text-zinc-300">Error Codes</p>
            </div>
            <div class="p-5 grid grid-cols-2 lg:grid-cols-4 gap-3">
                @php $errors = [
                    ['401','Unauthorized','API key salah atau kosong','text-yellow-400'],
                    ['404','Not Found','Resource tidak ada di database','text-orange-400'],
                    ['422','Unprocessable','Validasi field gagal','text-red-400'],
                    ['500','Server Error','Cek storage/logs/laravel.log','text-red-500'],
                ]; @endphp
                @foreach($errors as [$code, $label, $desc, $color])
                <div class="rounded-lg bg-zinc-900/60 border border-zinc-700/40 p-3 space-y-1">
                    <p class="font-mono font-bold text-sm {{ $color }}">{{ $code }}</p>
                    <p class="text-xs font-semibold text-zinc-300">{{ $label }}</p>
                    <p class="text-[11px] text-zinc-500">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>

    </main>
</div>

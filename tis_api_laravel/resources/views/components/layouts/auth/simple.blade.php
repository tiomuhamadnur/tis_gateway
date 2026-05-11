<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <title>{{ config('app.name', 'TIS Gateway') }}</title>
    </head>
    <body class="min-h-screen antialiased bg-zinc-950">
        <div class="flex min-h-screen">

            {{-- Left branding panel (desktop only) --}}
            <div class="hidden lg:flex lg:w-[45%] flex-col justify-between p-12 relative overflow-hidden"
                 style="background: linear-gradient(145deg, #0f172a 0%, #1e1b4b 60%, #0f172a 100%)">
                {{-- Subtle radial glow --}}
                <div style="position:absolute;top:-100px;left:-100px;width:500px;height:500px;background:radial-gradient(circle,rgba(59,130,246,0.12) 0%,transparent 70%);pointer-events:none;"></div>

                <div class="relative">
                    {{-- Logo --}}
                    <div class="flex items-center gap-3 mb-14">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl shadow-lg"
                             style="background: linear-gradient(135deg,#3b82f6,#1d4ed8); box-shadow:0 4px 16px rgba(59,130,246,0.35)">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-bold tracking-tight leading-tight">TIS Gateway</p>
                            <p class="text-xs font-semibold uppercase tracking-widest" style="color:#60a5fa">MRT Jakarta</p>
                        </div>
                    </div>

                    <h1 class="text-3xl font-bold text-white leading-tight mb-3" style="letter-spacing:-0.03em">
                        Traction Information<br>System Gateway
                    </h1>
                    <p class="text-base mb-10 leading-relaxed" style="color:#94a3b8">
                        Platform pemantauan dan analisis kegagalan<br>sistem traksi trainset MRT Jakarta.
                    </p>

                    <div class="space-y-3.5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg" style="background:rgba(59,130,246,0.18)">
                                <svg class="h-4 w-4" style="color:#60a5fa" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <span class="text-sm" style="color:#cbd5e1">Dashboard analitik & chart interaktif</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg" style="background:rgba(239,68,68,0.18)">
                                <svg class="h-4 w-4" style="color:#f87171" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <span class="text-sm" style="color:#cbd5e1">Pencatatan failure records otomatis</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg" style="background:rgba(34,197,94,0.18)">
                                <svg class="h-4 w-4" style="color:#4ade80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <span class="text-sm" style="color:#cbd5e1">Export laporan Excel & PDF</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg" style="background:rgba(168,85,247,0.18)">
                                <svg class="h-4 w-4" style="color:#c084fc" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <span class="text-sm" style="color:#cbd5e1">REST API untuk integrasi sistem onboard</span>
                        </div>
                    </div>
                </div>

                <p class="text-xs relative" style="color:#334155">© {{ date('Y') }} PT MRT Jakarta</p>
            </div>

            {{-- Right login panel --}}
            <div class="flex flex-1 items-center justify-center p-8 bg-zinc-900">
                <div class="w-full max-w-sm">
                    {{-- Mobile logo --}}
                    <div class="flex lg:hidden items-center justify-center gap-3 mb-8">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl"
                             style="background:linear-gradient(135deg,#3b82f6,#1d4ed8)">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-bold">TIS Gateway</p>
                            <p class="text-xs uppercase tracking-wider" style="color:#60a5fa">MRT Jakarta</p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-6">
                        {{ $slot }}
                    </div>
                </div>
            </div>

        </div>
        @fluxScripts
    </body>
</html>

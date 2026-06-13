<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <title>{{ config('app.name', 'TIS Gateway') }}</title>
    </head>
    <body class="min-h-screen antialiased bg-zinc-950">
        <div class="flex min-h-screen">

            {{-- Left panel (desktop) --}}
            <div class="hidden lg:flex lg:w-[40%] flex-col justify-between p-12 relative overflow-hidden"
                 style="background: linear-gradient(160deg, #0f172a 0%, #0b0f19 60%, #080c14 100%)">
                {{-- Subtle decorative glow --}}
                <div class="absolute -top-32 -left-32 w-[500px] h-[500px] rounded-full pointer-events-none"
                     style="background: radial-gradient(circle, rgba(59,130,246,0.08) 0%, transparent 70%)"></div>
                {{-- Subtle grid pattern overlay --}}
                <div class="absolute inset-0 pointer-events-none opacity-[0.03]"
                     style="background-image: linear-gradient(rgba(255,255,255,.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.1) 1px, transparent 1px); background-size: 48px 48px;"></div>

                <div class="relative">
                    <div class="flex items-center gap-3 mb-12">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 shadow-lg shadow-blue-600/20">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-bold tracking-tight leading-tight">TIS Gateway</p>
                            <p class="text-xs font-semibold uppercase tracking-widest text-blue-400">MRT Jakarta</p>
                        </div>
                    </div>

                    <div class="mb-12">
                        <h1 class="text-2xl font-bold text-white leading-tight mb-3" style="letter-spacing:-0.03em">
                            Train Information<br>System Gateway
                        </h1>
                        <p class="text-sm text-zinc-300 leading-relaxed max-w-sm">
                            Fault monitoring and analysis platform for MRT Jakarta trainsets.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center gap-3.5">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-500/20 border border-blue-500/30">
                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-200">Real-time monitoring</p>
                                <p class="text-xs text-zinc-300">Fault telemetry and analytics dashboard</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3.5">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-500/20 border border-emerald-500/30">
                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-200">Session export</p>
                                <p class="text-xs text-zinc-300">Excel and PDF report generation</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3.5">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-purple-500/20 border border-purple-500/30">
                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-200">API integration</p>
                                <p class="text-xs text-zinc-300">REST gateway for onboard data flow</p>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-zinc-700 relative">&copy; {{ date('Y') }} PT MRT Jakarta</p>
            </div>

            {{-- Right login panel --}}
            <div class="flex flex-1 items-center justify-center p-8 bg-zinc-950 relative overflow-hidden">
                {{-- Subtle radial accent --}}
                <div class="absolute top-1/2 -translate-y-1/2 -right-32 w-[400px] h-[400px] rounded-full pointer-events-none opacity-30"
                     style="background: radial-gradient(circle, rgba(59,130,246,0.06) 0%, transparent 70%)"></div>

                <div class="w-full max-w-sm px-2 relative">
                    {{-- Mobile logo --}}
                    <div class="flex lg:hidden items-center justify-center gap-3 mb-8">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 shadow-sm">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-bold">TIS Gateway</p>
                            <p class="text-xs uppercase tracking-wider text-blue-400">MRT Jakarta</p>
                        </div>
                    </div>

                    <div class="login-card">
                        <div class="flex flex-col gap-6">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>

        </div>

        @fluxScripts
    </body>
</html>
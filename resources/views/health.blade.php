<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paulette Culture Kids | System Health</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #0c0a09; }
        .glass-card { 
            background: rgba(23, 23, 23, 0.6); 
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover { border-color: rgba(255, 255, 255, 0.15); transform: translateY(-2px); }
        .status-badge { @apply text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full; }
        .status-ok { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }
        .status-fail { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
    </style>
</head>
<body class="min-h-screen text-slate-300 flex items-center justify-center p-6">
    <div class="fixed top-0 left-0 w-full h-full pointer-events-none opacity-20 bg-gradient-to-br from-indigo-900/40 via-transparent to-rose-900/20"></div>

    <div class="max-w-xl w-full z-10">
        <header class="flex items-center space-x-3 mb-10">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">System Health</h1>
                <p class="text-sm text-slate-500">Paulette Culture Kids Infrastructure</p>
            </div>
        </header>

        <div class="grid gap-4">
            <!-- Server Status -->
            <div class="glass-card p-5 rounded-2xl flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="p-2 bg-slate-800 rounded-lg text-indigo-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-white">Application Server</div>
                        <div class="text-[11px] text-slate-500">PHP {{ $php }} | Env: {{ $env }}</div>
                    </div>
                </div>
                <div class="status-badge status-ok">Operational</div>
            </div>

            <!-- Database Status -->
            <div class="glass-card p-5 rounded-2xl flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="p-2 bg-slate-800 rounded-lg text-cyan-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 1.105 3.582 2 8 2s8-.895 8-2V7m0-3c0 1.105-3.582 2-8 2S4 2.105 4 1s3.582-2 8-2 8 .895 8 2z"></path></svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-white">Database Engine</div>
                        <div class="text-[11px] text-slate-500">MySQL / MariaDB via XAMPP</div>
                    </div>
                </div>
                <div class="status-badge {{ $db === 'Connected' ? 'status-ok' : 'status-fail' }}">{{ $db }}</div>
            </div>

            <!-- Redis Status -->
            <div class="glass-card p-5 rounded-2xl flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="p-2 bg-slate-800 rounded-lg text-rose-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-white">Cache Layer</div>
                        <div class="text-[11px] text-slate-500">Redis Engine | Memurai</div>
                    </div>
                </div>
                <div class="status-badge {{ $redis === 'Connected' ? 'status-ok' : 'status-fail' }}">{{ $redis }}</div>
            </div>
        </div>

        <footer class="mt-8 text-center">
            <p class="text-[10px] text-slate-600 uppercase tracking-[2px]">Last check: {{ now()->format('Y-m-d H:i:s') }}</p>
        </footer>
    </div>
</body>
</html>

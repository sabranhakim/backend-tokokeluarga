<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Grosir Toko Keluarga - Dashboard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet" media="print" onload="this.media='all'">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js" defer></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr" defer></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js" defer></script>

    <style>
        [x-cloak] { display: none !important; }
        
        /* Snappier Transitions */
        .transition-all, .transition-colors, .transition-transform {
            transition-duration: 150ms !important;
        }

        .sidebar-active {
            background: linear-gradient(to right, #eff6ff, #ffffff);
            color: #2563eb;
            font-weight: 600;
            box-shadow: inset 3px 0 0 #2563eb;
        }

        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

        /* Optimized Tom Select */
        .ts-control {
            border-radius: 0.75rem !important;
            padding: 0.5rem 1rem !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: none !important;
            min-height: 42px !important;
        }
        .ts-wrapper.focus .ts-control {
            border-color: #3b82f6 !important;
            ring: 2px #3b82f622;
        }
        .ts-dropdown {
            border-radius: 1rem !important;
            border: 1px solid #f1f5f9 !important;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;
            padding: 0.375rem !important;
        }
        .ts-dropdown .option {
            padding: 0.625rem 0.875rem !important;
            border-radius: 0.625rem !important;
        }

        /* Simplified Flatpickr */
        .flatpickr-calendar {
            border-radius: 1.25rem !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05) !important;
            border: 1px solid #f1f5f9 !important;
        }
        
        /* Optimization: Reduced heavy blurs */
        .backdrop-blur-md { backdrop-filter: blur(8px); }
        .bg-slate-900\/60 { background-color: rgba(15, 23, 42, 0.4); }

        /* Skeleton Loading */
        .skeleton {
            background-color: #f1f5f9;
            position: relative;
            overflow: hidden;
            border-radius: 0.375rem;
        }
        .skeleton::after {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            transform: translateX(-100%);
            background-image: linear-gradient(
                90deg,
                rgba(255, 255, 255, 0) 0,
                rgba(255, 255, 255, 0.2) 20%,
                rgba(255, 255, 255, 0.5) 60%,
                rgba(255, 255, 255, 0)
            );
            animation: shimmer 1.5s infinite;
        }
        @keyframes shimmer {
            100% {
                transform: translateX(100%);
            }
        }
    </style>
</head>
<body class="bg-[#f8fafc] font-sans text-slate-900 antialiased overflow-hidden" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden" x-cloak></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200 transition-transform duration-200 ease-out lg:relative lg:translate-x-0 flex flex-col">
            
            <!-- Logo Area -->
            <div class="flex items-center justify-between h-20 px-6 border-b border-slate-50">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-md">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <span class="text-xl font-extrabold tracking-tight text-slate-800">Toko<span class="text-blue-600">Keluarga</span></span>
                </div>
                <button @click="sidebarOpen = false" class="p-2 rounded-lg text-slate-400 hover:bg-slate-50 lg:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-6 space-y-1 custom-scrollbar px-3">
                <div class="px-4 mb-2">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Menu Utama</p>
                </div>
                
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-150 group {{ request()->routeIs('dashboard') ? 'sidebar-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <div class="p-2 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-white shadow-sm text-blue-600' : 'bg-transparent text-slate-400 group-hover:text-slate-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </div>
                    <span class="ml-3">Dashboard</span>
                </a>

                <div class="px-4 mt-6 mb-2">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Inventory</p>
                </div>

                <a href="{{ route('barang.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-150 group {{ request()->routeIs('barang.index') ? 'sidebar-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <div class="p-2 rounded-lg {{ request()->routeIs('barang.index') ? 'bg-white shadow-sm text-blue-600' : 'bg-transparent text-slate-400 group-hover:text-slate-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <span class="ml-3">Stok Barang</span>
                </a>

                <a href="{{ route('barang-stok.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-150 group {{ request()->routeIs('barang-stok.index') ? 'sidebar-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <div class="p-2 rounded-lg {{ request()->routeIs('barang-stok.index') ? 'bg-white shadow-sm text-blue-600' : 'bg-transparent text-slate-400 group-hover:text-slate-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <span class="ml-3">Stok per Batch</span>
                </a>

                <a href="{{ route('barang.history-all') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-150 group {{ request()->routeIs('barang.history*') ? 'sidebar-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <div class="p-2 rounded-lg {{ request()->routeIs('barang.history*') ? 'bg-white shadow-sm text-blue-600' : 'bg-transparent text-slate-400 group-hover:text-slate-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </div>
                    <span class="ml-3">Riwayat Stok</span>
                </a>

                <a href="{{ route('kategori.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-150 group {{ request()->routeIs('kategori.*') ? 'sidebar-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <div class="p-2 rounded-lg {{ request()->routeIs('kategori.*') ? 'bg-white shadow-sm text-blue-600' : 'bg-transparent text-slate-400 group-hover:text-slate-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    </div>
                    <span class="ml-3">Kategori</span>
                </a>

                <a href="{{ route('supplier.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-150 group {{ request()->routeIs('supplier.*') ? 'sidebar-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <div class="p-2 rounded-lg {{ request()->routeIs('supplier.*') ? 'bg-white shadow-sm text-blue-600' : 'bg-transparent text-slate-400 group-hover:text-slate-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <span class="ml-3">Supplier</span>
                </a>

                <div class="px-4 mt-6 mb-2">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Transaksi</p>
                </div>

                <a href="{{ route('penerimaan.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-150 group {{ request()->routeIs('penerimaan.*') ? 'sidebar-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <div class="p-2 rounded-lg {{ request()->routeIs('penerimaan.*') ? 'bg-white shadow-sm text-blue-600' : 'bg-transparent text-slate-400 group-hover:text-slate-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <span class="ml-3">Penerimaan Barang</span>
                </a>

                <a href="{{ route('barang-keluar.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-150 group {{ request()->routeIs('barang-keluar.*') ? 'sidebar-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <div class="p-2 rounded-lg {{ request()->routeIs('barang-keluar.*') ? 'bg-white shadow-sm text-blue-600' : 'bg-transparent text-slate-400 group-hover:text-slate-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </div>
                    <span class="ml-3">Barang Keluar</span>
                </a>

                <a href="{{ route('retur-pembelian.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-150 group {{ request()->routeIs('retur-pembelian.*') ? 'sidebar-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <div class="p-2 rounded-lg {{ request()->routeIs('retur-pembelian.*') ? 'bg-white shadow-sm text-blue-600' : 'bg-transparent text-slate-400 group-hover:text-slate-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h2m4 0h6M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="ml-3">Retur Pembelian</span>
                </a>

                <a href="{{ route('retur-penjualan.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-150 group {{ request()->routeIs('retur-penjualan.*') ? 'sidebar-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <div class="p-2 rounded-lg {{ request()->routeIs('retur-penjualan.*') ? 'bg-white shadow-sm text-blue-600' : 'bg-transparent text-slate-400 group-hover:text-slate-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                    </div>
                    <span class="ml-3">Retur Penjualan</span>
                </a>

                <a href="{{ route('stock-opname.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-150 group {{ request()->routeIs('stock-opname.*') ? 'sidebar-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <div class="p-2 rounded-lg {{ request()->routeIs('stock-opname.*') ? 'bg-white shadow-sm text-blue-600' : 'bg-transparent text-slate-400 group-hover:text-slate-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 00-4-4H5m14 0h-2a4 4 0 00-4 4v2m-2 4h.01M9 21h6a2 2 0 002-2V5a2 2 0 00-2-2H9a2 2 0 00-2-2H9a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <span class="ml-3">Stock Opname</span>
                </a>

                <a href="{{ route('laporan.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-150 group {{ request()->routeIs('laporan.*') ? 'sidebar-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <div class="p-2 rounded-lg {{ request()->routeIs('laporan.*') ? 'bg-white shadow-sm text-blue-600' : 'bg-transparent text-slate-400 group-hover:text-slate-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 00-4-4H5m14 0h-2a4 4 0 00-4 4v2m-2 4h.01M9 21h6a2 2 0 002-2V5a2 2 0 00-2-2H9a2 2 0 00-2-2H9a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <span class="ml-3">Laporan</span>
                </a>

                <div class="px-4 mt-6 mb-2">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Sistem</p>
                </div>

                <a href="{{ route('activity.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-150 group {{ request()->routeIs('activity.*') ? 'sidebar-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <div class="p-2 rounded-lg {{ request()->routeIs('activity.*') ? 'bg-white shadow-sm text-blue-600' : 'bg-transparent text-slate-400 group-hover:text-slate-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="ml-3">Log Aktivitas</span>
                </a>

                <a href="{{ route('users.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-150 group {{ request()->routeIs('users.*') ? 'sidebar-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <div class="p-2 rounded-lg {{ request()->routeIs('users.*') ? 'bg-white shadow-sm text-blue-600' : 'bg-transparent text-slate-400 group-hover:text-slate-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <span class="ml-3">Manajemen User</span>
                </a>

                <a href="{{ route('roles.index') }}" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-150 group {{ request()->routeIs('roles.*') ? 'sidebar-active' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                    <div class="p-2 rounded-lg {{ request()->routeIs('roles.*') ? 'bg-white shadow-sm text-blue-600' : 'bg-transparent text-slate-400 group-hover:text-slate-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <span class="ml-3">Manajemen Role</span>
                </a>
            </nav>

            <!-- User Footer -->
            <div class="p-4 border-t border-slate-50 bg-slate-50/50">
                <div class="flex items-center p-3 bg-white rounded-2xl shadow-sm border border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold">
                        {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="ml-3 flex-1 overflow-hidden">
                        <p class="text-sm font-bold text-slate-800 truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-[10px] text-slate-500 uppercase font-semibold">Administrator</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="h-20 bg-white/80 backdrop-blur-md border-b border-slate-100 flex items-center justify-between px-4 lg:px-8 sticky top-0 z-30">
                <div class="flex items-center space-x-4">
                    <button @click="sidebarOpen = true" class="p-2 rounded-xl bg-slate-50 text-slate-600 lg:hidden hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold text-slate-800 tracking-tight">@yield('header', 'Dashboard')</h1>
                        <p class="text-[11px] text-slate-400 font-medium hidden md:block">Selamat datang kembali, {{ explode(' ', auth()->user()->name ?? 'Admin')[0] }}!</p>
                    </div>
                </div>

                <div class="flex items-center space-x-2 md:space-x-4">
                    <!-- Dynamic Date & Time -->
                    <div class="hidden xl:flex items-center space-x-3 bg-slate-50 px-4 py-2 rounded-2xl border border-slate-100"
                        x-data="{
                            date: '',
                            time: '',
                            update() {
                                const now = new Date();
                                this.date = now.toLocaleDateString('id-ID', {
                                    day: 'numeric',
                                    month: 'short',
                                    year: 'numeric'
                                });
                                this.time = now.toLocaleTimeString('id-ID', {
                                    hour: '2-digit',
                                    minute: '2-digit'
                                });
                            }
                        }"
                        x-init="update(); setInterval(() => update(), 60000)">
                        <div class="flex items-center text-slate-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span x-text="date" class="text-xs font-bold uppercase tracking-wider"></span>
                        </div>
                        <div class="w-px h-4 bg-slate-200"></div>
                        <div class="flex items-center text-blue-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span x-text="time" class="text-xs font-black"></span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-1 md:space-x-2">
                        <livewire:notification-header />
                        <div class="h-8 w-px bg-slate-100 mx-1"></div>
                        <livewire:auth.logout />
                    </div>
                </div>
            </header>

            <!-- Scrollable Page Content -->
            <div class="flex-1 overflow-y-auto custom-scrollbar bg-[#f8fafc] p-4 lg:p-8">
                <div class="max-w-7xl mx-auto">
                    {{ $slot ?? '' }}
                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    @livewireScripts

    <!-- Toast Notifications -->
    <div x-data="{
            messages: [],
            remove(id) {
                this.messages = this.messages.filter(m => m.id !== id)
            },
            add(message, type = 'success') {
                const id = Date.now()
                this.messages.push({ id, text: message, type })
                setTimeout(() => this.remove(id), 5000)
            }
        }"
        @notify.window="add($event.detail)"
        class="fixed top-6 right-6 z-[9999] flex flex-col gap-3 w-80 pointer-events-none">

        <template x-for="message in messages" :key="message.id">
            <div x-show="true"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform translate-x-4 scale-95"
                x-transition:enter-end="opacity-100 transform translate-x-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform translate-x-0 scale-100"
                x-transition:leave-end="opacity-0 transform translate-x-4 scale-95"
                :class="{
                    'bg-emerald-500 border-emerald-600 shadow-sm': message.type === 'success',
                    'bg-rose-500 border-rose-600 shadow-sm': message.type === 'error',
                    'bg-blue-500 border-blue-600 shadow-sm': message.type === 'info'
                }"
                class="text-white px-4 py-3 rounded-2xl border flex items-start pointer-events-auto relative overflow-hidden group">
                <div class="flex-1 pr-4 relative">
                    <p x-text="message.text" class="text-sm font-bold leading-tight"></p>
                </div>
                <button @click="remove(message.id)" class="text-white/60 hover:text-white transition-colors relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </template>

        @if(session()->has('success'))
            <div x-init="add('{{ session('success') }}', 'success')"></div>
        @endif
        @if(session()->has('error'))
            <div x-init="add('{{ session('error') }}', 'error')"></div>
        @endif
    </div>
</body>
</html>

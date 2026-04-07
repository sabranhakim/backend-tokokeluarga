<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Toko Keluarga - Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-50 font-sans text-slate-900 antialiased">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="hidden md:flex flex-col w-64 bg-white border-r border-slate-200">
            <div class="flex items-center justify-center h-16 border-b border-slate-200">
                <span class="text-xl font-bold text-blue-600">Toko Keluarga</span>
            </div>
            <nav class="flex-1 overflow-y-auto py-4">
                <a href="{{ route('dashboard') }}" class="flex items-center px-6 py-3 text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('barang.index') }}" class="flex items-center px-6 py-3 text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Stok Barang
                </a>
                <a href="{{ route('kategori.index') }}" class="flex items-center px-6 py-3 text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    Kategori
                </a>
                <a href="{{ route('supplier.index') }}" class="flex items-center px-6 py-3 text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Supplier
                </a>
                <a href="{{ route('penerimaan.index') }}" class="flex items-center px-6 py-3 text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    Penerimaan Barang
                </a>
            </nav>
            <div class="p-4 border-t border-slate-200">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                        {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-slate-900">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-xs text-slate-500">Administrator</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-8">
                <h1 class="text-xl font-semibold text-slate-800">@yield('header', 'Dashboard')</h1>
                <div class="flex items-center space-x-4">
                    <button class="p-2 text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </button>
                    <div class="h-8 w-px bg-slate-200 mx-2"></div>
                    <button class="text-sm font-medium text-slate-600 hover:text-red-600">Keluar</button>
                </div>
            </header>

            <!-- Scrollable Page Content -->
            <div class="flex-1 overflow-y-auto">
                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>

<!-- Backdrop (hanya HP): tap area gelap untuk menutup menu -->
<div x-show="mobileMenu" x-transition.opacity @click="mobileMenu = false"
     class="fixed inset-0 bg-slate-900/60 z-40 md:hidden" style="display:none;"></div>

<aside x-data="{ sidebarCollapsed: (window.innerWidth >= 768 && window.innerWidth < 1024) }"
       @resize.window="if (window.innerWidth < 768) sidebarCollapsed = false"
       :class="[
            sidebarCollapsed ? 'md:w-20' : 'md:w-64',
            mobileMenu ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
       ]"
       class="bg-slate-900 text-slate-300 flex flex-col shrink-0 border-r border-slate-800 transition-all duration-300 select-none
              w-64 fixed inset-y-0 left-0 z-50
              md:static md:z-auto md:h-screen">

    <!-- Toggle Collapse/Expand Button (hanya iPad/desktop) -->
    <button @click="sidebarCollapsed = !sidebarCollapsed"
            class="hidden md:block absolute -right-3 top-6 bg-indigo-600 text-white rounded-full p-1 shadow-md hover:bg-indigo-500 transition focus:outline-none z-50">
        <svg :class="sidebarCollapsed ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </button>

    <!-- Brand / Logo -->
    <div class="p-4 border-b border-slate-800 flex items-center overflow-hidden" :class="sidebarCollapsed ? 'justify-center' : 'space-x-3'">
        <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-indigo-500/30 shrink-0">
            H
        </div>
        <div x-show="!sidebarCollapsed" x-transition class="whitespace-nowrap flex-1">
            <h1 class="font-bold text-white text-sm leading-none tracking-wide">Happay</h1>
            <span class="text-[9px] text-slate-500 font-mono">POS System</span>
        </div>
        <!-- Tombol tutup (hanya HP) -->
        <button type="button" @click="mobileMenu = false" class="md:hidden text-slate-400 hover:text-white text-2xl leading-none px-2" aria-label="Tutup menu">&times;</button>
    </div>

    <!-- Active User Context -->
    <div class="p-3 mx-2 my-3 bg-slate-800/60 rounded-xl border border-slate-700/50 flex items-center overflow-hidden" :class="sidebarCollapsed ? 'justify-center' : 'space-x-3'">
        <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-slate-300 font-semibold text-xs border border-slate-600 shrink-0" :title="sidebarCollapsed ? '{{ auth()->user()->admin_name ?? 'Admin' }}' : ''">
            {{ strtoupper(substr(auth()->user()->admin_name ?? 'A', 0, 2)) }}
        </div>
        <div x-show="!sidebarCollapsed" x-transition class="overflow-hidden whitespace-nowrap">
            <p class="text-xs font-medium text-white truncate">{{ auth()->user()->admin_name ?? 'Admin User' }}</p>
            <div class="flex items-center space-x-1 mt-0.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span class="text-[10px] text-slate-400 font-mono uppercase">{{ session('region_alias', 'JKT') }}</span>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 min-h-0 px-2 space-y-1 overflow-y-auto py-2 text-xs">

        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}"
           :title="sidebarCollapsed ? 'Dashboard' : ''"
           class="flex items-center px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200' }}"
           :class="sidebarCollapsed ? 'justify-center' : 'space-x-3'">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span x-show="!sidebarCollapsed" x-transition class="whitespace-nowrap">Dashboard</span>
        </a>

        <!-- POS (With Submenu) -->
        <div x-data="{ open: {{ request()->routeIs('pos.*') ? 'true' : 'false' }} }">
            <button @click="open = !open; if(sidebarCollapsed) sidebarCollapsed = false"
                    :title="sidebarCollapsed ? 'POS' : ''"
                    class="w-full flex items-center px-3 py-2.5 rounded-lg font-medium transition text-slate-400 hover:bg-slate-800 hover:text-slate-200"
                    :class="sidebarCollapsed ? 'justify-center' : 'justify-between'">
                <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'space-x-3'">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span x-show="!sidebarCollapsed" x-transition class="whitespace-nowrap">POS</span>
                </div>
                <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-180' : ''" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open && !sidebarCollapsed" x-transition class="pl-9 pr-2 py-1 space-y-1">
                <a href="{{ route('pos.create') }}" class="block px-3 py-1.5 rounded-md text-[11px] font-medium transition {{ request()->routeIs('pos.create') ? 'text-indigo-400 bg-slate-800/80 font-semibold' : 'text-slate-400 hover:text-slate-200' }}">New Transaction</a>
                <a href="{{ route('pos.index') }}" class="block px-3 py-1.5 rounded-md text-[11px] font-medium transition {{ request()->routeIs('pos.index') ? 'text-indigo-400 bg-slate-800/80 font-semibold' : 'text-slate-400 hover:text-slate-200' }}">Transaction List</a>
            </div>
        </div>

        <!-- Master Item (With Submenu) -->
        <div x-data="{ open: {{ request()->routeIs('items.*') ? 'true' : 'false' }} }">
            <button @click="open = !open; if(sidebarCollapsed) sidebarCollapsed = false"
                    :title="sidebarCollapsed ? 'Master Item' : ''"
                    class="w-full flex items-center px-3 py-2.5 rounded-lg font-medium transition text-slate-400 hover:bg-slate-800 hover:text-slate-200"
                    :class="sidebarCollapsed ? 'justify-center' : 'justify-between'">
                <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'space-x-3'">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span x-show="!sidebarCollapsed" x-transition class="whitespace-nowrap">Master Item</span>
                </div>
                <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-180' : ''" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open && !sidebarCollapsed" x-transition class="pl-9 pr-2 py-1 space-y-1">
                <a href="{{ route('items.index') }}" class="block px-3 py-1.5 rounded-md text-[11px] font-medium transition {{ request()->routeIs('items.index') ? 'text-indigo-400 bg-slate-800/80 font-semibold' : 'text-slate-400 hover:text-slate-200' }}">List Items</a>
            </div>
        </div>

        <!-- Warehouse Management (With Submenu) -->
        <div x-data="{ open: {{ request()->routeIs('warehouse.*', 'inventory.*', 'transfer.*') ? 'true' : 'false' }} }">
            <button @click="open = !open; if(sidebarCollapsed) sidebarCollapsed = false"
                    :title="sidebarCollapsed ? 'Warehouse Management' : ''"
                    class="w-full flex items-center px-3 py-2.5 rounded-lg font-medium transition text-slate-400 hover:bg-slate-800 hover:text-slate-200"
                    :class="sidebarCollapsed ? 'justify-center' : 'justify-between'">
                <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'space-x-3'">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 10V11m0 0h4m-4 0H7"/></svg>
                    <span x-show="!sidebarCollapsed" x-transition class="whitespace-nowrap">Warehouse Management</span>
                </div>
                <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-180' : ''" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open && !sidebarCollapsed" x-transition class="pl-9 pr-2 py-1 space-y-1">
                <a href="{{ route('warehouse.index') }}" class="block px-3 py-1.5 rounded-md text-[11px] font-medium transition {{ request()->routeIs('warehouse.index') ? 'text-indigo-400 bg-slate-800/80 font-semibold' : 'text-slate-400 hover:text-slate-200' }}">Warehouse</a>
                <a href="{{ route('warehouse.stock') }}" class="block px-3 py-1.5 rounded-md text-[11px] font-medium transition {{ request()->routeIs('warehouse.stock') ? 'text-indigo-400 bg-slate-800/80 font-semibold' : 'text-slate-400 hover:text-slate-200' }}">Stocks</a>
                <a href="{{ route('warehouse.stock-history') }}" class="block px-3 py-1.5 rounded-md text-[11px] font-medium transition {{ request()->routeIs('warehouse.stock-history') ? 'text-indigo-400 bg-slate-800/80 font-semibold' : 'text-slate-400 hover:text-slate-200' }}">Stock History</a>
                <a href="{{ route('inventory.index') }}" class="block px-3 py-1.5 rounded-md text-[11px] font-medium transition {{ request()->routeIs('inventory.*') ? 'text-indigo-400 bg-slate-800/80 font-semibold' : 'text-slate-400 hover:text-slate-200' }}">Inventory Movements</a>
                <a href="{{ route('transfer.index') }}" class="block px-3 py-1.5 rounded-md text-[11px] font-medium transition {{ request()->routeIs('transfer.*') ? 'text-indigo-400 bg-slate-800/80 font-semibold' : 'text-slate-400 hover:text-slate-200' }}">Stock Transfer</a>
            </div>
        </div>

        <!-- Report Menu (Submenus) -->
        <div x-data="{ open: {{ request()->routeIs('report.*') ? 'true' : 'false' }} }">
            <button @click="open = !open; if(sidebarCollapsed) sidebarCollapsed = false"
                    :title="sidebarCollapsed ? 'Report' : ''"
                    class="w-full flex items-center px-3 py-2.5 rounded-lg font-medium transition text-slate-400 hover:bg-slate-800 hover:text-slate-200"
                    :class="sidebarCollapsed ? 'justify-center' : 'justify-between'">
                <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'space-x-3'">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span x-show="!sidebarCollapsed" x-transition class="whitespace-nowrap">Report</span>
                </div>
                <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-180' : ''" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open && !sidebarCollapsed" x-transition class="pl-9 pr-2 py-1 space-y-1">
                <a href="{{ route('report.transactions') }}" class="block px-3 py-1.5 rounded-md text-[11px] font-medium transition {{ request()->routeIs('report.transactions') ? 'text-indigo-400 bg-slate-800/80 font-semibold' : 'text-slate-400 hover:text-slate-200' }}">Transaction Report</a>
                <a href="{{ route('report.items') }}" class="block px-3 py-1.5 rounded-md text-[11px] font-medium transition {{ request()->routeIs('report.items') ? 'text-indigo-400 bg-slate-800/80 font-semibold' : 'text-slate-400 hover:text-slate-200' }}">Item Report</a>
            </div>
        </div>

        <!-- Utilities (With Submenu & Role Check) -->
        @if(auth()->check() && auth()->user()->admin_role == 1)
        <div x-data="{ open: {{ request()->routeIs('region.*') ? 'true' : 'false' }} }">
            <button @click="open = !open; if(sidebarCollapsed) sidebarCollapsed = false"
                    :title="sidebarCollapsed ? 'Utilities' : ''"
                    class="w-full flex items-center px-3 py-2.5 rounded-lg font-medium transition text-slate-400 hover:bg-slate-800 hover:text-slate-200"
                    :class="sidebarCollapsed ? 'justify-center' : 'justify-between'">
                <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'space-x-3'">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span x-show="!sidebarCollapsed" x-transition class="whitespace-nowrap">Utilities</span>
                </div>
                <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-180' : ''" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open && !sidebarCollapsed" x-transition class="pl-9 pr-2 py-1 space-y-1">
                <a href="{{ route('region.index') }}" class="block px-3 py-1.5 rounded-md text-[11px] font-medium transition {{ request()->routeIs('region.index') ? 'text-indigo-400 bg-slate-800/80 font-semibold' : 'text-slate-400 hover:text-slate-200' }}">Region</a>
            </div>
        </div>
        @endif

    </nav>

    <!-- Logout -->
    <div class="p-2 border-t border-slate-800" style="padding-bottom: calc(0.5rem + env(safe-area-inset-bottom));">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit"
                    :title="sidebarCollapsed ? 'Logout' : ''"
                    class="w-full flex items-center px-3 py-2.5 rounded-lg text-xs font-medium text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 transition border border-rose-500/20"
                    :class="sidebarCollapsed ? 'justify-center' : 'space-x-3'">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span x-show="!sidebarCollapsed" x-transition class="whitespace-nowrap">Logout</span>
            </button>
        </form>
    </div>
</aside>
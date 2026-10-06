<template>
  <div class="min-h-screen bg-slate-50 flex flex-col md:flex-row pb-16 md:pb-0 print:bg-white print:pb-0 print:block">
    <!-- Desktop Sidebar -->
    <aside
      class="hidden md:flex flex-col h-screen sticky top-0 bg-slate-900 text-white shrink-0 border-r border-slate-800 transition-all duration-300 z-30 select-none print:hidden"
      :class="isSidebarCollapsed ? 'w-20' : 'w-64'"
    >
      <!-- Brand Logo & Minimize/Maximize Button -->
      <div
        class="h-16 shrink-0 flex items-center border-b border-slate-800/80 bg-slate-950/40 transition-all duration-300"
        :class="isSidebarCollapsed ? 'justify-center px-2' : 'justify-between px-4'"
      >
        <template v-if="!isSidebarCollapsed">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center font-black text-white text-lg shadow-md shadow-indigo-500/20 shrink-0">
              K
            </div>
            <div class="min-w-0">
              <h1 class="font-extrabold text-sm tracking-wide bg-gradient-to-r from-white to-slate-300 bg-clip-text text-transparent truncate">
                KlaimEDP
              </h1>
              <p class="text-[10px] text-slate-400 font-medium tracking-wider uppercase truncate">
                Reimbursement Hub
              </p>
            </div>
          </div>
          <button
            @click="toggleSidebar"
            class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/80 transition"
            title="Perkecil Sidebar (Minimize)"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
          </button>
        </template>
        <template v-else>
          <button
            @click="toggleSidebar"
            class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 hover:from-indigo-500 hover:to-violet-400 flex items-center justify-center font-black text-white text-lg shadow-md shadow-indigo-500/20 group relative transition"
            title="Perbesar Sidebar (Maximize)"
          >
            <span class="group-hover:hidden">K</span>
            <svg class="w-5 h-5 hidden group-hover:block transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
            </svg>
          </button>
        </template>
      </div>

      <!-- Navigation Links Container with Independent Scrolling -->
      <nav class="flex-1 min-h-0 px-3 py-4 space-y-1.5 overflow-y-auto sidebar-scrollbar">
        <!-- Dashboard -->
        <router-link
          to="/"
          class="flex items-center rounded-xl text-xs font-semibold transition group"
          :class="[
            $route.path === '/' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60',
            isSidebarCollapsed ? 'justify-center py-2.5 px-0' : 'gap-3 px-3.5 py-2.5'
          ]"
          title="Dashboard"
        >
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          <span v-if="!isSidebarCollapsed" class="truncate">Dashboard</span>
        </router-link>

        <!-- Buat Klaim Baru -->
        <router-link
          to="/claims/create"
          class="flex items-center rounded-xl text-xs font-semibold transition bg-gradient-to-r from-indigo-500 to-violet-600 text-white shadow-md shadow-indigo-500/20 hover:opacity-95"
          :class="[
            isSidebarCollapsed ? 'justify-center py-2.5 px-0 my-2' : 'gap-3 px-3.5 py-2.5 my-2'
          ]"
          title="Buat Klaim Baru"
        >
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
          </svg>
          <span v-if="!isSidebarCollapsed" class="truncate">+ Buat Klaim Baru</span>
        </router-link>

        <!-- Daftar Klaim -->
        <router-link
          to="/claims"
          class="flex items-center rounded-xl text-xs font-semibold transition group"
          :class="[
            $route.path === '/claims' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60',
            isSidebarCollapsed ? 'justify-center py-2.5 px-0' : 'gap-3 px-3.5 py-2.5'
          ]"
          title="Daftar Klaim"
        >
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <span v-if="!isSidebarCollapsed" class="truncate">Daftar Klaim</span>
        </router-link>

        <!-- Antrean Tugas -->
        <router-link
          to="/tasks"
          class="flex items-center rounded-xl text-xs font-semibold transition group relative"
          :class="[
            $route.path === '/tasks' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60',
            isSidebarCollapsed ? 'justify-center py-2.5 px-0' : 'justify-between px-3.5 py-2.5'
          ]"
          title="Antrean Tugas"
        >
          <div class="flex items-center gap-3">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            <span v-if="!isSidebarCollapsed" class="truncate">Antrean Tugas</span>
          </div>
          <span
            class="rounded-full bg-amber-400 animate-pulse"
            :class="isSidebarCollapsed ? 'absolute top-1.5 right-3 w-2 h-2' : 'w-2 h-2'"
          ></span>
        </router-link>

        <!-- Reports Accordion Menu -->
        <div class="space-y-1">
          <!-- Expanded State -->
          <div v-if="!isSidebarCollapsed" class="flex items-center justify-between">
            <router-link
              to="/reports"
              class="flex-1 flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition group"
              :class="$route.path.startsWith('/reports') ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'"
            >
              <svg class="w-4 h-4 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              <span class="truncate">Laporan & Analisa</span>
            </router-link>
            <button
              @click="showReportsMenu = !showReportsMenu"
              class="p-2 text-slate-400 hover:text-white rounded-lg transition"
              title="Buka / Tutup Menu Laporan"
            >
              <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': showReportsMenu }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
          </div>

          <!-- Collapsed State: Icon Only -->
          <router-link
            v-else
            to="/reports"
            class="flex items-center justify-center py-2.5 px-0 rounded-xl text-xs font-semibold transition group"
            :class="$route.path.startsWith('/reports') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'"
            title="Laporan & Analisa"
          >
            <svg class="w-4 h-4 shrink-0" :class="$route.path.startsWith('/reports') ? 'text-white' : 'text-indigo-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </router-link>

          <!-- Dedicated full-page sub-links (Only when expanded) -->
          <div v-if="!isSidebarCollapsed" v-show="showReportsMenu" class="pl-4 pr-1 py-1 space-y-0.5 border-l border-slate-800 ml-4">
            <router-link
              to="/reports"
              class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-[11px] font-medium transition"
              :class="$route.path === '/reports' ? 'text-indigo-400 bg-indigo-950/40 font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40'"
            >
              <span>📊</span>
              <span class="truncate">Direktori Laporan</span>
            </router-link>

            <router-link
              to="/reports/by-type"
              class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-[11px] font-medium transition"
              :class="$route.path === '/reports/by-type' ? 'text-indigo-400 bg-indigo-950/40 font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40'"
            >
              <span>🏷️</span>
              <span class="truncate">Kategori Klaim</span>
            </router-link>

            <router-link
              to="/reports/historical"
              class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-[11px] font-medium transition"
              :class="$route.path === '/reports/historical' ? 'text-indigo-400 bg-indigo-950/40 font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40'"
            >
              <span>📈</span>
              <span class="truncate">Tren Historis</span>
            </router-link>

            <router-link
              to="/reports/sla"
              class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-[11px] font-medium transition"
              :class="$route.path === '/reports/sla' ? 'text-indigo-400 bg-indigo-950/40 font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40'"
            >
              <span>⏱️</span>
              <span class="truncate">SLA & Latensi</span>
            </router-link>

            <router-link
              to="/reports/bbm"
              class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-[11px] font-medium transition"
              :class="$route.path === '/reports/bbm' ? 'text-indigo-400 bg-indigo-950/40 font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40'"
            >
              <span>⛽</span>
              <span class="truncate">Rasio BBM</span>
            </router-link>

            <router-link
              to="/reports/health"
              class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-[11px] font-medium transition"
              :class="$route.path === '/reports/health' ? 'text-indigo-400 bg-indigo-950/40 font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40'"
            >
              <span>🏥</span>
              <span class="truncate">Plafon Kesehatan</span>
            </router-link>

            <router-link
              to="/reports/approvals"
              class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-[11px] font-medium transition"
              :class="$route.path === '/reports/approvals' ? 'text-indigo-400 bg-indigo-950/40 font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40'"
            >
              <span>🛡️</span>
              <span class="truncate">Approval DBM/BM</span>
            </router-link>

            <router-link
              to="/reports/aging"
              class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-[11px] font-medium transition"
              :class="$route.path === '/reports/aging' ? 'text-indigo-400 bg-indigo-950/40 font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40'"
            >
              <span>⏳</span>
              <span class="truncate">Aging Klaim</span>
            </router-link>

            <router-link
              to="/reports/duplicates"
              class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-[11px] font-medium transition"
              :class="$route.path === '/reports/duplicates' ? 'text-indigo-400 bg-indigo-950/40 font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40'"
            >
              <span>⚠️</span>
              <span class="truncate">Deteksi Duplikasi</span>
            </router-link>
          </div>
        </div>

        <!-- Master Data Menu -->
        <div v-if="authStore.hasRole(['ADMIN', 'EDP_ADM', 'EDP_SUPERVISOR', 'EDP_MANAGER', 'EDP_OPERASIONAL'])" class="space-y-1 pt-2 border-t border-slate-800/80 my-2">
          <div v-if="!isSidebarCollapsed" class="px-3.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">
            Master Data
          </div>

          <router-link
            to="/master/edp-operasional"
            class="flex items-center rounded-xl text-xs font-semibold transition group"
            :class="[
              $route.path.startsWith('/master/edp-operasional') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60',
              isSidebarCollapsed ? 'justify-center py-2.5 px-0' : 'gap-3 px-3.5 py-2.5'
            ]"
            title="Master EDP Lapangan"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span v-if="!isSidebarCollapsed" class="truncate">Master EDP Lapangan</span>
          </router-link>

          <router-link
            to="/master/vehicles"
            class="flex items-center rounded-xl text-xs font-semibold transition group"
            :class="[
              $route.path.startsWith('/master/vehicles') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60',
              isSidebarCollapsed ? 'justify-center py-2.5 px-0' : 'gap-3 px-3.5 py-2.5'
            ]"
            title="Master Kendaraan Dinas"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h2m-6 0a1 1 0 001-1v-4" />
            </svg>
            <span v-if="!isSidebarCollapsed" class="truncate">Master Kendaraan Dinas</span>
          </router-link>
        </div>

        <!-- Audit Trail Log -->
        <router-link
          v-if="authStore.hasPermission('page.audit') || authStore.hasRole(['ADMIN', 'EDP_MANAGER', 'DBM', 'BM'])"
          to="/audit"
          class="flex items-center rounded-xl text-xs font-semibold transition group"
          :class="[
            $route.path === '/audit' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60',
            isSidebarCollapsed ? 'justify-center py-2.5 px-0' : 'gap-3 px-3.5 py-2.5'
          ]"
          title="Audit Trail Log"
        >
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 004.07 3.738M4.07 3.738C2.793 5.485 2 7.653 2 10c0 4.418 3.582 8 8 8" />
          </svg>
          <span v-if="!isSidebarCollapsed" class="truncate">Audit Trail Log</span>
        </router-link>

        <!-- User & Role Permission -->
        <router-link
          v-if="authStore.hasPermission('page.users') || authStore.hasRole('ADMIN')"
          to="/users"
          class="flex items-center rounded-xl text-xs font-semibold transition group"
          :class="[
            $route.path.startsWith('/users') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60',
            isSidebarCollapsed ? 'justify-center py-2.5 px-0' : 'gap-3 px-3.5 py-2.5'
          ]"
          title="User & Role Permission"
        >
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          <span v-if="!isSidebarCollapsed" class="truncate">User & Role Permission</span>
        </router-link>
      </nav>

      <!-- Bottom User Profile Card (Pinned at Bottom) -->
      <div class="shrink-0 p-3 border-t border-slate-800 bg-slate-950/60 transition-all duration-300">
        <!-- Expanded Profile -->
        <div v-if="!isSidebarCollapsed" class="p-2.5 rounded-xl bg-slate-800/60 flex items-center justify-between">
          <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-8 h-8 rounded-full bg-indigo-500/20 text-indigo-400 font-bold flex items-center justify-center shrink-0 border border-indigo-500/30 text-xs">
              {{ authStore.user?.name?.charAt(0) || 'U' }}
            </div>
            <div class="min-w-0">
              <p class="text-xs font-bold truncate text-white">
                {{ authStore.user?.name || 'User' }}
              </p>
              <p class="text-[10px] text-indigo-400 font-medium truncate">
                {{ authStore.primaryRole }}
              </p>
            </div>
          </div>
          <button @click="authStore.logout()" title="Logout" class="text-slate-400 hover:text-rose-400 p-1 rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        </div>

        <!-- Collapsed Profile -->
        <div v-else class="flex flex-col items-center gap-2 py-1">
          <div
            class="w-8 h-8 rounded-full bg-indigo-500/20 text-indigo-400 font-bold flex items-center justify-center shrink-0 border border-indigo-500/30 text-xs cursor-pointer hover:border-indigo-400 transition"
            :title="`${authStore.user?.name || 'User'} (${authStore.primaryRole})`"
          >
            {{ authStore.user?.name?.charAt(0) || 'U' }}
          </div>
          <button
            @click="authStore.logout()"
            title="Logout"
            class="text-slate-400 hover:text-rose-400 p-1.5 rounded-lg hover:bg-slate-800/60 transition"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 print:w-full print:block">
      <!-- Top Navigation Bar -->
      <header class="h-16 bg-white border-b border-slate-200/80 px-4 md:px-8 flex items-center justify-between sticky top-0 z-30 shadow-xs print:hidden">
        <!-- Left: Desktop Sidebar Toggle, Mobile Brand & Switcher -->
        <div class="flex items-center gap-3">
          <!-- Desktop Sidebar Toggle Button -->
          <button
            @click="toggleSidebar"
            class="hidden md:inline-flex items-center justify-center p-2 rounded-xl text-slate-500 hover:text-indigo-600 hover:bg-slate-100 border border-slate-200/80 transition"
            :title="isSidebarCollapsed ? 'Perbesar Sidebar (Maximize)' : 'Perkecil Sidebar (Minimize)'"
          >
            <svg class="w-5 h-5 transition-transform" :class="{ 'rotate-180': isSidebarCollapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16" />
            </svg>
          </button>

          <div class="md:hidden flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-indigo-600 text-white font-bold text-xs flex items-center justify-center">
              K
            </div>
            <span class="font-extrabold text-sm text-slate-800">KlaimEDP</span>
          </div>

          <!-- Quick Test Switcher Badge -->
          <div class="relative">
            <button
              @click="showRoleDropdown = !showRoleDropdown"
              class="flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 transition text-slate-700"
            >
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
              <span>Role: <strong class="text-indigo-600 font-bold">{{ authStore.primaryRole }}</strong></span>
              <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- Role Switch Dropdown -->
            <div
              v-if="showRoleDropdown"
              class="absolute left-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-100 p-2 z-50 animate-in fade-in zoom-in-95 duration-150"
            >
              <div class="px-3 py-2 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                Ganti Akun Demo (Uji Otorisasi)
              </div>
              <div class="max-h-72 overflow-y-auto py-1 space-y-0.5">
                <button
                  v-for="u in demoUsers"
                  :key="u.email"
                  @click="switchUser(u.email)"
                  class="w-full text-left px-3 py-2 rounded-xl text-xs flex items-center justify-between hover:bg-slate-50 transition"
                  :class="authStore.user?.email === u.email ? 'bg-indigo-50 font-bold text-indigo-700' : 'text-slate-700'"
                >
                  <div>
                    <p class="font-bold">{{ u.role }}</p>
                    <p class="text-[11px] text-slate-400 font-normal">{{ u.name }}</p>
                  </div>
                  <span v-if="authStore.user?.email === u.email" class="text-indigo-600 font-bold">✓</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Notification & Mobile User -->
        <div class="flex items-center gap-3">
          <!-- Notification Bell -->
          <div class="relative">
            <button
              @click="toggleNotifications"
              class="p-2 rounded-xl hover:bg-slate-100 text-slate-500 relative transition"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <span
                v-if="unreadCount > 0"
                class="absolute top-1.5 right-1.5 w-4 h-4 rounded-full bg-rose-500 text-white font-bold text-[10px] flex items-center justify-center animate-pulse"
              >
                {{ unreadCount }}
              </span>
            </button>

            <!-- Notifications Dropdown -->
            <div
              v-if="showNotifications"
              class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-100 p-2 z-50 animate-in fade-in zoom-in-95 duration-150"
            >
              <div class="px-3 py-2 border-b border-slate-100 flex items-center justify-between text-xs">
                <span class="font-bold text-slate-800">Notifikasi</span>
                <button @click="markAllRead" class="text-indigo-600 hover:underline text-[11px] font-semibold">
                  Tandai Dibaca
                </button>
              </div>
              <div class="max-h-72 overflow-y-auto divide-y divide-slate-50 py-1">
                <div v-if="notifications.length === 0" class="p-4 text-center text-xs text-slate-400">
                  Tidak ada notifikasi baru.
                </div>
                <div
                  v-for="n in notifications"
                  :key="n.id"
                  class="p-3 hover:bg-slate-50 transition rounded-xl text-xs"
                >
                  <p class="font-bold text-slate-800">{{ n.title }}</p>
                  <p class="text-slate-500 text-[11px] mt-0.5 leading-relaxed">{{ n.message }}</p>
                  <p class="text-[10px] text-slate-400 mt-1">{{ formatTimeAgo(n.created_at) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Mobile user initial -->
          <div class="md:hidden flex items-center gap-2">
            <button @click="authStore.logout()" class="w-8 h-8 rounded-full bg-slate-800 text-white font-bold text-xs flex items-center justify-center">
              {{ authStore.user?.name?.charAt(0) || 'U' }}
            </button>
          </div>
        </div>
      </header>

      <!-- Router View Container -->
      <main class="flex-1 p-4 md:p-8 max-w-7xl w-full mx-auto print:p-0 print:m-0 print:max-w-none print:w-full">
        <router-view />
      </main>
    </div>

    <!-- Mobile Bottom Navigation Bar -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 h-16 bg-white border-t border-slate-200/80 px-4 flex items-center justify-around z-40 shadow-lg print:hidden">
      <router-link to="/" class="flex flex-col items-center gap-1 text-[11px] font-semibold text-slate-500" active-class="text-indigo-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span>Home</span>
      </router-link>

      <router-link to="/claims" class="flex flex-col items-center gap-1 text-[11px] font-semibold text-slate-500" active-class="text-indigo-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span>Klaim</span>
      </router-link>

      <!-- Center Plus Button -->
      <router-link to="/claims/create" class="-mt-5 w-12 h-12 rounded-full bg-gradient-to-tr from-indigo-600 to-violet-500 text-white flex items-center justify-center shadow-lg shadow-indigo-500/30">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
        </svg>
      </router-link>

      <router-link to="/tasks" class="flex flex-col items-center gap-1 text-[11px] font-semibold text-slate-500 relative" active-class="text-indigo-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
        </svg>
        <span>Tugas</span>
        <span class="absolute top-0 right-1 w-2 h-2 rounded-full bg-amber-400"></span>
      </router-link>

      <router-link to="/reports" class="flex flex-col items-center gap-1 text-[11px] font-semibold text-slate-500" active-class="text-indigo-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <span>Laporan</span>
      </router-link>
    </nav>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useAuthStore } from '../../stores/auth';
import api from '../../api/client';

const authStore = useAuthStore();
const showRoleDropdown = ref(false);
const showNotifications = ref(false);
const showReportsMenu = ref(true);
const notifications = ref([]);
const unreadCount = ref(0);
const isSidebarCollapsed = ref(localStorage.getItem('klaimedp_sidebar_collapsed') === 'true');

function toggleSidebar() {
  isSidebarCollapsed.value = !isSidebarCollapsed.value;
  try {
    localStorage.setItem('klaimedp_sidebar_collapsed', isSidebarCollapsed.value ? 'true' : 'false');
  } catch (e) {
    // ignore
  }
}

const demoUsers = [
  { role: 'EDP Operasional (Input Nota)', name: 'Budi Operasional', email: 'operasional@klaimedp.com' },
  { role: 'EDP Adm (Review)', name: 'Siti Admin EDP', email: 'adm@klaimedp.com' },
  { role: 'EDP Supervisor (Verifikasi 1)', name: 'Ahmad Supervisor', email: 'supervisor@klaimedp.com' },
  { role: 'EDP Manager (Verifikasi 2)', name: 'Rendra Manager EDP', email: 'manager@klaimedp.com' },
  { role: 'GA / Finance (Bayar)', name: 'Dewi Finance', email: 'finance@klaimedp.com' },
  { role: 'DBM (Management Approver)', name: 'Hendra DBM', email: 'dbm@klaimedp.com' },
  { role: 'BM (Branch Manager Approver)', name: 'Bambang BM', email: 'bm@klaimedp.com' },
  { role: 'Administrator Sistem', name: 'Super Administrator', email: 'admin@klaimedp.com' },
];

async function switchUser(email) {
  showRoleDropdown.value = false;
  await authStore.demoLogin(email);
  window.location.reload();
}

async function fetchNotifications() {
  try {
    const res = await api.get('/notifications');
    notifications.value = res.data.data;
    unreadCount.value = res.data.unread_count;
  } catch (e) {
    // ignore
  }
}

function toggleNotifications() {
  showNotifications.value = !showNotifications.value;
  if (showNotifications.value) {
    fetchNotifications();
  }
}

async function markAllRead() {
  try {
    await api.post('/notifications/read-all');
    unreadCount.value = 0;
  } catch (e) {
    // ignore
  }
}

function formatTimeAgo(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

onMounted(() => {
  fetchNotifications();
});
</script>

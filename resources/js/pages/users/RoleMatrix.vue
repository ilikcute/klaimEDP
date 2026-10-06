<template>
  <div class="space-y-6 max-w-6xl mx-auto pb-12">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
          <router-link to="/users" class="hover:text-indigo-600 transition">Pengguna</router-link>
          <span>/</span>
          <span class="text-indigo-600">Matriks Default Role</span>
        </nav>
        <h2 class="text-xl font-black text-slate-900 tracking-tight">
          Matriks Hak Akses Default Role Jabatan
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Referensi wewenang dan hak akses bawaan untuk setiap role jabatan di sistem KlaimEDP
        </p>
      </div>

      <router-link
        to="/users"
        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition self-start sm:self-auto"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <span>Kembali ke Daftar Pengguna</span>
      </router-link>
    </div>

    <!-- Educational Banner -->
    <div class="bg-indigo-50/70 p-5 rounded-3xl border border-indigo-100 text-xs text-indigo-950 flex items-start gap-3.5 shadow-xs">
      <span class="text-xl">ℹ️</span>
      <div class="space-y-1">
        <h4 class="font-bold text-indigo-900">Bagaimana Sistem Role & Izin Bekerja?</h4>
        <p class="leading-relaxed text-indigo-800 text-[11px]">
          Setiap role jabatan memiliki paket izin bawaan (default). Saat pengguna baru dibuat dengan role tertentu, pengguna otomatis mewarisi akses tersebut. Jika ada pengguna yang membutuhkan wewenang khusus tambahan (misal: staf lapangan yang diberi akses melihat laporan analitika), Anda dapat memberikan izin khusus secara langsung di halaman <strong>Atur Hak Akses</strong> perorangan.
        </p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="bg-white p-12 rounded-3xl border border-slate-200 text-center text-slate-400 text-xs">
      <div class="flex items-center justify-center gap-2">
        <svg class="w-5 h-5 animate-spin text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <span>Memuat data matriks role...</span>
      </div>
    </div>

    <!-- Roles Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div
        v-for="r in roles"
        :key="r.name"
        class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-4 hover:border-indigo-200 transition"
      >
        <div class="space-y-3">
          <!-- Role Header -->
          <div class="flex items-center justify-between">
            <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold border" :class="getRoleBadgeClass(r.name)">
              {{ r.name }}
            </span>
            <span class="text-[10px] font-mono text-slate-400 font-semibold">{{ r.permissions.length }} Izin</span>
          </div>

          <div>
            <h4 class="font-extrabold text-slate-900 text-sm">{{ r.display_name }}</h4>
            <p class="text-[11px] text-slate-500 leading-relaxed mt-1">
              {{ r.description || 'Role pengguna operasional sistem.' }}
            </p>
          </div>
        </div>

        <!-- Accessible Pages Section -->
        <div class="pt-3 border-t border-slate-100 space-y-2">
          <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
            Akses Halaman Bawaan:
          </p>
          <div class="flex flex-wrap gap-1 max-h-32 overflow-y-auto pr-1">
            <span
              v-if="r.name === 'ADMIN'"
              class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 text-[10px] font-bold border border-purple-200"
            >
              ★ Semua Halaman (Super Admin)
            </span>
            <template v-else>
              <span
                v-for="p in r.permissions.filter(p => p.startsWith('page.'))"
                :key="p"
                class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px] border border-slate-200"
              >
                {{ formatPermName(p) }}
              </span>
            </template>
          </div>

          <!-- Link to filter users by this role -->
          <div class="pt-2">
            <router-link
              :to="`/users?role=${r.name}`"
              class="text-indigo-600 hover:text-indigo-800 text-[11px] font-bold inline-flex items-center gap-1 group"
            >
              <span>Lihat Pengguna Role Ini</span>
              <span class="transition-transform group-hover:translate-x-0.5">→</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import api from '../../api/client';

const roles = ref([]);
const isLoading = ref(true);

async function loadRoles() {
  isLoading.value = true;
  try {
    const res = await api.get('/users/roles');
    roles.value = res.data.data;
  } catch (e) {
    console.error('Failed to load roles:', e);
  } finally {
    isLoading.value = false;
  }
}

function getRoleBadgeClass(roleName) {
  switch (roleName) {
    case 'ADMIN':
      return 'bg-purple-50 text-purple-700 border-purple-200';
    case 'EDP_OPERASIONAL':
      return 'bg-blue-50 text-blue-700 border-blue-200';
    case 'EDP_ADM':
      return 'bg-cyan-50 text-cyan-700 border-cyan-200';
    case 'EDP_SUPERVISOR':
      return 'bg-amber-50 text-amber-700 border-amber-200';
    case 'EDP_MANAGER':
      return 'bg-orange-50 text-orange-700 border-orange-200';
    case 'GA_FINANCE':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200';
    case 'DBM':
      return 'bg-indigo-50 text-indigo-700 border-indigo-200';
    case 'BM':
      return 'bg-rose-50 text-rose-700 border-rose-200';
    default:
      return 'bg-slate-50 text-slate-700 border-slate-200';
  }
}

function formatPermName(perm) {
  return perm.replace('page.', '').replace('_', ' ');
}

onMounted(() => {
  loadRoles();
});
</script>

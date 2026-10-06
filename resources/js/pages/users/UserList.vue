<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 text-xs font-semibold text-indigo-600 mb-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          <span>Pengaturan Keamanan & Sistem</span>
        </div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight">
          Manajemen Pengguna & Hak Akses
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Kelola data pengguna, status akun, role jabatan, serta tentukan izin akses halaman dan fitur sistem
        </p>
      </div>

      <!-- Header Action Buttons -->
      <div class="flex items-center gap-2.5 self-start sm:self-auto">
        <router-link
          to="/users/roles"
          class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition"
          title="Lihat matriks hak akses default role"
        >
          <span>🛡️</span>
          <span>Matriks Default Role</span>
        </router-link>

        <router-link
          to="/users/create"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-500/20 transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>+ Tambah Pengguna Baru</span>
        </router-link>
      </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Pengguna</p>
        <p class="text-xl font-black text-slate-900 mt-1">{{ users.length }}</p>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Pengguna Aktif</p>
        <p class="text-xl font-black text-emerald-600 mt-1">{{ users.filter(u => u.is_active).length }}</p>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <p class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Akses Kustom</p>
        <p class="text-xl font-black text-amber-600 mt-1">{{ users.filter(u => u.direct_permissions && u.direct_permissions.length > 0).length }}</p>
      </div>
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider">Role Terdaftar</p>
        <p class="text-xl font-black text-indigo-600 mt-1">{{ roles.length }}</p>
      </div>
    </div>

    <!-- Search & Filters Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row gap-3 items-center justify-between">
      <div class="relative w-full sm:w-80">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama, email, NIK, atau wilayah..."
          class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500 transition"
        />
        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>

      <div class="flex items-center gap-2 w-full sm:w-auto">
        <select
          v-model="roleFilter"
          class="w-full sm:w-auto px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
        >
          <option value="">Semua Role Jabatan</option>
          <option v-for="r in roles" :key="r.name" :value="r.name">
            {{ r.display_name }}
          </option>
        </select>

        <select
          v-model="statusFilter"
          class="w-full sm:w-auto px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
        >
          <option value="">Semua Status</option>
          <option value="1">Aktif</option>
          <option value="0">Nonaktif</option>
        </select>

        <button
          @click="fetchData"
          :disabled="isLoading"
          class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 transition disabled:opacity-50 shrink-0"
          title="Segarkan data"
        >
          <svg class="w-4 h-4" :class="{ 'animate-spin': isLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="border-b border-slate-100 bg-slate-50/50 text-slate-400 font-bold uppercase text-[10px]">
              <th class="p-4">Pengguna & NIK</th>
              <th class="p-4">Wilayah</th>
              <th class="p-4">Role Utama</th>
              <th class="p-4">Status</th>
              <th class="p-4">Hak Akses Halaman</th>
              <th class="p-4">Tipe Izin</th>
              <th class="p-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="isLoading">
              <td colspan="7" class="p-8 text-center text-slate-400">
                <div class="flex items-center justify-center gap-2">
                  <svg class="w-4 h-4 animate-spin text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                  <span>Memuat data pengguna...</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="filteredUsers.length === 0">
              <td colspan="7" class="p-8 text-center text-slate-400">
                Tidak ada pengguna yang cocok dengan kriteria pencarian atau filter.
              </td>
            </tr>
            <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-slate-50/80 transition">
              <!-- Nama & NIK & Email -->
              <td class="p-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center shrink-0">
                    {{ user.name.charAt(0) }}
                  </div>
                  <div>
                    <div class="flex items-center gap-2">
                      <p class="font-bold text-slate-900">{{ user.name }}</p>
                      <span v-if="user.nik" class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-mono text-[10px] font-semibold border border-slate-200">
                        {{ user.nik }}
                      </span>
                    </div>
                    <p class="text-[11px] text-slate-400 font-mono">{{ user.email }}</p>
                  </div>
                </div>
              </td>

              <!-- Wilayah -->
              <td class="p-4">
                <div class="flex items-center gap-1.5 text-xs text-slate-700 font-medium">
                  <span class="text-indigo-500">📍</span>
                  <span>{{ user.wilayah || '-' }}</span>
                </div>
              </td>

              <!-- Role Utama -->
              <td class="p-4">
                <span
                  v-for="r in user.roles"
                  :key="r.name"
                  class="px-2.5 py-1 rounded-lg text-[10px] font-bold border"
                  :class="getRoleBadgeClass(r.name)"
                >
                  {{ r.display_name }}
                </span>
              </td>

              <!-- Status -->
              <td class="p-4">
                <span
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                  :class="user.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200'"
                >
                  {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>

              <!-- Accessible Pages Summary -->
              <td class="p-4 max-w-xs">
                <div class="flex flex-wrap gap-1">
                  <span
                    v-if="user.roles.some(r => r.name === 'ADMIN')"
                    class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 border border-purple-200 font-bold text-[10px]"
                  >
                    ★ Akses Semua Halaman (Super Admin)
                  </span>
                  <template v-else>
                    <span
                      v-for="p in user.accessible_pages.slice(0, 3)"
                      :key="p.name"
                      class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200 text-[10px] whitespace-nowrap"
                    >
                      {{ p.display_name.replace('Halaman: ', '') }}
                    </span>
                    <span
                      v-if="user.accessible_pages.length > 3"
                      class="px-1.5 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[10px] font-bold"
                      :title="user.accessible_pages.map(p => p.display_name).join(', ')"
                    >
                      +{{ user.accessible_pages.length - 3 }} lainnya
                    </span>
                  </template>
                </div>
              </td>

              <!-- Tipe Izin (Default vs Custom) -->
              <td class="p-4">
                <span
                  v-if="user.direct_permissions && user.direct_permissions.length > 0"
                  class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 border border-amber-200 text-[10px] font-bold"
                >
                  Custom ({{ user.direct_permissions.length }} Izin Langsung)
                </span>
                <span
                  v-else
                  class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px]"
                >
                  Default Role
                </span>
              </td>

              <!-- Aksi -->
              <td class="p-4 text-center">
                <div class="inline-flex items-center gap-1.5">
                  <router-link
                    :to="`/users/${user.id}/permissions`"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-indigo-200 bg-indigo-50/60 hover:bg-indigo-100 text-indigo-700 font-bold text-[11px] transition shadow-xs"
                    title="Buka Halaman Pengaturan Hak Akses Pengguna"
                  >
                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    <span>Atur Hak Akses</span>
                  </router-link>

                  <button
                    @click="confirmDelete(user)"
                    class="p-1.5 rounded-xl hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition"
                    title="Hapus / Nonaktifkan Pengguna"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../api/client';

const route = useRoute();
const isLoading = ref(false);
const users = ref([]);
const roles = ref([]);
const searchQuery = ref('');
const roleFilter = ref(route.query.role || '');
const statusFilter = ref('');

async function fetchData() {
  isLoading.value = true;
  try {
    const [resUsers, resRoles] = await Promise.all([
      api.get('/users'),
      api.get('/users/roles'),
    ]);
    users.value = resUsers.data.data;
    roles.value = resRoles.data.data;
  } catch (e) {
    console.error('Failed to load users:', e);
  } finally {
    isLoading.value = false;
  }
}

const filteredUsers = computed(() => {
  return users.value.filter(u => {
    const q = searchQuery.value.toLowerCase().trim();
    const matchesSearch = !q ||
      u.name.toLowerCase().includes(q) ||
      u.email.toLowerCase().includes(q) ||
      (u.nik && u.nik.toLowerCase().includes(q)) ||
      (u.wilayah && u.wilayah.toLowerCase().includes(q));

    const matchesRole = !roleFilter.value ||
      u.roles.some(r => r.name === roleFilter.value);

    const matchesStatus = statusFilter.value === '' ||
      (statusFilter.value === '1' ? u.is_active : !u.is_active);

    return matchesSearch && matchesRole && matchesStatus;
  });
});

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

async function confirmDelete(user) {
  if (confirm(`Apakah Anda yakin ingin menonaktifkan/menghapus pengguna "${user.name}" (${user.email})?`)) {
    try {
      await api.delete(`/users/${user.id}`);
      await fetchData();
    } catch (e) {
      alert(e.response?.data?.message || 'Gagal menghapus pengguna.');
    }
  }
}

onMounted(() => {
  fetchData();
});
</script>

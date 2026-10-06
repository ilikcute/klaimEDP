<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 text-xs font-semibold text-indigo-600 mb-1">
          <span>Master Data</span>
          <span>/</span>
          <span>Armada Operasional</span>
        </div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight">
          Master Data Kendaraan Dinas
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Kelola armada kendaraan dinas EDP, nomor polisi, dan penugasan langsung ke petugas EDP lapangan
        </p>
      </div>

      <button
        @click="openCreateModal"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition self-start sm:self-auto"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        + Tambah Kendaraan
      </button>
    </div>

    <!-- Filter & Search Controls -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row gap-3 items-center justify-between">
      <div class="relative w-full sm:w-80">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari kode, nama, atau nopol..."
          class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500 transition"
        />
        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>

      <div class="flex items-center gap-2 w-full sm:w-auto">
        <select
          v-model="typeFilter"
          class="w-full sm:w-auto px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
        >
          <option value="">Semua Jenis</option>
          <option value="MOTOR">Sepeda Motor</option>
          <option value="CAR">Mobil Operasional</option>
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
          class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 transition disabled:opacity-50"
          title="Segarkan data"
        >
          <svg class="w-4 h-4" :class="{ 'animate-spin': isLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="border-b border-slate-100 bg-slate-50/50 text-slate-400 font-bold uppercase text-[10px]">
              <th class="p-4">Kode</th>
              <th class="p-4">Nama Kendaraan & Tipe</th>
              <th class="p-4">Nomor Polisi</th>
              <th class="p-4">EDP Lapangan Terhubung</th>
              <th class="p-4 text-center">Standar Rasio</th>
              <th class="p-4 text-center">Status</th>
              <th class="p-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="isLoading">
              <td colspan="7" class="p-8 text-center text-slate-400">Memuat data armada kendaraan...</td>
            </tr>
            <tr v-else-if="filteredVehicles.length === 0">
              <td colspan="7" class="p-8 text-center text-slate-400">Belum ada data kendaraan dinas terdaftar.</td>
            </tr>
            <tr v-for="v in filteredVehicles" :key="v.id" class="hover:bg-slate-50 transition">
              <td class="p-4 font-mono font-bold text-slate-900">
                <span class="px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200">
                  {{ v.code }}
                </span>
              </td>
              <td class="p-4">
                <div class="flex items-center gap-2.5">
                  <span class="text-lg">{{ v.vehicle_type === 'MOTOR' ? '🛵' : '🚗' }}</span>
                  <div>
                    <p class="font-bold text-slate-900">{{ v.name }}</p>
                    <p class="text-[10px] text-slate-400 font-medium">
                      {{ v.vehicle_type === 'MOTOR' ? 'Sepeda Motor' : 'Mobil Operasional' }}
                    </p>
                  </div>
                </div>
              </td>
              <td class="p-4 font-mono font-bold text-slate-800 tracking-wider">
                {{ v.plate_number }}
              </td>
              <td class="p-4">
                <div v-if="v.assigned_user" class="flex items-center gap-2">
                  <div class="w-6 h-6 rounded-full bg-indigo-50 border border-indigo-200 text-indigo-700 font-bold text-[10px] flex items-center justify-center">
                    {{ v.assigned_user.name.charAt(0) }}
                  </div>
                  <div>
                    <p class="font-bold text-slate-900 leading-tight">{{ v.assigned_user.name }}</p>
                    <p class="text-[10px] text-slate-400 font-mono">{{ v.assigned_user.email }}</p>
                  </div>
                </div>
                <span v-else class="text-slate-400 italic text-[11px] bg-slate-100 px-2 py-0.5 rounded-full">
                  Pool Umum (Bebas Dipakai)
                </span>
              </td>
              <td class="p-4 text-center font-mono font-bold text-indigo-700">
                {{ v.vehicle_type === 'MOTOR' ? '40 KM/L' : '13 KM/L' }}
              </td>
              <td class="p-4 text-center">
                <span
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                  :class="v.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200'"
                >
                  {{ v.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="p-4 text-center">
                <div class="inline-flex items-center gap-1">
                  <button
                    @click="openEditModal(v)"
                    class="p-1.5 rounded-lg hover:bg-slate-100 text-indigo-600 transition"
                    title="Ubah Kendaraan"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="confirmDelete(v)"
                    class="p-1.5 rounded-lg hover:bg-rose-50 text-rose-600 transition"
                    title="Hapus Kendaraan"
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

    <!-- Create / Edit Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-xs animate-in fade-in duration-200"
    >
      <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <h3 class="font-black text-slate-900 text-sm">
            {{ isEditing ? 'Ubah Data Kendaraan' : 'Tambah Kendaraan Dinas Baru' }}
          </h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
            ✕
          </button>
        </div>

        <form @submit.prevent="saveVehicle" class="space-y-3 text-xs">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Kode Kendaraan *</label>
              <input
                v-model="modalForm.code"
                type="text"
                required
                placeholder="Contoh: MTR-03"
                class="w-full p-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 uppercase font-mono"
              />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Jenis Kendaraan *</label>
              <select
                v-model="modalForm.vehicle_type"
                required
                class="w-full p-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 bg-white"
              >
                <option value="MOTOR">Sepeda Motor (40 KM/L)</option>
                <option value="CAR">Mobil Operasional (13 KM/L)</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Nama / Merk Kendaraan *</label>
            <input
              v-model="modalForm.name"
              type="text"
              required
              placeholder="Contoh: Honda Vario 160 EDP 03"
              class="w-full p-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-indigo-500"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Nomor Polisi (Plat) *</label>
            <input
              v-model="modalForm.plate_number"
              type="text"
              required
              placeholder="Contoh: B 5678 EDP"
              class="w-full p-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 uppercase font-mono tracking-wider"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Tugaskan ke EDP Lapangan</label>
            <select
              v-model="modalForm.assigned_user_id"
              class="w-full p-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 bg-white"
            >
              <option :value="null">-- Tidak Ada (Pool Umum) --</option>
              <option
                v-for="u in edpUsers"
                :key="u.id"
                :value="u.id"
              >
                {{ u.name }} ({{ u.email }})
              </option>
            </select>
            <p class="text-[10px] text-slate-400 mt-1">
              Petugas ini akan otomatis mendapatkan opsi kendaraan ini saat mengajukan klaim BBM.
            </p>
          </div>

          <div class="flex items-center gap-2 pt-2">
            <input
              v-model="modalForm.is_active"
              type="checkbox"
              id="is_active_veh"
              class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
            />
            <label for="is_active_veh" class="font-medium text-slate-700">Kendaraan Aktif Siap Pakai</label>
          </div>

          <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <button
              type="button"
              @click="showModal = false"
              class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="isSaving"
              class="px-5 py-2 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition disabled:opacity-50"
            >
              {{ isSaving ? 'Menyimpan...' : 'Simpan Kendaraan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import api from '../../api/client';

const isLoading = ref(true);
const isSaving = ref(false);
const vehicles = ref([]);
const edpUsers = ref([]);
const searchQuery = ref('');
const typeFilter = ref('');
const statusFilter = ref('');

const showModal = ref(false);
const isEditing = ref(false);
const editingVehicleId = ref(null);

const modalForm = reactive({
  code: '',
  name: '',
  vehicle_type: 'MOTOR',
  plate_number: '',
  assigned_user_id: null,
  is_active: true,
});

const filteredVehicles = computed(() => {
  return vehicles.value.filter(v => {
    const q = searchQuery.value.toLowerCase();
    const matchesSearch = !searchQuery.value ||
      v.code.toLowerCase().includes(q) ||
      v.name.toLowerCase().includes(q) ||
      v.plate_number.toLowerCase().includes(q) ||
      (v.assigned_user?.name && v.assigned_user.name.toLowerCase().includes(q));

    const matchesType = !typeFilter.value || v.vehicle_type === typeFilter.value;
    const matchesStatus = statusFilter.value === '' ||
      (statusFilter.value === '1' ? v.is_active : !v.is_active);

    return matchesSearch && matchesType && matchesStatus;
  });
});

async function fetchData() {
  isLoading.value = true;
  try {
    const [vehRes, userRes] = await Promise.all([
      api.get('/master/vehicles'),
      api.get('/master/edp-operasional'),
    ]);
    vehicles.value = vehRes.data.data || [];
    edpUsers.value = userRes.data.data || [];
  } catch (e) {
    // handled
  } finally {
    isLoading.value = false;
  }
}

function openCreateModal() {
  isEditing.value = false;
  editingVehicleId.value = null;
  modalForm.code = '';
  modalForm.name = '';
  modalForm.vehicle_type = 'MOTOR';
  modalForm.plate_number = '';
  modalForm.assigned_user_id = null;
  modalForm.is_active = true;
  showModal.value = true;
}

function openEditModal(v) {
  isEditing.value = true;
  editingVehicleId.value = v.id;
  modalForm.code = v.code;
  modalForm.name = v.name;
  modalForm.vehicle_type = v.vehicle_type;
  modalForm.plate_number = v.plate_number;
  modalForm.assigned_user_id = v.assigned_user_id;
  modalForm.is_active = Boolean(v.is_active);
  showModal.value = true;
}

async function saveVehicle() {
  isSaving.value = true;
  try {
    if (isEditing.value) {
      await api.put(`/master/vehicles/${editingVehicleId.value}`, modalForm);
    } else {
      await api.post('/master/vehicles', modalForm);
    }
    showModal.value = false;
    await fetchData();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menyimpan kendaraan.');
  } finally {
    isSaving.value = false;
  }
}

async function confirmDelete(v) {
  if (confirm(`Yakin ingin menghapus kendaraan ${v.name} (${v.plate_number})?`)) {
    try {
      await api.delete(`/master/vehicles/${v.id}`);
      await fetchData();
    } catch (err) {
      alert(err.response?.data?.message || 'Gagal menghapus kendaraan.');
    }
  }
}

onMounted(() => {
  fetchData();
});
</script>

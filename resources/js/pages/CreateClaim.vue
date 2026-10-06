<template>
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header & Breadcrumbs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <router-link
                    to="/claims"
                    class="text-xs font-bold text-slate-400 hover:text-slate-600 transition flex items-center gap-1.5 mb-1"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Daftar Klaim
                </router-link>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        Form Pengajuan Reimbursement
                    </h2>
                    <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                        Alur Langsung 1 Halaman
                    </span>
                </div>
            </div>

            <!-- Quick Info Badge -->
            <div class="flex items-center gap-2">
                <span
                    v-if="canEditLastOdometer"
                    class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 shadow-2xs"
                >
                    <span>🔓</span> Mode Admin (KM Dapat Disesuaikan)
                </span>
                <span
                    v-else
                    class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs"
                >
                    <span>🔒</span> KM Awal Terkunci Otomatis
                </span>
            </div>
        </div>

        <!-- Main Form Grid: 2 Columns (Form Fields & Sticky Real-Time Ratio Analysis) -->
        <form @submit.prevent="submitClaim(true)" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left / Main Column (Input Sections) -->
            <div class="lg:col-span-8 space-y-5">
                <!-- 1. Kategori Klaim & Informasi Dasar -->
                <div class="bg-white rounded-3xl p-5 sm:p-7 border border-slate-200/80 shadow-xs space-y-5">
                    <!-- Kategori Klaim: Sleek Compact Horizontal Pill Buttons -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2.5 flex items-center justify-between">
                            <span>Kategori Reimbursement <span class="text-rose-500">*</span></span>
                            <span class="text-[11px] font-normal text-slate-400">Pilih salah satu kategori</span>
                        </label>

                        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                            <!-- BBM -->
                            <button
                                type="button"
                                @click="form.claim_type = 'BBM'"
                                class="px-4 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2.5 cursor-pointer border"
                                :class="
                                    form.claim_type === 'BBM'
                                        ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm ring-2 ring-indigo-200'
                                        : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200'
                                "
                            >
                                <span class="text-sm">⛽</span>
                                <span>Bahan Bakar (BBM)</span>
                                <span
                                    v-if="form.claim_type === 'BBM'"
                                    class="w-1.5 h-1.5 rounded-full bg-white ml-0.5"
                                ></span>
                            </button>

                            <!-- TOL -->
                            <button
                                type="button"
                                @click="form.claim_type = 'TOL'"
                                class="px-4 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2.5 cursor-pointer border"
                                :class="
                                    form.claim_type === 'TOL'
                                        ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm ring-2 ring-indigo-200'
                                        : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200'
                                "
                            >
                                <span class="text-sm">🛣️</span>
                                <span>Tol & Parkir</span>
                                <span
                                    v-if="form.claim_type === 'TOL'"
                                    class="w-1.5 h-1.5 rounded-full bg-white ml-0.5"
                                ></span>
                            </button>

                            <!-- HEALTH -->
                            <button
                                type="button"
                                @click="form.claim_type = 'HEALTH'"
                                class="px-4 py-2.5 rounded-2xl text-xs font-bold transition flex items-center gap-2.5 cursor-pointer border"
                                :class="
                                    form.claim_type === 'HEALTH'
                                        ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm ring-2 ring-indigo-200'
                                        : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200'
                                "
                            >
                                <span class="text-sm">🏥</span>
                                <span>Kesehatan</span>
                                <span
                                    v-if="form.claim_type === 'HEALTH'"
                                    class="w-1.5 h-1.5 rounded-full bg-white ml-0.5"
                                ></span>
                            </button>
                        </div>
                    </div>

                    <!-- Tanggal Klaim & Deskripsi / Rute -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-slate-100">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Tanggal Klaim <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.claim_date"
                                type="date"
                                required
                                class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 p-2.5 bg-slate-50/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition font-medium"
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                <span>Keperluan / Deskripsi Klaim <span class="text-rose-500">*</span></span>
                                <span v-if="form.claim_type === 'BBM'" class="text-[10px] text-indigo-600 font-semibold normal-case">
                                    Rute toko asal → toko tujuan
                                </span>
                            </label>
                            <input
                                v-model="form.description"
                                type="text"
                                required
                                :placeholder="descriptionPlaceholder"
                                class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 p-2.5 bg-slate-50/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                            />
                            <p v-if="form.claim_type === 'BBM'" class="text-[10px] text-indigo-600 mt-1 flex items-center gap-1">
                                <span>💡</span> Masukkan rute perjalanan antar toko (contoh: "Supervisi jaringan dari Toko Cabang Barat ke Toko Cabang Timur").
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 2. Data Kendaraan & Spedometer BBM (Hanya jika Kategori BBM) -->
                <div
                    v-if="form.claim_type === 'BBM'"
                    class="bg-white rounded-3xl p-5 sm:p-7 border border-indigo-100 shadow-xs space-y-4 relative overflow-hidden"
                >
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-indigo-50">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-bold">
                                ⛽
                            </span>
                            <div>
                                <h3 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wider">
                                    Data Kendaraan & Spedometer BBM Sesuai Nota
                                </h3>
                                <p class="text-[11px] text-slate-500">
                                    KM Terakhir terkunci otomatis untuk menjaga akurasi perhitungan rasio efisiensi.
                                </p>
                            </div>
                        </div>

                        <span class="text-[11px] text-indigo-700 bg-indigo-50 font-bold px-2.5 py-1 rounded-full border border-indigo-100/80 self-start sm:self-auto">
                            ⚡ Jarak Terhitung Otomatis
                        </span>
                    </div>

                    <!-- Pilihan Kendaraan Dinas -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                            <span>Kendaraan Dinas Operasional <span class="text-rose-500">*</span></span>
                            <span
                                v-if="myAssignedVehicle"
                                class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full"
                            >
                                ✓ Kendaraan Tugas Anda
                            </span>
                        </label>
                        <select
                            v-model="form.vehicle_id"
                            @change="onVehicleChange"
                            class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium"
                        >
                            <option value="" disabled>-- Pilih Kendaraan Dinas --</option>
                            <option
                                v-for="v in vehicles"
                                :key="v.id"
                                :value="v.id"
                            >
                                {{ v.name }} ({{ v.plate_number }})
                                {{
                                    v.assigned_user_id === authStore.user?.id
                                        ? "★ [Kendaraan Anda]"
                                        : v.assigned_user
                                          ? `— [PJ: ${v.assigned_user.name}]`
                                          : "— [Pool Umum]"
                                }}
                            </option>
                        </select>
                    </div>

                    <!-- Grid 4 Kolom: KM Terakhir (Locked), KM Nota Sekarang, Jarak Tempuh, Liter Bensin -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 pt-1">
                        <!-- KM Spedometer Terakhir (Sebelumnya) - TERKUNCI UNTUK NON-ADMIN -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                                <span class="flex items-center gap-1">
                                    <span v-if="!canEditLastOdometer">🔒</span>
                                    <span v-else>🔓</span>
                                    KM Terakhir
                                </span>
                                <span
                                    v-if="!canEditLastOdometer"
                                    class="text-[9px] font-bold text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200"
                                >
                                    Terkunci
                                </span>
                                <span
                                    v-else
                                    class="text-[9px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200"
                                >
                                    Admin Mode
                                </span>
                            </label>

                            <div class="relative">
                                <input
                                    v-model.number="form.last_odometer_km"
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    :readonly="!canEditLastOdometer"
                                    placeholder="0"
                                    class="w-full text-xs rounded-xl border p-2.5 font-mono font-bold transition pr-8"
                                    :class="
                                        !canEditLastOdometer
                                            ? 'bg-slate-100 text-slate-500 border-slate-200 cursor-not-allowed select-none'
                                            : 'bg-white text-slate-800 border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-500'
                                    "
                                />
                                <div
                                    v-if="!canEditLastOdometer"
                                    class="absolute right-2.5 top-2.5 text-slate-400 pointer-events-none"
                                    title="Terkunci otomatis dari sistem"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                            </div>

                            <p v-if="!canEditLastOdometer" class="text-[10px] text-slate-400 mt-1 leading-tight">
                                🔒 Terkunci dari klaim terakhir kendaraan dinas.
                            </p>
                            <p v-else class="text-[10px] text-amber-600 mt-1 font-semibold leading-tight">
                                🔓 Mode Admin: Anda dapat menyesuaikan KM awal.
                            </p>
                        </div>

                        <!-- KM Spedometer Nota Sekarang -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                                <span>KM Nota Sekarang *</span>
                                <span class="text-[9px] text-indigo-600 font-bold bg-indigo-50 px-1.5 py-0.5 rounded">
                                    (Pada Nota)
                                </span>
                            </label>
                            <input
                                v-model.number="form.current_odometer_km"
                                type="number"
                                step="0.1"
                                min="0"
                                required
                                placeholder="Contoh: 12620"
                                class="w-full text-xs rounded-xl border border-indigo-300 p-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono font-bold text-slate-900 shadow-2xs"
                            />
                            <p class="text-[10px] text-indigo-600 mt-1 leading-tight">
                                Angka spedometer di nota klaim saat ini.
                            </p>
                        </div>

                        <!-- Jarak Tempuh (KM) - Terhitung Otomatis -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                                <span>Jarak Tempuh (KM) *</span>
                                <span class="text-[9px] text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.5 rounded">
                                    ⚡ Otomatis
                                </span>
                            </label>
                            <input
                                v-model.number="form.distance_km"
                                type="number"
                                step="0.1"
                                min="0.1"
                                required
                                placeholder="0"
                                class="w-full text-xs rounded-xl border border-emerald-200 p-2.5 bg-emerald-50/50 text-emerald-950 font-mono font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            />
                            <p class="text-[10px] text-slate-500 mt-1 leading-tight font-mono">
                                <span v-if="form.current_odometer_km && form.last_odometer_km != null && form.current_odometer_km >= form.last_odometer_km">
                                    = {{ form.current_odometer_km }} - {{ form.last_odometer_km }} KM
                                </span>
                                <span v-else>
                                    Selisih KM Sekarang - KM Terakhir.
                                </span>
                            </p>
                        </div>

                        <!-- Bensin Terisi Sesuai Nota (Liter) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                                <span>Bensin Terisi (Liter) *</span>
                                <span class="text-[9px] text-rose-500 font-bold bg-rose-50 px-1.5 py-0.5 rounded">
                                    Sesuai Nota
                                </span>
                            </label>
                            <input
                                v-model.number="form.fuel_liter"
                                type="number"
                                step="0.01"
                                min="0.01"
                                required
                                placeholder="Contoh: 3.50"
                                class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono font-bold text-slate-900"
                            />
                            <p class="text-[10px] text-slate-400 mt-1 leading-tight">
                                Berapa liter bensin di nota SPBU.
                            </p>
                        </div>
                    </div>

                    <!-- Warning if KM current is smaller than KM last -->
                    <div
                        v-if="form.current_odometer_km != null && form.last_odometer_km != null && form.current_odometer_km < form.last_odometer_km"
                        class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs flex items-center gap-2"
                    >
                        <span class="text-base">⚠️</span>
                        <span>
                            Posisi KM spedometer saat ini (<strong>{{ form.current_odometer_km }}</strong>) lebih kecil dari KM sebelumnya (<strong>{{ form.last_odometer_km }}</strong>). Pastikan angka spedometer pada nota sudah benar.
                        </span>
                    </div>
                </div>

                <!-- 3. Rincian Nota Transaksi & Lampiran Foto Struk Langsung -->
                <div class="bg-white rounded-3xl p-5 sm:p-7 border border-slate-200/80 shadow-xs space-y-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                <span>🧾</span> Bukti Nota & Foto Struk Transaksi
                            </h3>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                Masukkan rincian nota dan lampirkan langsung foto struk dari kamera HP atau galeri.
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="addEmptyReceipt"
                            class="px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs transition flex items-center gap-1.5 cursor-pointer shadow-2xs"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Nota
                        </button>
                    </div>

                    <!-- Receipts List -->
                    <div class="space-y-4">
                        <div
                            v-for="(r, index) in receipts"
                            :key="index"
                            class="p-4 sm:p-5 rounded-2xl bg-slate-50/70 border border-slate-200/80 relative space-y-4 transition hover:border-slate-300"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white font-black text-xs flex items-center justify-center">
                                        {{ index + 1 }}
                                    </span>
                                    <span class="text-xs font-black text-slate-800 uppercase tracking-wider">
                                        Nota Transaksi #{{ index + 1 }}
                                    </span>
                                </div>

                                <button
                                    v-if="receipts.length > 1"
                                    @click="removeReceipt(index)"
                                    type="button"
                                    class="text-rose-600 hover:text-rose-800 text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Hapus Nota
                                </button>
                            </div>

                            <!-- Baris Input Toko, Nominal, Tanggal -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">
                                        Nama Toko / Merchant *
                                    </label>
                                    <input
                                        v-model="r.merchant"
                                        type="text"
                                        required
                                        placeholder="Contoh: SPBU Pertamina / Apotek"
                                        class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">
                                        Nominal Nota (Rp) *
                                    </label>
                                    <input
                                        v-model.number="r.amount"
                                        type="number"
                                        min="1"
                                        required
                                        placeholder="Contoh: 150000"
                                        class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white font-mono font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 mb-1">
                                        Tanggal Transaksi *
                                    </label>
                                    <input
                                        v-model="r.transaction_date"
                                        type="date"
                                        required
                                        class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium"
                                    />
                                </div>
                            </div>

                            <!-- Lampirkan Foto Nota Langsung (Kamera HP / Galeri) -->
                            <div class="pt-1">
                                <label class="block text-[11px] font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                    <span class="flex items-center gap-1.5">
                                        <span>📸</span> Lampiran Foto Nota / Struk
                                    </span>
                                    <span class="text-[10px] text-indigo-600 font-semibold bg-indigo-50 px-2 py-0.5 rounded-md">
                                        Kamera / Galeri HP
                                    </span>
                                </label>

                                <!-- Preview jika file dipilih -->
                                <div
                                    v-if="r.file"
                                    class="p-3 bg-white rounded-xl border border-indigo-200 space-y-2 shadow-2xs"
                                >
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <img
                                                v-if="r.previewUrl"
                                                :src="r.previewUrl"
                                                alt="Preview Nota"
                                                class="w-14 h-14 object-cover rounded-lg border border-slate-200 shadow-2xs shrink-0"
                                            />
                                            <div
                                                v-else
                                                class="w-14 h-14 rounded-lg bg-indigo-50 text-indigo-600 font-bold flex items-center justify-center text-xs shrink-0"
                                            >
                                                📄 PDF
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-slate-800 truncate">
                                                    {{ r.file.name }}
                                                </p>
                                                <p class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1 mt-0.5">
                                                    <span>✓</span> Foto nota terlampir
                                                    <span class="text-slate-400 font-normal">({{ formatFileSize(r.file.size) }})</span>
                                                </p>
                                            </div>
                                        </div>

                                        <button
                                            type="button"
                                            @click="removeReceiptFile(index)"
                                            class="px-2.5 py-1.5 rounded-lg text-rose-600 hover:bg-rose-50 text-xs font-bold transition shrink-0 cursor-pointer"
                                        >
                                            Ganti Foto
                                        </button>
                                    </div>

                                    <!-- GPS & Time Stamp Info Bar -->
                                    <div
                                        v-if="r.gpsLoading"
                                        class="p-2 rounded-lg bg-amber-50 text-amber-800 text-[10px] font-medium flex items-center gap-1.5 animate-pulse"
                                    >
                                        <span>⏳</span>
                                        <span>Mendeteksi koordinat GPS lokasi HP untuk stempel bukti SPBU...</span>
                                    </div>
                                    <div
                                        v-else-if="r.gpsData?.latitude"
                                        class="p-2 rounded-lg bg-emerald-50 text-emerald-900 text-[10px] flex items-center justify-between flex-wrap gap-1 font-mono border border-emerald-100"
                                    >
                                        <span class="font-bold flex items-center gap-1">
                                            <span>📍</span>
                                            <span>GPS SPBU: {{ Number(r.gpsData.latitude).toFixed(6) }}, {{ Number(r.gpsData.longitude).toFixed(6) }}</span>
                                            <span v-if="r.gpsData.accuracy" class="text-slate-400 font-normal">
                                                (±{{ Math.round(r.gpsData.accuracy) }}m)
                                            </span>
                                        </span>
                                        <span class="text-emerald-700 font-bold bg-white px-2 py-0.5 rounded shadow-2xs">
                                            ✓ Dicap ke Foto
                                        </span>
                                    </div>
                                    <div
                                        v-else-if="r.gpsData"
                                        class="p-2 rounded-lg bg-slate-100 text-slate-600 text-[10px] flex items-center gap-1 font-mono"
                                    >
                                        <span>⏰</span>
                                        <span>Stempel waktu tercatat pada foto • GPS tidak diaktifkan pada peramban</span>
                                    </div>
                                </div>

                                <!-- Input File jika belum ada foto -->
                                <label
                                    v-else
                                    class="flex flex-col sm:flex-row items-center justify-center gap-3 p-3.5 rounded-xl border-2 border-dashed border-indigo-200/90 bg-white hover:bg-indigo-50/40 cursor-pointer transition text-center sm:text-left group"
                                >
                                    <input
                                        type="file"
                                        accept="image/*,application/pdf"
                                        class="hidden"
                                        @change="handleReceiptFileChange(index, $event)"
                                    />
                                    <div class="w-9 h-9 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-700 group-hover:text-indigo-600 transition">
                                            Foto Langsung via Kamera HP atau Pilih dari Galeri
                                        </p>
                                        <p class="text-[10px] text-slate-400">
                                            Dapat berupa foto struk JPG, PNG, atau file PDF (Maks 10MB)
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sticky Summary & Real-Time Ratio Analysis & Submit Card -->
            <div class="lg:col-span-4 space-y-5">
                <div class="sticky top-20 space-y-5">
                    <!-- Real-Time BBM Efficiency Analysis Card (Hanya jika Kategori BBM) -->
                    <div
                        v-if="form.claim_type === 'BBM'"
                        class="rounded-3xl border transition p-5 shadow-xs space-y-4"
                        :class="
                            isRatioCompliant
                                ? 'bg-white border-emerald-200'
                                : 'bg-white border-amber-300'
                        "
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold"
                                    :class="isRatioCompliant ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                                >
                                    ⚡
                                </span>
                                <div>
                                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-800">
                                        Analisa Rasio BBM
                                    </h4>
                                    <p class="text-[10px] text-slate-400">
                                        Kalkulasi Real-Time
                                    </p>
                                </div>
                            </div>

                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wide"
                                :class="
                                    isRatioCompliant
                                        ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                        : 'bg-amber-50 text-amber-700 border border-amber-200'
                                "
                            >
                                <span v-if="isRatioCompliant">✓ Sesuai</span>
                                <span v-else>⚠️ Dibawah Standar</span>
                            </span>
                        </div>

                        <!-- Grid Rasio vs Standar -->
                        <div class="grid grid-cols-2 gap-3 bg-slate-50/70 p-3.5 rounded-2xl border border-slate-200/70 text-xs">
                            <div>
                                <p class="text-[10px] text-slate-400 font-bold uppercase">Rasio Aktual</p>
                                <p
                                    class="font-black font-mono text-lg mt-0.5"
                                    :class="isRatioCompliant ? 'text-emerald-700' : 'text-amber-700'"
                                >
                                    {{ computedKmLiter }} <span class="text-xs font-normal">KM/L</span>
                                </p>
                                <p class="text-[10px] text-slate-400 font-mono mt-0.5">
                                    {{ form.distance_km || 0 }} KM ÷ {{ form.fuel_liter || 0 }} L
                                </p>
                            </div>

                            <div>
                                <p class="text-[10px] text-slate-400 font-bold uppercase">Standar Kebijakan</p>
                                <p class="font-black font-mono text-lg text-indigo-700 mt-0.5">
                                    {{ standardKmLiter }} <span class="text-xs font-normal">KM/L</span>
                                </p>
                                <p class="text-[10px] text-slate-400 mt-0.5">
                                    {{ selectedVehicle?.vehicle_type === 'CAR' ? 'Standar Mobil' : 'Standar Motor' }}
                                </p>
                            </div>
                        </div>

                        <!-- Alert Penjelasan Rasio -->
                        <div
                            class="text-xs p-3 rounded-xl flex items-start gap-2.5 leading-relaxed"
                            :class="
                                isRatioCompliant
                                    ? 'bg-emerald-50/80 text-emerald-900 border border-emerald-100'
                                    : 'bg-amber-50/90 text-amber-900 border border-amber-200'
                            "
                        >
                            <span class="text-sm shrink-0">{{ isRatioCompliant ? '✨' : '⚠️' }}</span>
                            <div class="text-[11px]">
                                <span v-if="computedKmLiter <= 0">
                                    Masukkan KM Nota dan Liter Bensin untuk melihat hasil rasio efisiensi.
                                </span>
                                <span v-else-if="isRatioCompliant">
                                    Rasio <strong>{{ computedKmLiter }} KM/L</strong> memenuhi standar operasional ({{ standardKmLiter }} KM/L).
                                </span>
                                <span v-else>
                                    Rasio <strong>{{ computedKmLiter }} KM/L</strong> lebih boros dari standar ({{ standardKmLiter }} KM/L). Akan otomatis diberi catatan review ke SPV & Manager.
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Summary & Submission Card -->
                    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-4">
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 pb-2 border-b border-slate-100 flex items-center justify-between">
                            <span>Ringkasan Pengajuan</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700">
                                {{ form.claim_type }}
                            </span>
                        </h4>

                        <!-- Rincian Data Ringkas -->
                        <div class="space-y-2.5 text-xs">
                            <div class="flex items-center justify-between text-slate-500">
                                <span>Tanggal Pengajuan:</span>
                                <span class="font-bold text-slate-800">{{ form.claim_date || '-' }}</span>
                            </div>

                            <div class="flex items-center justify-between text-slate-500">
                                <span>Jumlah Nota:</span>
                                <span class="font-bold text-slate-800">{{ receipts.length }} Nota Transaksi</span>
                            </div>

                            <div v-if="form.claim_type === 'BBM'" class="flex items-center justify-between text-slate-500">
                                <span>Total Jarak:</span>
                                <span class="font-bold text-slate-800 font-mono">{{ form.distance_km || 0 }} KM</span>
                            </div>

                            <div v-if="form.claim_type === 'BBM'" class="flex items-center justify-between text-slate-500">
                                <span>Total Bensin:</span>
                                <span class="font-bold text-slate-800 font-mono">{{ form.fuel_liter || 0 }} Liter</span>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                                <span class="font-bold text-slate-700">Total Klaim:</span>
                                <span class="text-xl font-black font-mono text-indigo-700">
                                    Rp {{ formatNumber(totalReceiptsAmount) }}
                                </span>
                            </div>
                        </div>

                        <!-- Tombol Aksi: Ajukan & Simpan Draft -->
                        <div class="pt-2 space-y-2.5">
                            <button
                                type="submit"
                                :disabled="submitting || totalReceiptsAmount <= 0"
                                class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs uppercase tracking-wider rounded-xl shadow-md transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg
                                    v-if="submitting"
                                    class="animate-spin h-4 w-4 text-white"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8v8H4z"
                                    ></path>
                                </svg>
                                <span>Ajukan Klaim Sekarang</span>
                            </button>

                            <button
                                type="button"
                                @click="submitClaim(false)"
                                :disabled="submitting || totalReceiptsAmount <= 0"
                                class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Simpan sebagai Draft
                            </button>
                        </div>

                        <p class="text-[10px] text-slate-400 text-center leading-tight pt-1">
                            Data spedometer dan nota diverifikasi oleh sistem serta diaudit berjenjang.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import api from "../api/client";

const router = useRouter();
const authStore = useAuthStore();

const submitting = ref(false);
const vehicles = ref([]);
const bbmStandards = ref([]);

// Role-based privilege: KM Terakhir is strictly LOCKED unless user is ADMIN
const canEditLastOdometer = computed(() => {
    return authStore.user?.roles?.includes("ADMIN") || false;
});

const form = reactive({
    claim_type: "BBM",
    claim_date: new Date().toISOString().split("T")[0],
    description: "",
    vehicle_id: "",
    last_odometer_km: null,
    current_odometer_km: null,
    distance_km: null,
    fuel_liter: null,
});

const receipts = ref([
    {
        merchant: "",
        amount: null,
        transaction_date: new Date().toISOString().split("T")[0],
        file: null,
        previewUrl: null,
    },
]);

const computedKmLiter = computed(() => {
    if (form.distance_km > 0 && form.fuel_liter > 0) {
        return Math.round((form.distance_km / form.fuel_liter) * 100) / 100;
    }
    return 0;
});

const totalReceiptsAmount = computed(() => {
    return receipts.value.reduce((sum, r) => sum + (Number(r.amount) || 0), 0);
});

const descriptionPlaceholder = computed(() => {
    if (form.claim_type === "BBM") {
        return "Contoh: Supervisi jaringan dari Toko Cabang Barat ke Toko Cabang Timur";
    }
    if (form.claim_type === "TOL") {
        return "Contoh: Biaya e-Toll operasional perjalanan antar toko via Tol Gempol-Pasuruan";
    }
    return "Contoh: Penggantian biaya rawat jalan & obat resep dokter di RS Mitra";
});

const selectedVehicle = computed(() => {
    return vehicles.value.find((item) => item.id === form.vehicle_id);
});

const selectedVehicleName = computed(() => {
    const v = selectedVehicle.value;
    return v ? `${v.name} (${v.plate_number})` : "-";
});

const standardKmLiter = computed(() => {
    if (!selectedVehicle.value) return 40;
    const vType = selectedVehicle.value.vehicle_type;
    const activeStd = bbmStandards.value.find(
        (s) => s.vehicle_type === vType && s.is_active
    );
    if (activeStd) return Number(activeStd.standard_km_per_liter);
    return vType === "CAR" ? 13 : 40;
});

const bbmVarianceKmLiter = computed(() => {
    if (computedKmLiter.value <= 0 || standardKmLiter.value <= 0) return 0;
    return Math.round((computedKmLiter.value - standardKmLiter.value) * 100) / 100;
});

const isRatioCompliant = computed(() => {
    if (computedKmLiter.value <= 0) return true;
    const std = standardKmLiter.value;
    if (std <= 0) return true;
    const diff = computedKmLiter.value - std;
    if (diff >= 0) return true;
    // Tolerance within 10% below standard
    return diff / std >= -0.1;
});

// Auto calculate distance_km when current_odometer_km or last_odometer_km changes
watch(
    [() => form.current_odometer_km, () => form.last_odometer_km],
    ([currKm, lastKm]) => {
        if (
            currKm !== null &&
            currKm !== undefined &&
            currKm !== "" &&
            lastKm !== null &&
            lastKm !== undefined &&
            lastKm !== ""
        ) {
            const diff = Number(currKm) - Number(lastKm);
            if (diff >= 0) {
                form.distance_km = Math.round(diff * 10) / 10;
            } else {
                form.distance_km = 0;
            }
        }
    }
);

function onVehicleChange() {
    if (!form.vehicle_id) return;
    const v = vehicles.value.find((item) => item.id === form.vehicle_id);
    if (v && v.last_odometer_km !== null && v.last_odometer_km !== undefined) {
        form.last_odometer_km = Number(v.last_odometer_km);
        if (
            form.current_odometer_km !== null &&
            form.current_odometer_km !== undefined &&
            form.current_odometer_km !== ""
        ) {
            const diff = Number(form.current_odometer_km) - Number(form.last_odometer_km);
            form.distance_km = diff > 0 ? Math.round(diff * 10) / 10 : 0;
        }
    }
}

function addEmptyReceipt() {
    receipts.value.push({
        merchant: "",
        amount: null,
        transaction_date: form.claim_date,
        file: null,
        previewUrl: null,
        gpsData: null,
        gpsLoading: false,
    });
}

function removeReceipt(index) {
    if (receipts.value[index]?.previewUrl) {
        URL.revokeObjectURL(receipts.value[index].previewUrl);
    }
    receipts.value.splice(index, 1);
}

// Function to burn GPS coordinates & Timestamp onto image canvas
async function addWatermarkToImage(file, coords, merchant) {
    if (!file.type.startsWith("image/")) return file;

    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement("canvas");
                canvas.width = img.width;
                canvas.height = img.height;
                const ctx = canvas.getContext("2d");

                // Draw base image
                ctx.drawImage(img, 0, 0);

                // Calculate font size relative to image dimensions
                const fontSize = Math.max(14, Math.round(canvas.width * 0.024));
                const lineHeight = fontSize * 1.35;
                const pad = fontSize * 0.9;
                const bannerHeight = lineHeight * 3 + pad * 2;

                // Draw dark translucent banner at bottom
                ctx.fillStyle = "rgba(15, 23, 42, 0.85)";
                ctx.fillRect(0, canvas.height - bannerHeight, canvas.width, bannerHeight);

                // Draw accent color border line at top of banner
                ctx.fillStyle = coords.latitude ? "#10b981" : "#f59e0b";
                ctx.fillRect(0, canvas.height - bannerHeight, canvas.width, Math.max(3, Math.round(fontSize * 0.2)));

                // Text styles
                ctx.textBaseline = "top";

                // Line 1: Header / Title
                ctx.font = `bold ${Math.round(fontSize * 1.05)}px sans-serif`;
                ctx.fillStyle = "#ffffff";
                const now = new Date();
                const timeStr = now.toLocaleDateString("id-ID", {
                    day: "2-digit",
                    month: "2-digit",
                    year: "numeric",
                }) + " " + now.toLocaleTimeString("id-ID", { hour12: false }) + " WIB";

                const headerText = coords.latitude
                    ? "📍 BUKTI LOKASI TRANSAKSI SPBU • KLAIM EDP"
                    : "⏰ BUKTI TRANSAKSI OPERASIONAL • KLAIM EDP";
                ctx.fillText(headerText, pad, canvas.height - bannerHeight + pad);

                // Line 2: Coordinates (Lat & Long)
                ctx.font = `bold ${fontSize}px monospace`;
                ctx.fillStyle = coords.latitude ? "#34d399" : "#fbbf24";
                let gpsText = "";
                if (coords.latitude && coords.longitude) {
                    const accStr = coords.accuracy ? ` (Akurasi: ±${Math.round(coords.accuracy)}m)` : "";
                    gpsText = `GPS: ${Number(coords.latitude).toFixed(6)}, ${Number(coords.longitude).toFixed(6)}${accStr}`;
                } else {
                    gpsText = "GPS: Koordinat tidak tersedia (Izin lokasi dinonaktifkan)";
                }
                ctx.fillText(gpsText, pad, canvas.height - bannerHeight + pad + lineHeight);

                // Line 3: Timestamp & Company Tag
                ctx.font = `normal ${Math.round(fontSize * 0.9)}px sans-serif`;
                ctx.fillStyle = "#cbd5e1";
                const merchText = merchant ? ` • ${merchant}` : "";
                const footerText = `Waktu: ${timeStr}${merchText} • PT. INDOMARCO PRISMATAMA`;
                ctx.fillText(footerText, pad, canvas.height - bannerHeight + pad + lineHeight * 2);

                canvas.toBlob(
                    (blob) => {
                        if (blob) {
                            const stampedFile = new File([blob], file.name, { type: "image/jpeg" });
                            resolve(stampedFile);
                        } else {
                            resolve(file);
                        }
                    },
                    "image/jpeg",
                    0.92
                );
            };
            img.onerror = () => resolve(file);
            img.src = e.target.result;
        };
        reader.onerror = () => resolve(file);
        reader.readAsDataURL(file);
    });
}

async function handleReceiptFileChange(index, event) {
    const file = event.target.files?.[0];
    if (!file) return;

    if (receipts.value[index].previewUrl) {
        URL.revokeObjectURL(receipts.value[index].previewUrl);
    }

    receipts.value[index].gpsLoading = true;
    receipts.value[index].gpsData = null;

    const nowIso = new Date().toISOString();

    if (navigator.geolocation && file.type.startsWith("image/")) {
        navigator.geolocation.getCurrentPosition(
            async (pos) => {
                const coords = {
                    latitude: pos.coords.latitude,
                    longitude: pos.coords.longitude,
                    accuracy: pos.coords.accuracy,
                    captured_at: nowIso,
                };
                const stampedFile = await addWatermarkToImage(file, coords, receipts.value[index].merchant);
                receipts.value[index].file = stampedFile;
                receipts.value[index].previewUrl = URL.createObjectURL(stampedFile);
                receipts.value[index].gpsData = coords;
                receipts.value[index].gpsLoading = false;
            },
            async () => {
                // GPS error or permission denied fallback
                const coords = {
                    latitude: null,
                    longitude: null,
                    accuracy: null,
                    captured_at: nowIso,
                };
                const stampedFile = await addWatermarkToImage(file, coords, receipts.value[index].merchant);
                receipts.value[index].file = stampedFile;
                receipts.value[index].previewUrl = URL.createObjectURL(stampedFile);
                receipts.value[index].gpsData = coords;
                receipts.value[index].gpsLoading = false;
            },
            { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
        );
    } else {
        receipts.value[index].file = file;
        receipts.value[index].previewUrl = file.type.startsWith("image/") ? URL.createObjectURL(file) : null;
        receipts.value[index].gpsData = {
            latitude: null,
            longitude: null,
            accuracy: null,
            captured_at: nowIso,
        };
        receipts.value[index].gpsLoading = false;
    }
}

function removeReceiptFile(index) {
    if (receipts.value[index].previewUrl) {
        URL.revokeObjectURL(receipts.value[index].previewUrl);
    }
    receipts.value[index].file = null;
    receipts.value[index].previewUrl = null;
    receipts.value[index].gpsData = null;
    receipts.value[index].gpsLoading = false;
}

function formatFileSize(bytes) {
    if (!bytes) return "0 B";
    const k = 1024;
    const sizes = ["B", "KB", "MB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return (bytes / Math.pow(k, i)).toFixed(1) + " " + sizes[i];
}

function validateForm() {
    if (!form.claim_type) {
        alert("Mohon pilih kategori klaim reimbursement.");
        return false;
    }
    if (!form.claim_date) {
        alert("Mohon isi tanggal klaim.");
        return false;
    }
    if (!form.description || !form.description.trim()) {
        alert("Mohon isi keperluan / deskripsi klaim.");
        return false;
    }

    if (form.claim_type === "BBM") {
        if (!form.vehicle_id) {
            alert("Mohon pilih kendaraan dinas operasional.");
            return false;
        }
        if (
            form.current_odometer_km !== null &&
            form.last_odometer_km !== null &&
            Number(form.current_odometer_km) < Number(form.last_odometer_km)
        ) {
            alert(
                "Posisi KM spedometer saat ini tidak boleh lebih kecil dari KM spedometer terakhir."
            );
            return false;
        }
        if (!form.distance_km || form.distance_km <= 0) {
            alert(
                "Mohon masukkan posisi KM spedometer saat ini pada nota agar jarak tempuh terhitung otomatis."
            );
            return false;
        }
        if (!form.fuel_liter || form.fuel_liter <= 0) {
            alert("Mohon isi jumlah bensin terisi (liter) sesuai dengan nota.");
            return false;
        }
    }

    if (receipts.value.length === 0) {
        alert("Mohon lampirkan minimal 1 bukti nota transaksi.");
        return false;
    }

    for (let i = 0; i < receipts.value.length; i++) {
        const r = receipts.value[i];
        if (!r.merchant || !r.merchant.trim()) {
            alert(`Mohon lengkapi Nama Toko / Merchant pada Nota #${i + 1}.`);
            return false;
        }
        if (!r.amount || Number(r.amount) <= 0) {
            alert(`Mohon isi nominal nota yang valid pada Nota #${i + 1}.`);
            return false;
        }
        if (!r.transaction_date) {
            alert(`Mohon isi tanggal transaksi pada Nota #${i + 1}.`);
            return false;
        }
    }

    return true;
}

async function submitClaim(directSubmit = true) {
    if (!validateForm()) return;

    submitting.value = true;
    try {
        // 1. Create claim
        const claimRes = await api.post("/claims", form);
        const createdClaim = claimRes.data.data;

        // 2. Add receipts & upload linked photos
        for (const r of receipts.value) {
            const receiptPayload = {
                merchant: r.merchant,
                amount: r.amount,
                transaction_date: r.transaction_date,
            };
            const recRes = await api.post(
                `/claims/${createdClaim.id}/receipts`,
                receiptPayload
            );
            const createdReceipt = recRes.data.data;

            if (r.file) {
                const formData = new FormData();
                formData.append("file", r.file);
                formData.append("document_type", "RECEIPT");
                formData.append("claim_receipt_id", createdReceipt.id);
                if (r.gpsData?.latitude && r.gpsData?.longitude) {
                    formData.append("latitude", r.gpsData.latitude);
                    formData.append("longitude", r.gpsData.longitude);
                    if (r.gpsData.accuracy) {
                        formData.append("gps_accuracy_meters", r.gpsData.accuracy);
                    }
                    if (r.gpsData.captured_at) {
                        formData.append("captured_at", r.gpsData.captured_at);
                    }
                }
                await api.post(
                    `/claims/${createdClaim.id}/documents`,
                    formData,
                    {
                        headers: { "Content-Type": "multipart/form-data" },
                    }
                );
            }
        }

        // 3. Submit to workflow if chosen
        if (directSubmit) {
            await api.post(`/claims/${createdClaim.id}/submit`, {
                notes: "Pengajuan klaim diajukan oleh requester.",
            });
        }

        router.push(`/claims/${createdClaim.id}`);
    } catch (err) {
        alert(err.response?.data?.message || "Gagal menyimpan klaim.");
    } finally {
        submitting.value = false;
    }
}

function formatNumber(num) {
    return new Intl.NumberFormat("id-ID").format(num || 0);
}

const myAssignedVehicle = computed(() => {
    return vehicles.value.find(
        (v) =>
            v.id === form.vehicle_id &&
            v.assigned_user_id === authStore.user?.id
    );
});

onMounted(async () => {
    try {
        const [vehRes, stdRes] = await Promise.allSettled([
            api.get("/vehicles"),
            api.get("/bbm/standards"),
        ]);
        if (vehRes.status === "fulfilled") {
            vehicles.value = vehRes.value.data.data || [];
            if (vehicles.value.length > 0) {
                // Prioritize vehicle assigned to the logged-in EDP Operasional user
                const assigned = vehicles.value.find(
                    (v) => v.assigned_user_id === authStore.user?.id
                );
                if (assigned) {
                    form.vehicle_id = assigned.id;
                } else {
                    form.vehicle_id = vehicles.value[0].id;
                }
                onVehicleChange();
            }
        }
        if (stdRes.status === "fulfilled") {
            bbmStandards.value = stdRes.value.data.data || [];
        }
    } catch (e) {
        // ignore
    }
});
</script>

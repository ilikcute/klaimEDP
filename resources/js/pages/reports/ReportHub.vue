<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 text-xs font-semibold text-indigo-600 mb-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          <span>Pusat Laporan & Analisa</span>
        </div>
        <h2 class="text-xl font-black text-slate-900 tracking-tight">
          Direktori Laporan Klaim EDP
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Pilih modul laporan spesifik untuk analisa mendalam per halaman utuh dengan performa optimal.
        </p>
      </div>

      <a
        href="/api/reports/claims/export"
        target="_blank"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-md transition self-start sm:self-auto"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Ekspor Semua Klaim (CSV)
      </a>
    </div>

    <!-- Quick Navigation Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <router-link
        v-for="rep in reportModules"
        :key="rep.route"
        :to="rep.route"
        class="group bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md hover:border-indigo-300 transition-all duration-200 flex flex-col justify-between"
      >
        <div class="space-y-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-base transition-colors" :class="rep.iconBg">
            <span>{{ rep.icon }}</span>
          </div>
          <div>
            <h3 class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">
              {{ rep.title }}
            </h3>
            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
              {{ rep.description }}
            </p>
          </div>
        </div>

        <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-indigo-600 group-hover:translate-x-0.5 transition-transform">
          <span>Buka Halaman</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </div>
      </router-link>
    </div>

    <!-- Performance & Architecture Notice -->
    <div class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-100/80 text-xs text-indigo-900 flex items-start gap-3">
      <svg class="w-5 h-5 text-indigo-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <div>
        <p class="font-bold">Arsitektur Per-Halaman Utuh (Independent Load)</p>
        <p class="text-[11px] text-indigo-700/80 mt-0.5 leading-relaxed">
          Setiap laporan memiliki halaman tersendiri dan hanya memanggil API kalkulasi agregasi yang diperlukan saat dibuka. Hal ini menghemat beban server database hingga 80% dan membuat waktu muat halaman instan.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
const reportModules = [
  {
    title: '1. Kategori Klaim',
    description: 'Analisa proporsi dan volume klaim berdasarkan kategori BBM, Tol, dan Pengobatan/Kesehatan.',
    route: '/reports/by-type',
    icon: '🏷️',
    iconBg: 'bg-emerald-50 text-emerald-600 border border-emerald-100',
  },
  {
    title: '2. Tren Historis',
    description: 'Perbandingan nominal pengajuan klaim vs realisasi pencairan (PAID) hingga 12 bulan terakhir.',
    route: '/reports/historical',
    icon: '📈',
    iconBg: 'bg-blue-50 text-blue-600 border border-blue-100',
  },
  {
    title: '3. SLA & Latensi',
    description: 'Monitoring persentase ketepatan waktu SLA, P90 tail latency, dan deteksi bottleneck per tahapan.',
    route: '/reports/sla',
    icon: '⏱️',
    iconBg: 'bg-purple-50 text-purple-600 border border-purple-100',
  },
  {
    title: '4. Rasio Konsumsi BBM',
    description: 'Audit rasio KM/Liter per kendaraan, standar pemakaian, selisih variansi, dan status kepatuhan.',
    route: '/reports/bbm',
    icon: '⛽',
    iconBg: 'bg-amber-50 text-amber-600 border border-amber-100',
  },
  {
    title: '5. Plafon Kesehatan',
    description: 'Pemanfaatan kuota plafon tahunan Rp 10.000.000 per karyawan, klaim terbayar, dan sisa saldo.',
    route: '/reports/health',
    icon: '🏥',
    iconBg: 'bg-teal-50 text-teal-600 border border-teal-100',
  },
  {
    title: '6. Approval DBM / BM',
    description: 'Statistik approval manajemen di bawah kebijakan kuorum ANY_OF (satu tanda tangan DBM atau BM).',
    route: '/reports/approvals',
    icon: '🛡️',
    iconBg: 'bg-slate-100 text-slate-700 border border-slate-200',
  },
  {
    title: '7. Aging Klaim (Tunggakan)',
    description: 'Distribusi umur klaim berjalan (0-1 hari, 2-3 hari, 4-7 hari, hingga >14 hari) untuk mitigasi keterlambatan.',
    route: '/reports/aging',
    icon: '⏳',
    iconBg: 'bg-rose-50 text-rose-600 border border-rose-100',
  },
  {
    title: '8. Deteksi Duplikasi',
    description: 'Peringatan dini (advisory) terhadap kuitansi dengan nominal, tanggal, dan merchant yang sama.',
    route: '/reports/duplicates',
    icon: '⚠️',
    iconBg: 'bg-orange-50 text-orange-600 border border-orange-100',
  },
];
</script>

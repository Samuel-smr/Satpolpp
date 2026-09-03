@extends('layouts.admin')

@section('title', 'Peta Persebaran')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <style>
        /* Custom map container to fill space */
        .map-container {
            height: calc(100vh - 180px); /* Adjust based on header height */
            min-height: 500px;
        }
        /* Custom popup styling */
        .leaflet-popup-content-wrapper {
            border-radius: 12px;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }
        .leaflet-popup-content {
            margin: 0;
            width: 280px !important;
        }
        .custom-div-icon {
            background: transparent;
            border: none;
        }
        .marker-pin {
            width: 30px;
            height: 30px;
            border-radius: 50% 50% 50% 0;
            background: #2d5c8a; /* Default brand */
            position: absolute;
            transform: rotate(-45deg);
            left: 50%;
            top: 50%;
            margin: -15px 0 0 -15px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: -1px 1px 4px rgba(0,0,0,0.3);
        }
        .marker-pin::after {
            content: '';
            width: 22px;
            height: 22px;
            margin: 4px 0 0 4px;
            background: #fff;
            position: absolute;
            border-radius: 50%;
        }
        .marker-icon {
            position: absolute;
            z-index: 10;
            font-size: 16px;
            color: #2d5c8a;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        /* Color variants */
        .marker-laporan .marker-pin { background: #ef4444; }
        .marker-laporan .marker-icon { color: #ef4444; }
        .marker-kegiatan_eksternal .marker-pin { background: #3b82f6; }
        .marker-kegiatan_eksternal .marker-icon { color: #3b82f6; }
        .marker-kegiatan_internal .marker-pin { background: #10b981; }
        .marker-kegiatan_internal .marker-icon { color: #10b981; }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
    <div x-data="petaManager()" x-init="initPeta()" x-cloak class="flex flex-col lg:flex-row gap-6 h-full">
        
        <!-- Left: Map Area -->
        <div class="flex-1 flex flex-col bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <!-- Map Header & Filters -->
            <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-brand-600">map</span>
                    <h2 class="font-bold text-slate-800">Live Deployment Map</h2>
                </div>
                
                <div class="flex flex-wrap gap-3 items-center">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Filter:</span>
                    
                    <label class="flex items-center gap-2 cursor-pointer bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm hover:bg-slate-50 transition-colors">
                        <input type="checkbox" x-model="filters.laporan" @change="renderMap()" class="w-4 h-4 text-red-500 rounded border-slate-300 focus:ring-red-500">
                        <span class="text-sm font-semibold text-slate-700 flex items-center gap-1">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Laporan/Aduan
                        </span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm hover:bg-slate-50 transition-colors">
                        <input type="checkbox" x-model="filters.kegiatan_eksternal" @change="renderMap()" class="w-4 h-4 text-blue-500 rounded border-slate-300 focus:ring-blue-500">
                        <span class="text-sm font-semibold text-slate-700 flex items-center gap-1">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Kegiatan Eksternal
                        </span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm hover:bg-slate-50 transition-colors">
                        <input type="checkbox" x-model="filters.kegiatan_internal" @change="renderMap()" class="w-4 h-4 text-emerald-500 rounded border-slate-300 focus:ring-emerald-500">
                        <span class="text-sm font-semibold text-slate-700 flex items-center gap-1">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Kegiatan Internal
                        </span>
                    </label>
                </div>
            </div>

            <!-- Search Lokasi -->
            <div class="p-3 border-b border-slate-200 bg-white flex flex-col sm:flex-row gap-2 sm:items-center">
                <div class="flex-1 relative">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
                    <input type="text" x-model="searchQuery" @keyup.enter="searchLocation()" placeholder="Cari lokasi / alamat... mis. Pasar Wates, Kulon Progo"
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sidebar-active/20 focus:border-sidebar-active outline-none transition-all text-sm">
                </div>
                <button type="button" @click="searchLocation()" class="inline-flex items-center justify-center gap-2 bg-sidebar-active hover:bg-sidebar-hover text-white px-5 py-2 rounded-xl text-sm font-semibold shadow-sm transition-colors">
                    <span class="material-symbols-outlined text-[18px]">search</span>
                    Cari Lokasi
                </button>
            </div>

            <!-- The Map -->
            <div id="mainMap" class="w-full flex-1 map-container z-10 relative rounded-b-2xl"></div>
        </div>

        <!-- Right: Real-time Metrics Sidebar -->
        <div class="w-full lg:w-80 flex flex-col gap-6 shrink-0">
            <!-- Stat Cards -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex flex-col">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Active Units</span>
                    <span class="text-2xl font-headline font-bold text-slate-800">142</span>
                </div>
                <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex flex-col">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Open Reports</span>
                    <span class="text-2xl font-headline font-bold text-red-600">28</span>
                </div>
            </div>

            <!-- Charts Container -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm flex-1 flex flex-col">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6 flex items-center gap-2 border-b border-slate-100 pb-3">
                    <span class="material-symbols-outlined text-[20px] text-brand-500">monitoring</span>
                    Real-Time Metrics
                </h3>

                <div class="flex-1 overflow-y-auto pr-2 space-y-8" style="max-height: calc(100vh - 300px);">
                    
                    <!-- Chart 1 -->
                    <div>
                        <h4 class="text-xs font-semibold text-slate-600 mb-3">Incident Categories</h4>
                        <div class="h-40 w-full relative">
                            <canvas id="incidentChart"></canvas>
                        </div>
                    </div>

                    <!-- Chart 2 -->
                    <div>
                        <h4 class="text-xs font-semibold text-slate-600 mb-3">Status Distribution</h4>
                        <div class="h-40 w-full relative flex items-center justify-center">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>

                    <!-- Chart 3 -->
                    <div>
                        <h4 class="text-xs font-semibold text-slate-600 mb-3">Avg Response Time (Mins)</h4>
                        <div class="h-32 w-full relative">
                            <canvas id="responseChart"></canvas>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        function petaManager() {
            return {
                map: null,
                markers: [],
                hotspots: [],
                selectedItem: null,
                searchQuery: '',
                
                filters: {
                    laporan: true,
                    kegiatan_eksternal: true,
                    kegiatan_internal: true
                },

                // Mock Data for Map
                mapData: [
                    // Kegiatan Eksternal
                    { id: 'K1', type: 'kegiatan_eksternal', title: 'Operasi Yustisi KTP', desc: 'Pemeriksaan identitas pendatang baru pasca libur panjang.', location: 'Terminal Kampung Rambutan', lat: -6.3093, lng: 106.8834, status: 'Berlangsung', date: 'Hari ini, 08:00' },
                    { id: 'K2', type: 'kegiatan_eksternal', title: 'Sosialisasi Kawasan Tanpa Rokok', desc: 'Edukasi masyarakat di fasilitas umum.', location: 'Taman Suropati', lat: -6.1997, lng: 106.8326, status: 'Dijadwalkan', date: 'Besok, 09:00' },
                    { id: 'K3', type: 'kegiatan_eksternal', title: 'Penertiban PKL Monas', desc: 'Pembersihan kawasan ring 1 dari pedagang ilegal.', location: 'Kawasan Monas', lat: -6.1754, lng: 106.8272, status: 'Selesai', date: 'Kemarin, 15:00' },

                    // Kegiatan Internal
                    { id: 'I1', type: 'kegiatan_internal', title: 'Apel Pasukan', desc: 'Apel pagi gabungan regu patroli wilayah pusat.', location: 'Balai Kota', lat: -6.1820, lng: 106.8250, status: 'Selesai', date: 'Hari ini, 07:00' },
                    { id: 'I2', type: 'kegiatan_internal', title: 'Pelatihan Jasmani', desc: 'Latihan fisik rutin untuk anggota Satpol PP.', location: 'Gelora Bung Karno', lat: -6.2185, lng: 106.8020, status: 'Berlangsung', date: 'Hari ini, 15:00' },
                ],

                // Defines Hotspot areas based on report density
                hotspotData: [
                    { lat: -6.1880, lng: 106.8150, radius: 400, intensity: 0.8 }, // Tanah abang high density
                    { lat: -6.2043, lng: 106.8225, radius: 300, intensity: 0.4 }  // Sudirman medium density
                ],

                initPeta() {
                    // Muat laporan real (yang sudah selesai + punya lokasi penandaan)
                    this.loadLaporanFromStorage();

                    // Center roughly in Jakarta
                    this.map = L.map('mainMap', {
                        zoomControl: false
                    }).setView([-6.2000, 106.8166], 12);

                    L.control.zoom({
                        position: 'bottomright'
                    }).addTo(this.map);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        maxZoom: 19
                    }).addTo(this.map);

                    this.renderMap();
                    this.initCharts();
                },

                // Muat laporan yang sudah selesai + menandai titik lokasi kejadian
                loadLaporanFromStorage() {
                    let reports = [];
                    try {
                        reports = JSON.parse(localStorage.getItem('sipraja_reports') || '[]');
                    } catch (e) { reports = []; }

                    const laporanItems = reports
                        .filter(r => (r.statusIdx != null ? r.statusIdx : 0) >= 5 && r.lokasiSelesai && r.lokasiSelesai.lat && r.lokasiSelesai.lng)
                        .map((r, i) => ({
                            id: 'L-' + r.kode,
                            type: 'laporan',
                            title: r.kategori || r.kode,
                            desc: r.deskripsi || '-',
                            location: r.lokasi || 'Lokasi kejadian',
                            lat: parseFloat(r.lokasiSelesai.lat),
                            lng: parseFloat(r.lokasiSelesai.lng),
                            status: 'Selesai',
                            date: r.dibuat || '-'
                        }));

                    this.mapData = laporanItems.concat(this.mapData);
                },

                renderMap() {
                    // Clear existing markers and hotspots
                    this.markers.forEach(m => this.map.removeLayer(m));
                    this.hotspots.forEach(h => this.map.removeLayer(h));
                    this.markers = [];
                    this.hotspots = [];

                    // Render Hotspots (Red Circles) if Laporan filter is ON
                    if (this.filters.laporan) {
                        this.hotspotData.forEach(hs => {
                            let circle = L.circle([hs.lat, hs.lng], {
                                color: '#ef4444',
                                fillColor: '#ef4444',
                                fillOpacity: hs.intensity * 0.3, // opacity based on intensity
                                weight: 1,
                                radius: hs.radius,
                                className: 'animate-pulse' // Tailwind animation on svg path
                            }).addTo(this.map);
                            this.hotspots.push(circle);
                        });
                    }

                    // Render Markers
                    this.mapData.forEach(item => {
                        if (!this.filters[item.type]) return; // Skip if filtered out

                        let iconHtml = '';
                        if (item.type === 'laporan') iconHtml = 'campaign';
                        if (item.type === 'kegiatan_eksternal') iconHtml = 'shield';
                        if (item.type === 'kegiatan_internal') iconHtml = 'groups';

                        let colorClass = this.getColorClass(item.type, false);
                        let bgColorClass = this.getColorClass(item.type, true);
                        let statusColor = this.getStatusColor(item.status);
                        let typeFormatted = this.formatType(item.type);

                        let customIcon = L.divIcon({
                            className: 'custom-div-icon marker-' + item.type,
                            html: `<div class="marker-pin"></div><span class="material-symbols-outlined marker-icon">${iconHtml}</span>`,
                            iconSize: [30, 42],
                            iconAnchor: [15, 42],
                            popupAnchor: [0, -35]
                        });

                        let popupHtml = `
                            <div class="flex flex-col">
                                <div class="h-2 w-full ${bgColorClass}"></div>
                                <div class="p-4 pt-5 relative">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ${colorClass}">${typeFormatted}</span>
                                        <span class="text-[10px] font-semibold text-slate-500">${item.date}</span>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-800 mb-1 leading-tight">${item.title}</h3>
                                    <p class="text-xs text-slate-600 mb-3 line-clamp-2">${item.desc}</p>
                                    
                                    <div class="bg-slate-50 rounded-lg p-2 flex flex-col gap-1.5 mb-3 border border-slate-100">
                                        <div class="flex items-center gap-1.5 text-xs text-slate-700">
                                            <span class="material-symbols-outlined text-[14px] text-slate-400">location_on</span>
                                            <span class="font-medium line-clamp-1">${item.location}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-xs text-slate-700">
                                            <span class="material-symbols-outlined text-[14px] text-slate-400">info</span>
                                            Status: <span class="font-bold ${statusColor}">${item.status}</span>
                                        </div>
                                    </div>
                                    
                                    <button class="w-full py-2 bg-sidebar-active hover:bg-sidebar-hover text-white font-semibold rounded-lg text-xs transition-colors shadow-sm">
                                        Lihat Detail Lengkap
                                    </button>
                                </div>
                            </div>
                        `;

                        let marker = L.marker([item.lat, item.lng], { icon: customIcon })
                            .bindPopup(popupHtml, {
                                closeButton: true,
                                offset: L.point(0, -5)
                            })
                            .addTo(this.map);
                        
                        marker.on('click', () => {
                            this.map.setView([item.lat, item.lng], 14, { animate: true });
                        });

                        this.markers.push(marker);
                    });
                },

                // ===== Pencarian Lokasi (Geocoding via Nominatim OSM) =====
                async searchLocation() {
                    const q = (this.searchQuery || '').trim();
                    if (!q) return;
                    const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(q);
                    try {
                        const res = await fetch(url, { headers: { 'Accept-Language': 'id' } });
                        const data = await res.json();
                        if (!data || data.length === 0) {
                            alert('Lokasi tidak ditemukan. Coba alamat yang lebih spesifik.');
                            return;
                        }
                        const lat = parseFloat(data[0].lat);
                        const lng = parseFloat(data[0].lon);
                        this.map.setView([lat, lng], 15, { animate: true });
                    } catch (e) {
                        alert('Gagal mencari lokasi. Periksa koneksi internet.');
                    }
                },

                // Formatting Helpers
                formatType(type) {
                    if (type === 'laporan') return 'Laporan Warga';
                    if (type === 'kegiatan_eksternal') return 'Keg. Eksternal';
                    if (type === 'kegiatan_internal') return 'Keg. Internal';
                    return type;
                },

                getColorClass(type, isBg = false) {
                    if (type === 'laporan') return isBg ? 'bg-red-500' : 'bg-red-100 text-red-700';
                    if (type === 'kegiatan_eksternal') return isBg ? 'bg-blue-500' : 'bg-blue-100 text-blue-700';
                    if (type === 'kegiatan_internal') return isBg ? 'bg-emerald-500' : 'bg-emerald-100 text-emerald-700';
                    return isBg ? 'bg-slate-500' : 'bg-slate-100 text-slate-700';
                },

                getStatusColor(status) {
                    if (status === 'Selesai') return 'text-emerald-600';
                    if (status === 'Diproses' || status === 'Berlangsung') return 'text-blue-600';
                    if (status === 'Menunggu' || status === 'Dijadwalkan') return 'text-amber-600';
                    return 'text-slate-600';
                },

                // Init Charts
                initCharts() {
                    const ctxIncident = document.getElementById('incidentChart').getContext('2d');
                    new Chart(ctxIncident, {
                        type: 'bar',
                        data: {
                            labels: ['Noise', 'PKL', 'Permit', 'Traffic', 'Other'],
                            datasets: [{
                                label: 'Incidents',
                                data: [12, 19, 8, 15, 4],
                                backgroundColor: '#1e3a8a',
                                borderRadius: 4,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true, grid: { display: false } },
                                x: { grid: { display: false } }
                            }
                        }
                    });

                    const ctxStatus = document.getElementById('statusChart').getContext('2d');
                    new Chart(ctxStatus, {
                        type: 'doughnut',
                        data: {
                            labels: ['Critical', 'In Progress', 'Resolved'],
                            datasets: [{
                                data: [15, 35, 50],
                                backgroundColor: ['#ef4444', '#f59e0b', '#1e293b'],
                                borderWidth: 0,
                                cutout: '70%'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
                                    labels: { boxWidth: 10, font: { size: 10 } }
                                }
                            }
                        }
                    });

                    const ctxResponse = document.getElementById('responseChart').getContext('2d');
                    new Chart(ctxResponse, {
                        type: 'line',
                        data: {
                            labels: ['08:00', '10:00', '12:00', '14:00', '16:00'],
                            datasets: [{
                                label: 'Avg Time (Mins)',
                                data: [18, 15, 22, 12, 14],
                                borderColor: '#b45309',
                                backgroundColor: 'rgba(180, 83, 9, 0.1)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2,
                                pointBackgroundColor: '#b45309',
                                pointBorderColor: '#fff',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true, max: 25, grid: { borderDash: [2,2] } },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }
            }
        }
    </script>
@endsection

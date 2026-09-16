@extends('layouts.super_admin')

@section('title', 'Peta Persebaran')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <style>
        /* Custom map container to fit within the remaining viewport */
        .map-container {
            height: calc(100vh - 285px);
            min-height: 420px;
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
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-super-active/20 focus:border-super-active outline-none transition-all text-sm">
                </div>
                <button type="button" @click="searchLocation()" class="inline-flex items-center justify-center gap-2 bg-super-active hover:bg-super-hover text-white px-5 py-2 rounded-xl text-sm font-semibold shadow-sm transition-colors">
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
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Unit Aktif</span>
                    <span class="text-2xl font-headline font-bold text-slate-800">{{ $mapStatistics['active_units'] }}</span>
                </div>
                <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex flex-col">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Laporan Terbuka</span>
                    <span class="text-2xl font-headline font-bold text-red-600">{{ $mapStatistics['open_reports'] }}</span>
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
                        <h4 class="text-xs font-semibold text-slate-600 mb-3">Kategori Laporan</h4>
                        <div class="h-40 w-full relative">
                            <canvas id="incidentChart"></canvas>
                        </div>
                    </div>

                    <!-- Chart 2 -->
                    <div>
                        <h4 class="text-xs font-semibold text-slate-600 mb-3">Distribusi Status</h4>
                        <div class="h-40 w-full relative flex items-center justify-center">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>

                    <!-- Chart 3 -->
                    <div>
                        <h4 class="text-xs font-semibold text-slate-600 mb-3">Laporan 7 Hari Terakhir</h4>
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

                mapData: @json($mapPoints),

                // Defines Hotspot areas based on report density
                hotspotData: [],

                initPeta() {
                    this.map = L.map('mainMap', {
                        zoomControl: false,
                        scrollWheelZoom: false,
                        doubleClickZoom: true
                    });
                    KulonProgoMap.setup(this.map);

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
                    // Titik peta sudah disediakan controller dari database.
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
                                        <span class="text-[10px] font-semibold text-slate-500">${this.escapeHtml(item.date)}</span>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-800 mb-1 leading-tight">${this.escapeHtml(item.title)}</h3>
                                    <p class="text-xs text-slate-600 mb-3 line-clamp-2">${this.escapeHtml(item.desc || '-')}</p>
                                    
                                    <div class="bg-slate-50 rounded-lg p-2 flex flex-col gap-1.5 mb-3 border border-slate-100">
                                        <div class="flex items-center gap-1.5 text-xs text-slate-700">
                                            <span class="material-symbols-outlined text-[14px] text-slate-400">location_on</span>
                                            <span class="font-medium line-clamp-1">${this.escapeHtml(item.location)}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-xs text-slate-700">
                                            <span class="material-symbols-outlined text-[14px] text-slate-400">info</span>
                                            Status: <span class="font-bold ${statusColor}">${this.escapeHtml(item.status)}</span>
                                        </div>
                                    </div>
                                    
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
                            this.map.panTo([item.lat, item.lng], { animate: true });
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
                escapeHtml(value) {
                    const element = document.createElement('div');
                    element.textContent = value == null ? '' : String(value);
                    return element.innerHTML;
                },

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
                            labels: @json($categoryChart->pluck('label')),
                            datasets: [{
                                label: 'Laporan',
                                data: @json($categoryChart->pluck('total')),
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
                            labels: @json($statusChart->pluck('label')),
                            datasets: [{
                                data: @json($statusChart->pluck('total')),
                                backgroundColor: ['#64748b', '#3b82f6', '#8b5cf6', '#f59e0b', '#10b981'],
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
                            labels: @json($reportTrend->pluck('label')),
                            datasets: [{
                                label: 'Laporan',
                                data: @json($reportTrend->pluck('total')),
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
                                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { borderDash: [2,2] } },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }
            }
        }
    </script>
@endsection

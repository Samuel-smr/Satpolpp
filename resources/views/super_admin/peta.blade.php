@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <style>
        /* Custom map container to fit within the remaining viewport */
        .map-container {
            height: 100%;
            min-height: 440px;
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
        .marker-kegiatan_internal .marker-pin { background: #10b981; }
        .marker-kegiatan_internal .marker-icon { color: #10b981; }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

    <div id="peta-persebaran" x-data="petaManager()" x-init="initPeta()" x-cloak class="flex flex-col lg:flex-row gap-6 h-full">
        
        <!-- Left: Control Panel + Map -->
        <div class="flex-1 flex flex-col gap-4 min-w-0">
            <!-- Kartu Kontrol: Filter Kategori (terpisah dari peta) -->
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm">
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
                            <input type="checkbox" x-model="filters.kegiatan_internal" @change="renderMap()" class="w-4 h-4 text-emerald-500 rounded border-slate-300 focus:ring-emerald-500">
                            <span class="text-sm font-semibold text-slate-700 flex items-center gap-1">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Kegiatan Internal
                            </span>
                        </label>
                    </div>
                </div>
                <p class="px-4 py-3 text-xs text-slate-500">Penanda pada lokasi yang sama ditampilkan berdampingan. Klik setiap penanda untuk melihat laporan atau kegiatan yang berbeda.</p>
            </div>

            <!-- Kartu Peta (terpisah) -->
            <div class="flex-1 bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden flex flex-col relative">
                <div id="mainMap" class="w-full flex-1 map-container relative rounded-b-2xl"></div>
                <button type="button" @click="screenshotPeta()" :disabled="screenshotLoading"
                    title="Ambil screenshot peta pada tampilan saat ini"
                    class="absolute top-3 right-3 z-[1000] flex items-center gap-2 rounded-xl bg-blue-700 px-3.5 py-2.5 text-sm font-semibold text-white shadow-md transition-colors hover:bg-blue-800 disabled:cursor-wait disabled:opacity-70">
                    <span class="material-symbols-outlined text-[18px]" :class="screenshotLoading ? 'animate-spin' : ''" x-text="screenshotLoading ? 'progress_activity' : 'photo_camera'"></span>
                    <span x-text="screenshotLoading ? 'Memproses...' : 'Screenshot Peta'"></span>
                </button>
            </div>
        </div>

        <!-- Right: Real-time Metrics Sidebar -->
        <div class="w-full lg:w-80 flex flex-col gap-6 shrink-0">
            <!-- Stat Cards -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex flex-col">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pegawai e-Profil</span>
                    <span class="text-2xl font-headline font-bold text-slate-800">{{ $mapStatistics['active_officers'] }}</span>
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
                        <h4 class="text-xs font-semibold text-slate-600 mb-3">Kategori Persebaran</h4>
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

                    <!-- Chart 4 -->
                    <div>
                        <h4 class="text-xs font-semibold text-slate-600 mb-3">Distribusi per Kecamatan</h4>
                        <div class="h-56 w-full relative">
                            <canvas id="districtChart"></canvas>
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
                screenshotLoading: false,

                filters: {
                    laporan: true,
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
                        crossOrigin: true,
                        maxZoom: 19
                    }).addTo(this.map);

                    this.map.getContainer().addEventListener('wheel', (e) => {
                        if (!e.ctrlKey) return;
                        e.preventDefault();
                        const dir = e.deltaY > 0 ? -1 : 1;
                        const z = this.map.getZoom() + dir;
                        this.map.setZoom(Math.min(19, Math.max(6, z)));
                    }, { passive: false });

                    this.renderMap();
                    this.initCharts();
                },

                // Mengambil screenshot peta sesuai tampilan/lokasi yang sedang aktif
                async screenshotPeta() {
                    if (this.screenshotLoading || !this.map) return;

                    if (typeof html2canvas === 'undefined') {
                        alert('Fitur screenshot belum siap. Muat ulang halaman dan coba lagi.');
                        return;
                    }

                    this.screenshotLoading = true;

                    try {
                        const container = this.map.getContainer();
                        await new Promise(resolve => setTimeout(resolve, 120));

                        const scale = Math.max(window.devicePixelRatio || 1, 2);
                        const canvas = await html2canvas(container, {
                            useCORS: true,
                            allowTaint: false,
                            backgroundColor: '#ffffff',
                            scale,
                            logging: false,
                        });

                        const center = this.map.getCenter();
                        const stamp = new Date().toISOString().slice(0, 19).replace(/[:T]/g, '-');
                        const link = document.createElement('a');
                        link.href = canvas.toDataURL('image/png');
                        link.download = `Peta_${center.lat.toFixed(4)}_${center.lng.toFixed(4)}_${stamp}.png`;
                        document.body.appendChild(link);
                        link.click();
                        link.remove();
                    } catch (error) {
                        console.error('Gagal membuat screenshot peta:', error);
                        alert('Gagal membuat screenshot peta. Silakan coba lagi.');
                    } finally {
                        this.screenshotLoading = false;
                    }
                },

                // Muat laporan yang sudah selesai + menandai titik lokasi kejadian
                loadLaporanFromStorage() {
                    // Titik peta sudah disediakan controller dari database.
                },

                visibleMarkers() {
                    const markers = this.mapData
                        .filter(item => this.filters[item.type])
                        .map(item => ({ item, offsetX: 0, offsetY: 0 }));
                    const locations = new Map();

                    markers.forEach(marker => {
                        const key = `${Number(marker.item.lat).toFixed(7)},${Number(marker.item.lng).toFixed(7)}`;
                        if (!locations.has(key)) locations.set(key, []);
                        locations.get(key).push(marker);
                    });

                    locations.forEach(group => {
                        if (group.length < 2) return;
                        const radius = Math.max(28, 44 / (2 * Math.sin(Math.PI / group.length)));
                        group.forEach((marker, index) => {
                            const angle = Math.PI + (2 * Math.PI * index / group.length);
                            marker.offsetX = Math.round(radius * Math.cos(angle));
                            marker.offsetY = Math.round(radius * Math.sin(angle));
                        });
                    });

                    return markers;
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
                    this.visibleMarkers().forEach(({ item, offsetX, offsetY }) => {
                        let iconHtml = '';
                        if (item.type === 'laporan') iconHtml = 'campaign';
                        if (item.type === 'kegiatan_internal') iconHtml = 'groups';

                        let colorClass = this.getColorClass(item.type, false);
                        let bgColorClass = this.getColorClass(item.type, true);
                        let statusColor = this.getStatusColor(item.status);
                        let typeFormatted = this.formatType(item.type);

                        let customIcon = L.divIcon({
                            className: 'custom-div-icon marker-' + item.type,
                            html: `<div class="marker-pin"></div><span class="material-symbols-outlined marker-icon">${iconHtml}</span>`,
                            iconSize: [30, 42],
                            iconAnchor: [15 - offsetX, 42 - offsetY],
                            popupAnchor: [offsetX, offsetY - 35]
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

                        let marker = L.marker([item.lat, item.lng], { icon: customIcon, title: `${typeFormatted}: ${item.title}` })
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

                // Formatting Helpers
                escapeHtml(value) {
                    const element = document.createElement('div');
                    element.textContent = value == null ? '' : String(value);
                    return element.innerHTML;
                },

                formatType(type) {
                    if (type === 'laporan') return 'Laporan/Aduan';
                    if (type === 'kegiatan_internal') return 'Kegiatan Internal';
                    return type;
                },

                getColorClass(type, isBg = false) {
                    if (type === 'laporan') return isBg ? 'bg-red-500' : 'bg-red-100 text-red-700';
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
                    const kategoriMap = {
                        'laporan': { label: 'Laporan/Aduan', color: '#ef4444' },
                        'kegiatan_internal': { label: 'Kegiatan Internal', color: '#10b981' },
                    };
                    const kategoriCounts = {};
                    this.mapData.forEach(item => {
                        const key = kategoriMap[item.type] ? item.type : 'laporan';
                        kategoriCounts[key] = (kategoriCounts[key] || 0) + 1;
                    });
                    const kategoriLabels = Object.keys(kategoriMap);
                    const kategoriData = kategoriLabels.map(k => kategoriCounts[k] || 0);

                    const ctxIncident = document.getElementById('incidentChart').getContext('2d');
                    new Chart(ctxIncident, {
                        type: 'bar',
                        data: {
                            labels: kategoriLabels.map(k => kategoriMap[k].label),
                            datasets: [{
                                label: 'Jumlah',
                                data: kategoriData,
                                backgroundColor: kategoriLabels.map(k => kategoriMap[k].color),
                                borderRadius: 4,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0, stepSize: 5 }, grid: { display: false } },
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
                                y: { beginAtZero: true, ticks: { precision: 0, stepSize: 5 }, grid: { borderDash: [2,2] } },
                                x: { grid: { display: false } }
                            }
                        }
                    });

                    const stats = this.districts.map((d, i) => ({ label: d, total: this.districtCounts()[i] })).filter(s => s.total > 0);

                    const ctxDistrict = document.getElementById('districtChart').getContext('2d');
                    new Chart(ctxDistrict, {
                        type: 'bar',
                        data: {
                            labels: stats.map(s => s.label),
                            datasets: [{
                                label: 'Jumlah',
                                data: stats.map(s => s.total),
                                backgroundColor: 'rgba(45, 92, 138, 0.85)',
                                borderRadius: 4,
                                maxBarThickness: 18,
                                categoryPercentage: 0.6,
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { beginAtZero: true, ticks: { precision: 0, stepSize: 5 }, grid: { display: false } },
                                y: { grid: { display: false } }
                            }
                        }
                    });
                },
                districts: @json($districts),
                districtCenters: @json($districtCenters),
                districtCounts() {
                    const counts = this.districts.map(() => 0);
                    this.mapData.forEach(item => {
                        if (item.lat == null || item.lng == null) return;
                        let bestIdx = 0;
                        let bestDist = Infinity;
                        this.districtCenters.forEach((c, idx) => {
                            const d = Math.pow(item.lat - c.lat, 2) + Math.pow(item.lng - c.lng, 2);
                            if (d < bestDist) { bestDist = d; bestIdx = idx; }
                        });
                        counts[bestIdx]++;
                    });
                    return counts;
                }
            }
        }
    </script>

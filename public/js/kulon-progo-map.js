(function () {
    const kulonProgoBoundary = [
        [-7.7057399, 110.2704349], [-7.7087351, 110.2492294], [-7.7173465, 110.2315454],
        [-7.7300246, 110.2222451], [-7.7525142, 110.2180684], [-7.7647536, 110.2203373],
        [-7.782769, 110.2253252], [-7.7980415, 110.2360508], [-7.8150045, 110.2334361],
        [-7.8350515, 110.2220598], [-7.8455266, 110.2327473], [-7.8665172, 110.2571524],
        [-7.9004902, 110.2743361], [-7.9279223, 110.2626135], [-7.9385879, 110.2432574],
        [-7.9638732, 110.226196], [-7.9838971, 110.2053613], [-7.9502888, 110.1428203],
        [-7.9190192, 110.0767542], [-7.9016498, 110.0299277], [-7.8905878, 110.0139423],
        [-7.8963065, 110.0315572], [-7.8743629, 110.0430538], [-7.8546085, 110.0472428],
        [-7.8413065, 110.0593992], [-7.8228084, 110.0604985], [-7.8066512, 110.0807909],
        [-7.7893026, 110.0901772], [-7.7787586, 110.1061815], [-7.7593437, 110.1184006],
        [-7.7405133, 110.1323033], [-7.7160111, 110.1325245], [-7.6953378, 110.1352627],
        [-7.6774864, 110.1268833], [-7.6677474, 110.1220234], [-7.6543064, 110.136504],
        [-7.6497368, 110.1445602], [-7.6469877, 110.1628378], [-7.6478134, 110.195564],
        [-7.6501312, 110.2221212], [-7.6468033, 110.2485275], [-7.6527052, 110.2637711],
        [-7.6730518, 110.2659454], [-7.6874833, 110.2613525], [-7.7037749, 110.2708893],
        [-7.7057399, 110.2704349]
    ];

    window.KulonProgoMap = {
        center: [-7.75, 110.16],
        defaultZoom: 11,
        boundarySource: 'https://gist.githubusercontent.com/itnirwana/475e123fe5e4ad9aa5287070b6760124/raw/diy_yogyakarta.json',

        addBoundary(map) {
            const boundaryStyle = {
                color: '#dc2626',
                weight: 3,
                opacity: 0.95,
                fillColor: '#ef4444',
                fillOpacity: 0.08,
                interactive: false
            };

            const fallbackBoundary = () => L.polygon(kulonProgoBoundary, boundaryStyle).addTo(map);

            return fetch(this.boundarySource)
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Batas wilayah tidak dapat dimuat.');
                    }

                    return response.json();
                })
                .then((geoJson) => {
                    const kulonProgoFeature = geoJson.features.find((feature) =>
                        feature.properties?.name === 'KULON PROGO'
                    );

                    if (!kulonProgoFeature) {
                        throw new Error('Data batas Kulon Progo tidak ditemukan.');
                    }

                    return L.geoJSON(kulonProgoFeature, { style: boundaryStyle }).addTo(map);
                })
                .catch(() => fallbackBoundary());
        },

        setup(map, zoom = this.defaultZoom) {
            map.setView(this.center, zoom);
            this.addBoundary(map);
            return map;
        },

        createLocationPicker({
            map,
            latitudeInput,
            longitudeInput,
            addressInput = null,
            searchInput = null,
            searchButton = null,
            currentLocationButton = null,
            statusElement = null,
            zoom = 16
        }) {
            let marker = null;

            const setStatus = (message = '', type = 'info') => {
                if (!statusElement) {
                    return;
                }

                statusElement.textContent = message;
                statusElement.classList.toggle('text-red-600', type === 'error');
                statusElement.classList.toggle('text-slate-500', type !== 'error');
            };

            const setLoading = (button, isLoading) => {
                if (!button) {
                    return;
                }

                button.disabled = isLoading;
                button.classList.toggle('opacity-60', isLoading);
                button.classList.toggle('cursor-wait', isLoading);
            };

            const selectLocation = (latitude, longitude, options = {}) => {
                const moveMap = options.moveMap ?? true;

                latitudeInput.value = Number(latitude).toFixed(7);
                longitudeInput.value = Number(longitude).toFixed(7);

                if (marker) {
                    marker.setLatLng([latitude, longitude]);
                } else {
                    marker = L.marker([latitude, longitude]).addTo(map);
                }

                if (moveMap) {
                    map.setView([latitude, longitude], zoom);
                }

                if (addressInput && options.address) {
                    addressInput.value = options.address;
                }
            };

            const reverseGeocode = async (latitude, longitude) => {
                const params = new URLSearchParams({
                    format: 'jsonv2',
                    lat: latitude,
                    lon: longitude,
                    zoom: '18',
                    addressdetails: '1'
                });
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?${params}`, {
                    headers: { 'Accept-Language': 'id' }
                });

                if (!response.ok) {
                    throw new Error('Alamat lokasi tidak dapat dimuat.');
                }

                return response.json();
            };

            const searchLocation = async () => {
                const query = searchInput?.value.trim();

                if (!query) {
                    setStatus('Masukkan nama tempat atau alamat yang ingin dicari.', 'error');
                    searchInput?.focus();
                    return;
                }

                setLoading(searchButton, true);
                setStatus('Mencari lokasi...');

                try {
                    const params = new URLSearchParams({
                        format: 'jsonv2',
                        q: query,
                        limit: '1',
                        countrycodes: 'id',
                        viewbox: '110.01,-7.64,110.28,-7.99',
                        bounded: '1'
                    });
                    const response = await fetch(`https://nominatim.openstreetmap.org/search?${params}`, {
                        headers: { 'Accept-Language': 'id' }
                    });

                    if (!response.ok) {
                        throw new Error('Pencarian lokasi tidak dapat digunakan.');
                    }

                    const results = await response.json();
                    const result = results[0];

                    if (!result) {
                        setStatus('Lokasi tidak ditemukan di wilayah Kulon Progo.', 'error');
                        return;
                    }

                    selectLocation(Number.parseFloat(result.lat), Number.parseFloat(result.lon), {
                        address: result.display_name
                    });
                    setStatus('Lokasi ditemukan dan koordinat sudah diperbarui.');
                } catch (error) {
                    setStatus('Gagal mencari lokasi. Periksa koneksi internet lalu coba lagi.', 'error');
                } finally {
                    setLoading(searchButton, false);
                }
            };

            searchButton?.addEventListener('click', searchLocation);
            searchInput?.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    searchLocation();
                }
            });

            currentLocationButton?.addEventListener('click', () => {
                if (!navigator.geolocation) {
                    setStatus('Browser ini tidak mendukung akses lokasi saat ini.', 'error');
                    return;
                }

                setLoading(currentLocationButton, true);
                setStatus('Mengambil lokasi saat ini...');
                navigator.geolocation.getCurrentPosition(async (position) => {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    selectLocation(latitude, longitude);

                    try {
                        const result = await reverseGeocode(latitude, longitude);

                        if (addressInput && result.display_name) {
                            addressInput.value = result.display_name;
                        }
                    } catch (error) {
                        // Koordinat tetap dapat digunakan saat layanan alamat tidak tersedia.
                    } finally {
                        setLoading(currentLocationButton, false);
                        setStatus('Lokasi saat ini sudah digunakan.');
                    }
                }, (error) => {
                    setLoading(currentLocationButton, false);
                    const denied = error.code === error.PERMISSION_DENIED;
                    setStatus(denied
                        ? 'Izin lokasi ditolak. Aktifkan izin lokasi pada browser.'
                        : 'Lokasi saat ini tidak dapat diambil. Coba lagi.', 'error');
                }, {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 30000
                });
            });

            map.on('click', (event) => {
                selectLocation(event.latlng.lat, event.latlng.lng, { moveMap: false });
                setStatus('Titik lokasi dan koordinat sudah diperbarui.');
            });

            [latitudeInput, longitudeInput].forEach((input) => input?.addEventListener('change', () => {
                const latitude = Number.parseFloat(latitudeInput.value);
                const longitude = Number.parseFloat(longitudeInput.value);

                if (Number.isFinite(latitude) && Number.isFinite(longitude)) {
                    selectLocation(latitude, longitude);
                }
            }));

            const initialLatitude = Number.parseFloat(latitudeInput.value);
            const initialLongitude = Number.parseFloat(longitudeInput.value);

            if (Number.isFinite(initialLatitude) && Number.isFinite(initialLongitude)) {
                selectLocation(initialLatitude, initialLongitude);
            }

            return { selectLocation, searchLocation };
        }
    };
})();

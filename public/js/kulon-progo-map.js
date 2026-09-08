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
        }
    };
})();

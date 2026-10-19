import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import vm from 'node:vm';

const page = readFileSync(new URL('../resources/views/super_admin/peta.blade.php', import.meta.url), 'utf8');

function mapManager(points) {
    const charts = new Map();
    const sandbox = vm.createContext({
        document: { getElementById: id => ({ getContext: () => id }) },
        Chart: class { constructor(id, options) { charts.set(id, options); } },
        L: {
            divIcon: options => options,
            point: (x, y) => [x, y],
            marker: (coordinates, options) => ({
                coordinates,
                options,
                bindPopup(html) { this.popup = html; return this; },
                addTo() { return this; },
                on() { return this; },
            }),
        },
    });
    const start = page.indexOf('        function petaManager()');
    const end = page.indexOf('</script>', start);
    assert.ok(start >= 0 && end > start);
    vm.runInContext(page.slice(start, end).replace(/@json\([^\n]*\)/g, '[]'), sandbox);
    const manager = sandbox.petaManager();
    manager.mapData = points;
    manager.map = { removeLayer() {} };
    manager.escapeHtml = value => String(value ?? '');
    return { manager, charts };
}

function point(type) {
    return { type, lat: -7.85, lng: 110.16, title: 'Kegiatan pada peta', date: '19/10/2026', status: 'Selesai' };
}

test('distribution metrics count external activities separately from reports and internal activities', () => {
    const { manager, charts } = mapManager([
        point('laporan'), point('kegiatan_eksternal'), point('kegiatan_eksternal'), point('kegiatan_internal'),
    ]);

    manager.initCharts();

    const incident = charts.get('incidentChart');
    assert.deepEqual(Array.from(incident.data.labels), ['Laporan/Aduan', 'Kegiatan Eksternal', 'Kegiatan Internal']);
    assert.deepEqual(Array.from(incident.data.datasets[0].data), [1, 2, 1]);
});

test('external activity markers render in blue and respond to the external category filter', () => {
    const { manager } = mapManager([point('laporan'), point('kegiatan_eksternal'), point('kegiatan_internal')]);

    manager.renderMap();

    assert.equal(manager.markers.length, 3);
    const externalMarker = manager.markers.find(marker => marker.options.icon.className.includes('marker-kegiatan_eksternal'));
    assert.ok(externalMarker);
    assert.ok(externalMarker.options.icon.html.includes('shield'));
    assert.equal(manager.getColorClass('kegiatan_eksternal', true), 'bg-blue-500');

    manager.filters.kegiatan_eksternal = false;
    manager.renderMap();

    assert.equal(manager.markers.length, 2);
    assert.ok(manager.markers.every(marker => !marker.options.icon.className.includes('marker-kegiatan_eksternal')));
});

test('a report and its external activity at the same location have separate clickable markers', () => {
    const report = { ...point('laporan'), title: 'SPJ-TEST-001' };
    const activity = { ...point('kegiatan_eksternal'), title: 'Penanganan SPJ-TEST-001' };
    const { manager } = mapManager([report, activity]);

    manager.renderMap();

    assert.equal(manager.markers.length, 2);
    const [reportMarker, activityMarker] = manager.markers;
    assert.notDeepEqual(Array.from(reportMarker.options.icon.iconAnchor), Array.from(activityMarker.options.icon.iconAnchor));
    assert.notDeepEqual(Array.from(reportMarker.options.icon.popupAnchor), Array.from(activityMarker.options.icon.popupAnchor));
    assert.ok(reportMarker.popup.includes('SPJ-TEST-001'));
    assert.ok(reportMarker.popup.includes('Laporan/Aduan'));
    assert.ok(activityMarker.popup.includes('Penanganan SPJ-TEST-001'));
    assert.ok(activityMarker.popup.includes('Kegiatan Eksternal'));
    assert.deepEqual(Array.from(reportMarker.coordinates), [-7.85, 110.16]);
    assert.deepEqual(Array.from(activityMarker.coordinates), [-7.85, 110.16]);
    assert.equal(report.lat, -7.85);
    assert.equal(activity.lng, 110.16);

    manager.filters.laporan = false;
    manager.renderMap();

    assert.equal(manager.markers.length, 1);
    assert.ok(manager.markers[0].popup.includes('Kegiatan Eksternal'));
    assert.deepEqual(Array.from(manager.markers[0].options.icon.iconAnchor), [15, 42]);
});

test('multiple records at one location each have a distinct marker while other locations keep their original anchor', () => {
    const { manager } = mapManager([
        ...Array.from({ length: 8 }, () => point('kegiatan_eksternal')),
        { ...point('laporan'), lat: -7.9 },
    ]);

    manager.renderMap();

    const anchors = manager.markers.slice(0, 8).map(marker => marker.options.icon.iconAnchor.join(','));
    assert.equal(new Set(anchors).size, 8);
    assert.deepEqual(Array.from(manager.markers[8].options.icon.iconAnchor), [15, 42]);

    manager.initCharts();
    assert.equal(manager.mapData.length, 9);
});

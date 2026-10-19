import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import vm from 'node:vm';
import { crc32 } from 'node:zlib';

const source = readFileSync(new URL('../public/js/activity-word-export.js', import.meta.url), 'utf8');
const page = readFileSync(new URL('../resources/views/admin/kegiatan.blade.php', import.meta.url), 'utf8');
const tinyImage = {
    data: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
    width: 1,
    height: 1,
};

function exporter() {
    const sandbox = vm.createContext({ window: {}, TextEncoder, Uint8Array, DataView, Blob, atob });
    vm.runInContext(source, sandbox);
    return sandbox.window.SiprajaWordExport;
}

function activity(attributes = {}) {
    return {
        nama: 'Patroli Ketertiban', tanggal: '2026-10-19', waktu: '09:30', lokasi: 'Wates',
        personel: ['AGUNG SUKMAWANTORO'], deskripsi: 'Melakukan patroli.', hasil: 'Situasi tertib.',
        temuan: [{ jenis: 'Orang', jumlah: 2, satuan: 'Orang' }], nama_template: 'Patroli', dokumentasi: [],
        ...attributes,
    };
}

async function entries(blob) {
    assert.equal(blob.type, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    const bytes = Buffer.from(await blob.arrayBuffer());
    const end = bytes.length - 22;
    assert.equal(bytes.readUInt32LE(end), 0x06054b50, 'ZIP end record is present');
    const count = bytes.readUInt16LE(end + 10);
    const start = bytes.readUInt32LE(end + 16);
    assert.equal(start + bytes.readUInt32LE(end + 12), end);
    const files = new Map();
    let offset = start;
    for (let index = 0; index < count; index++) {
        assert.equal(bytes.readUInt32LE(offset), 0x02014b50);
        const length = bytes.readUInt32LE(offset + 24);
        const nameLength = bytes.readUInt16LE(offset + 28);
        const name = bytes.subarray(offset + 46, offset + 46 + nameLength).toString('utf8');
        const local = bytes.readUInt32LE(offset + 42);
        assert.equal(bytes.readUInt32LE(local), 0x04034b50);
        assert.equal(bytes.readUInt16LE(local + 8), 0, 'ZIP uses the standard stored method');
        assert.equal(bytes.readUInt32LE(local + 22), length);
        const dataStart = local + 30 + bytes.readUInt16LE(local + 26) + bytes.readUInt16LE(local + 28);
        const data = bytes.subarray(dataStart, dataStart + length);
        assert.equal(crc32(data), bytes.readUInt32LE(offset + 16), 'ZIP entry CRC matches its content');
        assert.equal(crc32(data), bytes.readUInt32LE(local + 14));
        files.set(name, data);
        offset += 46 + nameLength + bytes.readUInt16LE(offset + 30) + bytes.readUInt16LE(offset + 32);
    }
    assert.equal(offset, end);
    return files;
}

test('Word export produces a genuine DOCX with editable report text, names, period and embedded images', async () => {
    const files = await entries(exporter().createDocument({
        activities: [activity()], periodLabel: '19 OKTOBER 2026', exportDate: '19 Oktober 2026',
        logos: [tinyImage], photos: [[tinyImage]],
    }));
    const document = files.get('word/document.xml').toString();
    assert.match(files.get('_rels/.rels').toString(), /Target="word\/document.xml"/);
    assert.match(files.get('[Content_Types].xml').toString(), /wordprocessingml.document.main\+xml/);
    assert.match(files.get('word/styles.xml').toString(), /Arial/);
    for (const text of ['LAPORAN KEGIATAN', 'AGUNG SUKMAWANTORO', '19 OKTOBER 2026', 'Patroli Ketertiban', 'Situasi tertib.', 'Orang: 2 Orang', 'Patroli: 1']) assert.ok(document.includes(text), text);
    assert.ok(!document.includes('Polisi Pamong Praja Mahir'));
    assert.match(document, /w:orient="landscape"/);
    assert.match(document, /<w:tblHeader\/>/);
    const relationships = files.get('word/_rels/document.xml.rels').toString();
    const media = [...files.keys()].filter(name => name.startsWith('word/media/'));
    assert.equal(media.length, 2);
    for (const name of media) {
        assert.match(relationships, new RegExp(name.replace('word/', '')));
        assert.equal(files.get(name).toString('base64'), tinyImage.data.split(',')[1]);
    }
    assert.equal((document.match(/<w:drawing>/g) || []).length, 2);
});

test('Word text remains literal XML-safe text, including malicious input and Unicode names', async () => {
    const unsafe = '<w:hyperlink r:id="evil">&\u0001 Łódź café';
    const files = await entries(exporter().createDocument({
        activities: [activity({ nama: unsafe, personel: [unsafe], nama_template: '__proto__', deskripsi: 'Baris satu\nBaris dua' })],
        periodLabel: unsafe, exportDate: unsafe,
    }));
    const document = files.get('word/document.xml').toString();
    assert.ok(!document.includes('<w:hyperlink'));
    assert.ok(!document.includes('\u0001'));
    assert.ok(document.includes('&lt;w:hyperlink r:id=&quot;evil&quot;&gt;&amp; Łódź café'));
    assert.ok(document.includes('__proto__: 1'));
    assert.ok(document.includes('<w:br/>'));
    assert.ok(!files.get('word/_rels/document.xml.rels').toString().includes('TargetMode="External"'));
});

test('Word export includes all filtered records and tolerates unavailable or unsupported photos', async () => {
    const activities = Array.from({ length: 17 }, (_, index) => activity({ nama: `Kegiatan nomor ${index + 1}` }));
    const files = await entries(exporter().createDocument({
        activities, periodLabel: 'TAHUN 2026', exportDate: '19 Oktober 2026',
        logos: [null], photos: [[null, { ...tinyImage, data: 'data:text/html;base64,PHNjcmlwdD4=' }]],
    }));
    const document = files.get('word/document.xml').toString();
    assert.ok(document.includes('Kegiatan nomor 17'));
    assert.ok(document.includes('Patroli: 17'));
    assert.equal([...files.keys()].filter(name => name.startsWith('word/media/')).length, 0);
});

test('Word export refuses an empty report', () => {
    assert.throws(() => exporter().createDocument({ activities: [] }), /Tidak ada kegiatan/);
});

function handler({ hasData = true, fail = false } = {}) {
    const downloads = [];
    const messages = [];
    const revoked = [];
    let callback;
    const wordButton = { disabled: false, addEventListener(type, listener) { callback = listener; } };
    const sandbox = vm.createContext({
        activities: [activity()], wordButton,
        ensureData: () => hasData,
        logoData: async () => null,
        imageData: async () => null,
        period: () => ['2026-10-01', '2026-10-19'],
        suffix: () => '2026-10-01_sd_2026-10-19',
        dateLabel: date => date,
        setTimeout: callback => callback(),
        window: {
            SiprajaWordExport: fail ? { createDocument() { throw new Error('Ekspor gagal.'); } } : exporter(),
            tampilToast: (...args) => messages.push(args),
        },
        URL: { createObjectURL(blob) { downloads.push({ blob }); return 'blob:word-export'; }, revokeObjectURL: url => revoked.push(url) },
        document: {
            body: { appendChild() {} },
            createElement() { return { click() { downloads.at(-1).filename = this.download; }, remove() {} }; },
        },
    });
    const start = page.indexOf("            wordButton.addEventListener('click'");
    const end = page.indexOf("            pdfButton.addEventListener('click'", start);
    assert.ok(start >= 0 && end > start);
    vm.runInContext(page.slice(start, end), sandbox);
    return { run: () => callback(), wordButton, downloads, messages, revoked };
}

test('Word button downloads DOCX using the current filter dates and restores the button', async () => {
    const exportHandler = handler();
    await exportHandler.run();
    assert.equal(exportHandler.wordButton.disabled, false);
    assert.equal(exportHandler.downloads[0].filename, 'Laporan_Kegiatan_2026-10-01_sd_2026-10-19.docx');
    const files = await entries(exportHandler.downloads[0].blob);
    assert.ok(files.get('word/document.xml').toString().includes('2026-10-01-2026-10-19'));
    assert.deepEqual(exportHandler.revoked, ['blob:word-export']);
    assert.deepEqual(exportHandler.messages, []);
});

test('Word button handles failures and empty filters without leaving the button disabled', async () => {
    const failed = handler({ fail: true });
    await failed.run();
    assert.equal(failed.wordButton.disabled, false);
    assert.deepEqual(failed.messages, [['Ekspor gagal.', 'error']]);
    assert.deepEqual(failed.downloads, []);
    const empty = handler({ hasData: false });
    await empty.run();
    assert.equal(empty.wordButton.disabled, false);
    assert.deepEqual(empty.downloads, []);
});

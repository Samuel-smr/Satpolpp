(() => {
    'use strict';

    const xmlHeader = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    const relationshipsNamespace = 'http://schemas.openxmlformats.org/package/2006/relationships';
    const officeRelationships = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    const mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    const encoder = new TextEncoder();
    const crcTable = Array.from({ length: 256 }, (_, index) => {
        let value = index;
        for (let bit = 0; bit < 8; bit++) value = (value & 1) ? 0xedb88320 ^ (value >>> 1) : value >>> 1;
        return value >>> 0;
    });

    const escapeXml = value => String(value ?? '')
        .replace(/[\u0000-\u0008\u000B\u000C\u000E-\u001F\uFFFE\uFFFF]/g, '')
        .replace(/[&<>"']/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&apos;' })[character]);

    const paragraph = (text, { bold = false, align = 'left', size = 18, color = '1E293B', after = 80 } = {}) =>
        `<w:p><w:pPr><w:spacing w:after="${after}"/><w:jc w:val="${align}"/></w:pPr><w:r><w:rPr>${bold ? '<w:b/>' : ''}<w:color w:val="${color}"/><w:sz w:val="${size}"/></w:rPr>${String(text ?? '-').split(/\r\n|\r|\n/).map((line, index) => `${index ? '<w:br/>' : ''}<w:t xml:space="preserve">${escapeXml(line)}</w:t>`).join('')}</w:r></w:p>`;

    const cell = (content, width, header = false) =>
        `<w:tc><w:tcPr><w:tcW w:w="${width}" w:type="dxa"/>${header ? '<w:shd w:fill="2B4F7C"/>' : ''}<w:vAlign w:val="top"/></w:tcPr>${content || '<w:p/>'}</w:tc>`;

    const table = (widths, rows, bordered = true) => {
        const border = bordered ? 'single' : 'nil';
        return `<w:tbl><w:tblPr><w:tblW w:w="${widths.reduce((sum, width) => sum + width, 0)}" w:type="dxa"/><w:tblBorders>${['top', 'left', 'bottom', 'right', 'insideH', 'insideV'].map(side => `<w:${side} w:val="${border}" w:sz="4" w:color="CBD5E1"/>`).join('')}</w:tblBorders><w:tblLayout w:type="fixed"/><w:tblCellMar><w:top w:w="100" w:type="dxa"/><w:left w:w="100" w:type="dxa"/><w:bottom w:w="100" w:type="dxa"/><w:right w:w="100" w:type="dxa"/></w:tblCellMar></w:tblPr><w:tblGrid>${widths.map(width => `<w:gridCol w:w="${width}"/>`).join('')}</w:tblGrid>${rows.join('')}</w:tbl>`;
    };

    const zip = files => {
        if (files.length > 65535) throw new Error('Jumlah lampiran terlalu banyak untuk ekspor Word.');
        const parts = [];
        const directory = [];
        let offset = 0;
        let directorySize = 0;

        files.forEach(([path, content]) => {
            const name = encoder.encode(path);
            const data = typeof content === 'string' ? encoder.encode(content) : content;
            if (offset + data.length + name.length + 30 > 0xffffffff) throw new Error('Ukuran ekspor Word terlalu besar. Persempit filter kegiatan.');
            let crc = 0xffffffff;
            for (const byte of data) crc = crcTable[(crc ^ byte) & 255] ^ (crc >>> 8);
            crc = (crc ^ 0xffffffff) >>> 0;

            const local = new Uint8Array(30 + name.length);
            const localView = new DataView(local.buffer);
            localView.setUint32(0, 0x04034b50, true);
            localView.setUint16(4, 20, true);
            localView.setUint16(6, 0x0800, true);
            localView.setUint16(12, 0x0021, true);
            localView.setUint32(14, crc, true);
            localView.setUint32(18, data.length, true);
            localView.setUint32(22, data.length, true);
            localView.setUint16(26, name.length, true);
            local.set(name, 30);
            parts.push(local, data);

            const central = new Uint8Array(46 + name.length);
            const centralView = new DataView(central.buffer);
            centralView.setUint32(0, 0x02014b50, true);
            centralView.setUint16(4, 20, true);
            centralView.setUint16(6, 20, true);
            centralView.setUint16(8, 0x0800, true);
            centralView.setUint16(14, 0x0021, true);
            centralView.setUint32(16, crc, true);
            centralView.setUint32(20, data.length, true);
            centralView.setUint32(24, data.length, true);
            centralView.setUint16(28, name.length, true);
            centralView.setUint32(42, offset, true);
            central.set(name, 46);
            directory.push(central);
            directorySize += central.length;
            offset += local.length + data.length;
        });

        const end = new Uint8Array(22);
        const endView = new DataView(end.buffer);
        endView.setUint32(0, 0x06054b50, true);
        endView.setUint16(8, files.length, true);
        endView.setUint16(10, files.length, true);
        endView.setUint32(12, directorySize, true);
        endView.setUint32(16, offset, true);
        return new Blob([...parts, ...directory, end], { type: mimeType });
    };

    const createDocument = ({ activities, periodLabel, exportDate, logos = [], photos = [] }) => {
        if (!activities.length) throw new Error('Tidak ada kegiatan yang sesuai dengan filter untuk diekspor.');
        const files = [];
        const relationships = [];
        let imageId = 0;

        const picture = (image, maxWidth, maxHeight) => {
            if (!image || !image.width || !image.height) return '';
            const match = /^data:image\/(png|jpeg);base64,([A-Za-z0-9+/=]+)$/.exec(image.data);
            if (!match) return '';
            imageId++;
            const extension = match[1] === 'jpeg' ? 'jpg' : 'png';
            const path = `media/image${imageId}.${extension}`;
            files.push([`word/${path}`, Uint8Array.from(atob(match[2]), character => character.charCodeAt(0))]);
            relationships.push(`<Relationship Id="image${imageId}" Type="${officeRelationships}/image" Target="${path}"/>`);
            const scale = Math.min(maxWidth / image.width, maxHeight / image.height);
            const width = Math.round(image.width * scale * 36000);
            const height = Math.round(image.height * scale * 36000);
            return `<w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0"><wp:extent cx="${width}" cy="${height}"/><wp:docPr id="${imageId}" name="Dokumentasi ${imageId}"/><wp:cNvGraphicFramePr><a:graphicFrameLocks noChangeAspect="1"/></wp:cNvGraphicFramePr><a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:pic><pic:nvPicPr><pic:cNvPr id="${imageId}" name="image${imageId}.${extension}"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed="image${imageId}"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill><pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="${width}" cy="${height}"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r>`;
        };

        const logoRuns = logos.map(logo => picture(logo, 26, 22)).join('<w:r><w:t xml:space="preserve">  </w:t></w:r>');
        const heading = table([5100, 10094], [`<w:tr>${cell(`<w:p>${logoRuns}</w:p>`, 5100)}${cell(
            ['LAPORAN KEGIATAN', 'SATUAN POLISI PAMONG PRAJA', 'KABUPATEN KULON PROGO'].map(line => paragraph(line, { bold: true, size: 26, after: 30 })).join('') + paragraph(periodLabel, { bold: true, size: 18 }), 10094,
        )}</w:tr>`], false);

        const widths = [680, 1700, 2720, 6570, 3524];
        const headers = ['No', 'Tanggal/Waktu', 'Personel', 'Tindakan dan Hasil', 'Dokumentasi'];
        const rows = [`<w:tr><w:trPr><w:tblHeader/></w:trPr>${headers.map((text, index) => cell(paragraph(text, { bold: true, align: 'center', color: 'FFFFFF' }), widths[index], true)).join('')}</w:tr>`];

        activities.forEach((activity, index) => {
            const findings = (activity.temuan || []).map(item => `${item.jenis}: ${item.jumlah} ${item.satuan}`).join(', ') || '-';
            const details = [`Kegiatan: ${activity.nama}`, `Lokasi: ${activity.lokasi}`, `Tindakan: ${activity.deskripsi || '-'}`, `Hasil: ${activity.hasil || '-'}`, `Temuan: ${findings}`].map(text => paragraph(text, { size: 16 })).join('');
            const photoRuns = (photos[index] || []).map(photo => picture(photo, 23, 22)).filter(Boolean);
            const photoParagraphs = [];
            for (let photoIndex = 0; photoIndex < photoRuns.length; photoIndex += 2) {
                photoParagraphs.push(`<w:p><w:pPr><w:spacing w:after="80"/><w:jc w:val="center"/></w:pPr>${photoRuns.slice(photoIndex, photoIndex + 2).join('<w:r><w:t xml:space="preserve">  </w:t></w:r>')}</w:p>`);
            }
            rows.push(`<w:tr>${[
                paragraph(index + 1, { align: 'center', size: 16 }),
                paragraph(`${activity.tanggal}\n${activity.waktu ? activity.waktu + ' WIB' : '-'}`, { align: 'center', size: 16 }),
                paragraph(activity.personel.join(', ') || '-', { size: 16 }),
                details,
                photoParagraphs.join('') || paragraph('-', { align: 'center', size: 16 }),
            ].map((content, column) => cell(content, widths[column])).join('')}</w:tr>`);
        });

        const counts = new Map();
        activities.forEach(activity => {
            const category = activity.nama_template || 'Tanpa Template';
            counts.set(category, (counts.get(category) || 0) + 1);
        });
        const summary = paragraph('Jumlah Kegiatan per Kategori:', { bold: true, size: 22 })
            + [...counts].sort((first, second) => second[1] - first[1]).map(([category, count]) => paragraph(`${category}: ${count}`)).join('');
        const signature = table([10094, 5100], [`<w:tr><w:trPr><w:cantSplit/></w:trPr>${cell('<w:p/>', 10094)}${cell([
            `Kulon Progo, ${exportDate}`, 'Menyetujui,', 'Kepala Satuan Polisi Pamong Praja', 'Kabupaten Kulon Progo',
        ].map(text => paragraph(text, { align: 'center', after: 20 })).join('')
            + paragraph('', { after: 540 })
            + paragraph('BUDI HARTONO, S.Si, M.Si', { bold: true, align: 'center', after: 20 })
            + paragraph('Pembina Utama Muda, IV/c', { align: 'center', after: 20 })
            + paragraph('NIP. 19690318 199603 1 003', { align: 'center', after: 20 }), 5100)}</w:tr>`], false);

        files.push(['word/document.xml', xmlHeader + `<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="${officeRelationships}" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"><w:body>${heading}${paragraph('')}${table(widths, rows)}${paragraph('')}${summary}${signature}<w:sectPr><w:pgSz w:w="16838" w:h="11906" w:orient="landscape"/><w:pgMar w:top="680" w:right="822" w:bottom="680" w:left="822" w:header="360" w:footer="360"/></w:sectPr></w:body></w:document>`]);
        files.push(['word/styles.xml', xmlHeader + '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial"/><w:sz w:val="18"/></w:rPr></w:rPrDefault><w:pPrDefault><w:pPr><w:spacing w:after="80" w:line="240" w:lineRule="auto"/></w:pPr></w:pPrDefault></w:docDefaults></w:styles>']);
        relationships.push(`<Relationship Id="styles" Type="${officeRelationships}/styles" Target="styles.xml"/>`);
        files.push(['word/_rels/document.xml.rels', xmlHeader + `<Relationships xmlns="${relationshipsNamespace}">${relationships.join('')}</Relationships>`]);
        files.push(['_rels/.rels', xmlHeader + `<Relationships xmlns="${relationshipsNamespace}"><Relationship Id="document" Type="${officeRelationships}/officeDocument" Target="word/document.xml"/></Relationships>`]);
        files.push(['[Content_Types].xml', xmlHeader + '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Default Extension="png" ContentType="image/png"/><Default Extension="jpg" ContentType="image/jpeg"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/><Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/></Types>']);
        return zip(files);
    };

    window.SiprajaWordExport = { createDocument };
})();

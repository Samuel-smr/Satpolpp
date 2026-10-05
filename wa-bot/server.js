import fs from 'node:fs';

import express from 'express';
import qrcode from 'qrcode-terminal';
import wa from 'whatsapp-web.js';

const { Client, LocalAuth } = wa;

const PORT = process.env.PORT || 3456;
const app = express();

app.use(express.json());

function findChrome() {
    const candidates = [
        process.env.CHROME_PATH,
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
        'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
    ].filter(Boolean);

    return candidates.find((path) => fs.existsSync(path)) ?? undefined;
}

function normalizePhone(phone) {
    let digits = String(phone).replace(/\D+/g, '');
    if (digits.startsWith('0')) {
        digits = '62' + digits.slice(1);
    } else if (digits.startsWith('8')) {
        digits = '62' + digits;
    }
    return digits;
}

const client = new Client({
    authStrategy: new LocalAuth({ dataPath: './session' }),
    puppeteer: {
        headless: true,
        executablePath: findChrome(),
        args: ['--no-sandbox', '--disable-setuid-sandbox'],
    },
});

app.get('/health', (_req, res) => {
    res.json({ status: client.info ? 'ready' : 'connecting' });
});

app.post('/send', async (req, res) => {
    const { target, message } = req.body || {};

    if (!client.info) {
        return res.status(503).json({ status: false, reason: 'BOT_BELUM_SIAP' });
    }
    if (!target || !message) {
        return res.status(400).json({ status: false, reason: 'TARGET_ATAU_PESAN_KOSONG' });
    }

try {
        const number = normalizePhone(target);
        const chatId = `${number}@c.us`;

        let registered = true;
        try {
            registered = await client.isRegisteredUser(chatId);
        } catch (_error) {
            registered = null;
        }

        if (registered === false) {
            return res.status(422).json({ status: false, reason: 'NOMOR_TIDAK_TERDAFTAR', number });
        }

        const sent = await client.sendMessage(chatId, String(message));
        return res.json({ status: true, messageId: sent?.id?.id ?? null, number });
    } catch (error) {
        console.error('Send error:', error);
        return res.status(422).json({ status: false, reason: error.message || 'GAGAL_KIRIM', stack: error.stack });
    }
});

client.on('qr', (qr) => {
    console.log('=== Scan QR berikut dengan WhatsApp Anda ===');
    qrcode.generate(qr, { small: true });
});

client.on('ready', () => {
    console.log('Bot WhatsApp siap. Notifikasi akan dikirim otomatis.');
    console.log(`Server lokal: http://localhost:${PORT}`);
});

client.on('auth_failure', (message) => {
    console.error('Autentikasi gagal:', message);
    process.exit(1);
});

client.initialize();

app.listen(PORT, () => {
    console.log(`WA Bot HTTP API berjalan di port ${PORT}`);
});
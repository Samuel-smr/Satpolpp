import fs from 'node:fs';

import express from 'express';
import qrcode from 'qrcode-terminal';
import wa from 'whatsapp-web.js';

const { Client, LocalAuth } = wa;

const PORT = process.env.PORT || 3456;
const BOT_TOKEN = process.env.WA_BOT_TOKEN || '';
const app = express();

app.use(express.json());

function checkToken(req, res, next) {
    if (!BOT_TOKEN) {
        return next();
    }

    const [scheme, token] = String(req.headers.authorization || '').split(' ');

    if (scheme === 'Bearer' && token === BOT_TOKEN) {
        return next();
    }

    return res.status(401).json({ status: false, reason: 'UNAUTHORIZED' });
}

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

let ready = false;

app.get('/health', (_req, res) => {
    res.json({ status: client.info && ready ? 'ready' : 'connecting' });
});

app.post('/send', checkToken, async (req, res) => {
    const { target, message } = req.body || {};

    if (!client.info || !ready) {
        return res.status(503).json({ status: false, reason: 'BOT_BELUM_SIAP', ready });
    }
    if (
        typeof target !== 'string' || !/^\+?[0-9][0-9\s-]{8,18}$/.test(target)
        || typeof message !== 'string' || message.trim() === '' || message.length > 5000
    ) {
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
        return res.status(422).json({ status: false, reason: 'GAGAL_KIRIM' });
    }
});

client.on('loading_screen', (percent, message) => {
    console.log(`WhatsApp memuat: ${percent}% ${message || ''}`);
});

client.on('authenticated', () => {
    console.log('WhatsApp terautentikasi (scan berhasil).');
});

client.on('qr', (qr) => {
    console.log('=== Scan QR berikut dengan WhatsApp Anda ===');
    qrcode.generate(qr, { small: true });
});

client.on('ready', () => {
    ready = true;
    console.log('Bot WhatsApp siap. Notifikasi akan dikirim otomatis.');
    console.log(`Server lokal: http://localhost:${PORT}`);
});

client.on('disconnected', (reason) => {
    console.log('WhatsApp terputus:', reason);
    if (reason === 'LOGGED_OUT' || reason === 'LOGOUT') {
        console.log('Sesi di-logout. Hapus folder session dan scan QR baru.');
    }
});

client.on('auth_failure', (message) => {
    console.error('Autentikasi gagal:', message);
    process.exit(1);
});

client.initialize();

process.on('unhandledRejection', (reason) => {
    console.error('Ada unhandled rejection:', reason);
});

process.on('uncaughtException', (error) => {
    console.error('Ada uncaught exception:', error.message);
});

app.listen(PORT, '127.0.0.1', () => {
    console.log(`WA Bot HTTP API berjalan di port ${PORT}`);

    if (!BOT_TOKEN) {
        console.warn('PERINGATAN: WA_BOT_TOKEN belum diset. Endpoint /send terbuka tanpa autentikasi.');
    }
});

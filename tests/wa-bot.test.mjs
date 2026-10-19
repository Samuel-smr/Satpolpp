import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import vm from 'node:vm';

function bot({ ready = true, fail = false } = {}) {
    const routes = new Map();
    const sent = [];
    let host;
    const app = {
        use() {}, get() {},
        post(path, ...handlers) { routes.set(path, handlers); },
        listen(port, address) { host = address; },
    };
    const express = Object.assign(() => app, { json() {} });
    class Client {
        info = ready ? {} : null;
        on(event, handler) { if (event === 'ready' && ready) handler(); }
        initialize() {}
        async isRegisteredUser() { return true; }
        async sendMessage(number, message) {
            if (fail) throw new Error('private session path and credentials');
            sent.push({ number, message });
            return { id: { id: 'message-id' } };
        }
    }
    const source = readFileSync(new URL('../wa-bot/server.js', import.meta.url), 'utf8').replace(/^import .*;\r?$/gm, '');
    vm.runInNewContext(source, {
        fs: { existsSync: () => false }, express, qrcode: {},
        wa: { Client, LocalAuth: class {} }, process: { env: {}, on() {} },
        console: { log() {}, error() {} },
    });
    return {
        host, sent,
        async send(body) {
            const response = { statusCode: 200, status(code) { this.statusCode = code; return this; }, json(data) { this.data = data; return this; } };
            const handlers = routes.get('/send');
            let index = 0;
            const next = () => handlers[index++]({ body, headers: {} }, response, next);
            await next();
            return response;
        },
    };
}

test('WhatsApp bot accepts connections only on loopback', () => {
    assert.equal(bot().host, '127.0.0.1');
});

test('invalid target, array message and oversized message never reach WhatsApp', async () => {
    const server = bot();
    for (const body of [
        { target: ['082233445566'], message: 'test' },
        { target: '082233445566', message: ['test'] },
        { target: 'invalid', message: 'test' },
        { target: '082233445566', message: 'a'.repeat(5001) },
    ]) assert.equal((await server.send(body)).statusCode, 400);
    assert.equal(server.sent.length, 0);
});

test('WhatsApp send failure never exposes stack trace or internal error', async () => {
    const response = await bot({ fail: true }).send({ target: '082233445566', message: 'test' });
    assert.equal(response.statusCode, 422);
    assert.equal(response.data.reason, 'GAGAL_KIRIM');
    assert.equal(response.data.stack, undefined);
    assert.ok(!JSON.stringify(response.data).includes('credentials'));
});

test('valid phone is normalized and connecting bot returns 503', async () => {
    const server = bot();
    assert.equal((await server.send({ target: '082233445566', message: 'test' })).statusCode, 200);
    assert.equal(server.sent[0].number, '6282233445566@c.us');
    assert.equal((await bot({ ready: false }).send({ target: '082233445566', message: 'test' })).statusCode, 503);
});

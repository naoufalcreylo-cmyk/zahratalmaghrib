// Extract translatable strings from the theme (PHP, JS, block.json, theme.json, pattern headers).
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const DOMAIN = 'zahrat-almaghrib';

function walk(dir, out = []) {
	for (const e of fs.readdirSync(dir, { withFileTypes: true })) {
		const p = path.join(dir, e.name);
		if (e.isDirectory()) walk(p, out);
		else out.push(p);
	}
	return out;
}

// String literal: '...' or "..." with escapes.
const S = String.raw`('(?:[^'\\]|\\.)*'|"(?:[^"\\]|\\.)*")`;
const W = String.raw`\s*`;
function unq(s) {
	const q = s[0];
	s = s.slice(1, -1);
	if (q === "'") return s.replace(/\\'/g, "'").replace(/\\\\/g, '\\');
	return s.replace(/\\"/g, '"').replace(/\\n/g, '\n').replace(/\\\\/g, '\\');
}

const patterns = [
	{ re: new RegExp(String.raw`\b(?:__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e)\(${W}${S}${W},${W}['"]${DOMAIN}['"]${W}\)`, 'g'), kind: 's' },
	{ re: new RegExp(String.raw`\b(?:_x|_ex|esc_html_x|esc_attr_x)\(${W}${S}${W},${W}${S}${W},${W}['"]${DOMAIN}['"]${W}\)`, 'g'), kind: 'x' },
	{ re: new RegExp(String.raw`\b_n\(${W}${S}${W},${W}${S}${W},[^,]+,${W}['"]${DOMAIN}['"]${W}\)`, 'g'), kind: 'n' },
];

export function extract(themeDir) {
	const THEME = path.resolve(themeDir);
	const entries = new Map(); // key -> {msgid, ctx, plural, refs:Set, js:bool}
	function add(e, ref, js = false) {
		const key = (e.ctx ? e.ctx + '\u0004' : '') + e.msgid;
		if (!entries.has(key)) entries.set(key, { ...e, refs: new Set(), js: false });
		const en = entries.get(key);
		en.refs.add(ref);
		if (js) en.js = true;
	}

	for (const file of walk(THEME)) {
		const rel = path.relative(THEME, file).replace(/\\/g, '/');
		const ext = path.extname(file);
		if (ext === '.php' || ext === '.js') {
			const src = fs.readFileSync(file, 'utf8');
			for (const { re, kind } of patterns) {
				for (const m of src.matchAll(re)) {
					const line = src.slice(0, m.index).split('\n').length;
					const ref = `${rel}:${line}`;
					if (kind === 's') add({ msgid: unq(m[1]) }, ref, ext === '.js');
					if (kind === 'x') add({ msgid: unq(m[1]), ctx: unq(m[2]) }, ref, ext === '.js');
					if (kind === 'n') add({ msgid: unq(m[1]), plural: unq(m[2]) }, ref, ext === '.js');
				}
			}
		}
		if (path.basename(file) === 'block.json') {
			const j = JSON.parse(fs.readFileSync(file, 'utf8'));
			if (j.title) add({ msgid: j.title, ctx: 'block title' }, rel);
			if (j.description) add({ msgid: j.description, ctx: 'block description' }, rel);
		}
	}

	const tj = JSON.parse(fs.readFileSync(path.join(THEME, 'theme.json'), 'utf8'));
	for (const c of tj.settings.color.palette) add({ msgid: c.name, ctx: 'Color name' }, 'theme.json');
	for (const f of tj.settings.typography.fontSizes) add({ msgid: f.name, ctx: 'Font size name' }, 'theme.json');
	for (const f of tj.settings.typography.fontFamilies) add({ msgid: f.name, ctx: 'Font family name' }, 'theme.json');
	for (const s of tj.settings.spacing.spacingSizes) add({ msgid: s.name, ctx: 'Space size name' }, 'theme.json');
	for (const t of tj.templateParts) add({ msgid: t.title, ctx: 'Template part name' }, 'theme.json');
	for (const t of tj.customTemplates) add({ msgid: t.title, ctx: 'Custom template name' }, 'theme.json');

	const css = fs.readFileSync(path.join(THEME, 'style.css'), 'utf8');
	const desc = css.match(/^Description:\s*(.+)$/m);
	if (desc) add({ msgid: desc[1].trim() }, 'style.css');

	for (const file of walk(path.join(THEME, 'patterns'))) {
		const src = fs.readFileSync(file, 'utf8');
		const t = src.match(/^\s*\*\s*Title:\s*(.+)$/m);
		const d = src.match(/^\s*\*\s*Description:\s*(.+)$/m);
		const rel = 'patterns/' + path.basename(file);
		if (t) add({ msgid: t[1].trim(), ctx: 'Pattern title' }, rel);
		if (d) add({ msgid: d[1].trim(), ctx: 'Pattern description' }, rel);
	}
	return entries;
}

if (process.argv[1] && fileURLToPath(import.meta.url) === path.resolve(process.argv[1])) {
	const entries = extract(process.argv[2] || 'zahrat-almaghrib');
	for (const e of entries.values()) console.log(JSON.stringify([e.ctx || '', e.msgid, e.plural || '']));
	console.error(entries.size + ' strings');
}

const fs = require('fs');
const path = require('path');
const { convert } = require('./md2html.js');

const SRC = '/home/douglas/Documents/AfrikSolutions/Projects/Petrolex/docs/client';
const OUT = process.argv[2];

const BRAND = '#1B6B4A';
const BRAND_DARK = '#0F4630';
const ACCENT = '#E8A33D';

const DOCS = [
  {
    file: '01-cahier-des-charges.md',
    out: 'Isogaz-01-Cahier-des-charges',
    kicker: 'Document 1 sur 3',
    title: 'Cahier des charges fonctionnel',
    subtitle: 'Périmètre, acteurs, cas d’utilisation et règles de gestion',
    audience: 'Direction · Maîtrise d’ouvrage',
  },
  {
    file: '02-guide-utilisateur.md',
    out: 'Isogaz-02-Guide-utilisateur',
    kicker: 'Document 2 sur 3',
    title: 'Guide d’utilisation',
    subtitle: 'Configuration initiale et exploitation de bout en bout',
    audience: 'Administrateurs · Exploitants',
  },
  {
    file: '03-documentation-technique.md',
    out: 'Isogaz-03-Documentation-technique',
    kicker: 'Document 3 sur 3',
    title: 'Documentation technique',
    subtitle: 'Architecture, modèle de données, API et exploitation',
    audience: 'Équipe technique · DSI',
  },
];

function css() {
  return `
@page {
  size: A4;
  margin: 20mm 17mm 18mm 17mm;
}
@page :first { margin: 0; }

* { box-sizing: border-box; }

html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

body {
  font-family: "Source Sans 3", "Segoe UI", "Helvetica Neue", Arial, sans-serif;
  font-size: 9.7pt;
  line-height: 1.62;
  color: #23282E;
  margin: 0;
  hyphens: none;
}

/* ---------- Cover ---------- */
/* Sized in vh, not mm: Chrome's page box rounds a 297mm child just over the
   sheet and spills a blank second page. */
.cover {
  page-break-after: always;
  height: 100vh;
  position: relative;
  overflow: hidden;
  background: ${BRAND_DARK};
  color: #fff;
}
.cover-art {
  position: absolute; inset: 0;
  background:
    radial-gradient(circle at 78% 12%, rgba(232,163,61,.30) 0%, rgba(232,163,61,0) 46%),
    radial-gradient(circle at 12% 88%, rgba(27,107,74,.85) 0%, rgba(15,70,48,0) 55%),
    linear-gradient(158deg, ${BRAND_DARK} 0%, #10513A 46%, #093726 100%);
}
.cover-rule {
  position: absolute; top: 0; left: 0; right: 0; height: 7mm;
  background: linear-gradient(90deg, ${ACCENT} 0%, ${ACCENT} 34%, ${BRAND} 34%, ${BRAND} 100%);
}
.cover-inner { position: absolute; inset: 0; padding: 40mm 24mm 24mm 24mm; }
.brand { display: flex; align-items: center; gap: 5mm; }
.brand-mark {
  width: 15mm; height: 15mm; border-radius: 3.4mm;
  background: ${ACCENT};
  color: ${BRAND_DARK};
  font-weight: 800; font-size: 20pt; letter-spacing: -1px;
  display: flex; align-items: center; justify-content: center;
}
.brand-name { font-size: 21pt; font-weight: 800; letter-spacing: .5px; }
.brand-tag { font-size: 8.6pt; opacity: .74; letter-spacing: 2.4px; text-transform: uppercase; margin-top: 1mm; }

.cover-mid { position: absolute; left: 24mm; right: 24mm; bottom: 24mm; }
.kicker {
  display: inline-block;
  font-size: 8pt; letter-spacing: 2.6px; text-transform: uppercase;
  padding: 2mm 4.5mm; border: .35mm solid rgba(255,255,255,.42); border-radius: 20mm;
  opacity: .92;
}
h1.cover-title {
  font-size: 33pt; line-height: 1.13; font-weight: 800;
  margin: 7mm 0 0 0; letter-spacing: -.6px; max-width: 150mm;
}
.cover-sub {
  font-size: 12.4pt; font-weight: 300; line-height: 1.5;
  margin-top: 5mm; max-width: 132mm; opacity: .9;
}
.cover-accent { width: 34mm; height: 1.4mm; background: ${ACCENT}; margin: 9mm 0 0 0; border-radius: 1mm; }

.cover-meta {
  margin-top: 14mm;
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 6mm;
  border-top: .3mm solid rgba(255,255,255,.24); padding-top: 6mm;
}
.cover-meta dt {
  font-size: 7.4pt; letter-spacing: 1.5px; text-transform: uppercase; opacity: .62; margin-bottom: 1.6mm;
}
.cover-meta dd { margin: 0; font-size: 9.6pt; font-weight: 600; }

/* ---------- Table of contents ---------- */
.toc { page-break-after: always; }
.toc h2 {
  font-size: 17pt; color: ${BRAND_DARK}; margin: 0 0 8mm 0;
  padding-bottom: 3mm; border-bottom: .8mm solid ${ACCENT};
  page-break-after: avoid;
}
.toc ul { list-style: none; margin: 0; padding: 0; }
/* Long tables of contents run in two columns so the front matter stays
   on a single sheet. */
.toc.two-col ul { column-count: 2; column-gap: 10mm; }
.toc.two-col li { break-inside: avoid; }
.toc.two-col .toc-1 { padding-top: 2mm; padding-bottom: 2mm; }
.toc li { margin: 0; }
.toc a { text-decoration: none; color: inherit; display: block; }
.toc-1 {
  font-weight: 700; font-size: 10.2pt; color: ${BRAND_DARK};
  padding: 2.4mm 0 2.4mm 0; border-bottom: .2mm solid #E4E8EA;
}
.toc-2 {
  font-size: 9.1pt; color: #3E4B55; font-weight: 600;
  padding: 1.6mm 0 1.6mm 7mm; border-bottom: .2mm dotted #E9EDEF;
}
.toc-3 {
  font-size: 8.4pt; color: #66727C;
  padding: 1mm 0 1mm 15mm;
}
.toc-num {
  display: inline-block; min-width: 8mm;
  color: ${ACCENT}; font-weight: 700;
}
.toc-3 .toc-num { min-width: 10mm; color: #9AA6AE; }

/* ---------- Body ---------- */
.content { counter-reset: none; }

.content h1 {
  font-size: 19pt; color: ${BRAND_DARK}; font-weight: 800;
  margin: 0 0 6mm 0; padding-bottom: 3mm;
  border-bottom: .8mm solid ${ACCENT};
  page-break-after: avoid; page-break-before: always;
  letter-spacing: -.2px;
}
.content > h1:first-child { page-break-before: avoid; }

.content h2 {
  font-size: 13.4pt; color: ${BRAND_DARK}; font-weight: 700;
  margin: 9mm 0 3.5mm 0; padding-left: 3.4mm;
  border-left: 1.1mm solid ${BRAND};
  page-break-after: avoid; letter-spacing: -.1px;
}
.content h3 {
  font-size: 11pt; color: #1D3B30; font-weight: 700;
  margin: 6.5mm 0 2.4mm 0; page-break-after: avoid;
}
.content h4 {
  font-size: 9.9pt; color: #3A4750; font-weight: 700;
  margin: 5mm 0 2mm 0; page-break-after: avoid;
}

.content p { margin: 0 0 3.2mm 0; orphans: 3; widows: 3; }

.content ul, ol { margin: 0 0 3.6mm 0; padding-left: 5.6mm; }
.content li { margin-bottom: 1.5mm; padding-left: 1mm; }
li::marker { color: ${BRAND}; font-weight: 700; }

strong { color: #10231A; font-weight: 700; }

a { color: ${BRAND}; text-decoration: none; border-bottom: .2mm solid rgba(27,107,74,.35); }
.xref { color: ${BRAND}; font-weight: 600; }

code {
  font-family: "JetBrains Mono", "SFMono-Regular", Consolas, monospace;
  font-size: 8.3pt;
  background: #F0F4F2;
  color: ${BRAND_DARK};
  padding: .5mm 1.4mm;
  border-radius: 1mm;
  border: .18mm solid #DDE6E1;
  white-space: nowrap;
}

.content pre.fig {
  background: #F7F9F8;
  border: .25mm solid #DDE5E1;
  border-left: 1.1mm solid ${BRAND};
  border-radius: 1.4mm;
  padding: 4.5mm 5mm;
  margin: 0 0 4.5mm 0;
  page-break-inside: avoid;
  overflow: hidden;
}
pre.fig code {
  font-family: "JetBrains Mono", "SFMono-Regular", Consolas, monospace;
  font-size: 7.5pt;
  line-height: 1.5;
  background: none; border: none; padding: 0;
  color: #24352C;
  white-space: pre;
  display: block;
}

.content table {
  width: 100%;
  border-collapse: collapse;
  margin: 0 0 4.5mm 0;
  font-size: 8.7pt;
  page-break-inside: auto;
}
thead { display: table-header-group; }
tr { page-break-inside: avoid; }
th {
  background: ${BRAND_DARK};
  color: #fff;
  font-weight: 600;
  text-align: left;
  padding: 2.4mm 3mm;
  font-size: 8.3pt;
  letter-spacing: .3px;
  border-right: .2mm solid rgba(255,255,255,.14);
}
th:last-child { border-right: none; }
td {
  padding: 2.2mm 3mm;
  border-bottom: .2mm solid #E6EBE8;
  vertical-align: top;
  line-height: 1.5;
}
tbody tr:nth-child(even) { background: #F7FAF8; }
td code { font-size: 7.9pt; white-space: normal; }

.content blockquote {
  margin: 0 0 4.5mm 0;
  padding: 3.4mm 4.5mm;
  border-radius: 1.4mm;
  page-break-inside: avoid;
  font-size: 9.3pt;
}
blockquote p { margin: 0; }
blockquote.note {
  background: #F1F6F3;
  border-left: 1.1mm solid ${BRAND};
  color: #24382E;
}
blockquote.warn {
  background: #FEF7EC;
  border-left: 1.1mm solid ${ACCENT};
  color: #5A3D0E;
}
blockquote.warn::before {
  content: "\\26A0  IMPORTANT";
  display: block;
  font-size: 7.6pt; font-weight: 800; letter-spacing: 1.4px;
  color: #B87413; margin-bottom: 1.8mm;
}

.content hr { border: none; border-top: .2mm solid #E2E8E5; margin: 6mm 0; }

.content figure {
  margin: 0 0 5.5mm 0;
  page-break-inside: avoid;
}
.content figure img {
  display: block;
  width: 100%;
  max-width: 100%;
  border: .25mm solid #D8E0DC;
  border-radius: 1.4mm;
}
.content figcaption {
  margin-top: 1.8mm;
  font-size: 8.2pt;
  color: #5C6B73;
  font-style: italic;
  line-height: 1.45;
  padding-left: 1mm;
  border-left: .8mm solid ${ACCENT};
  padding-left: 2.6mm;
}

.content > hr:last-child { display: none; }
`;
}

function buildToc(toc) {
  const items = toc.map(e => {
    const m = e.txt.match(/^(\d+(?:\.\d+)?)\.?\s+(.*)$/);
    const num = m ? m[1] : null;
    const label = (m ? m[2] : e.txt).replace(/&/g, '&amp;').replace(/</g, '&lt;');
    return '<li><a href="#' + e.id + '"><div class="toc-' + e.lvl + '">' +
      (num ? '<span class="toc-num">' + num + '</span>' : '') +
      label + '</div></a></li>';
  }).join('');
  const cls = toc.length > 24 ? 'toc two-col' : 'toc';
  return '<section class="' + cls + '"><h2>Sommaire</h2><ul>' + items + '</ul></section>';
}

function cover(d, meta) {
  return `<section class="cover">
  <div class="cover-art"></div>
  <div class="cover-rule"></div>
  <div class="cover-inner">
    <div class="brand">
      <div class="brand-mark">IG</div>
      <div>
        <div class="brand-name">Isogaz</div>
        <div class="brand-tag">Distribution de gaz domestique</div>
      </div>
    </div>
    <div class="cover-mid">
      <span class="kicker">${d.kicker}</span>
      <h1 class="cover-title">${d.title}</h1>
      <div class="cover-sub">${d.subtitle}</div>
      <div class="cover-accent"></div>
      <dl class="cover-meta">
        <div><dt>Version du document</dt><dd>${meta.docVersion}</dd></div>
        <div><dt>Version applicative</dt><dd>${meta.appVersion}</dd></div>
        <div><dt>Date</dt><dd>${meta.date}</dd></div>
      </dl>
      <dl class="cover-meta" style="border-top:none;padding-top:0;margin-top:6mm;">
        <div style="grid-column: span 3;"><dt>Destinataires</dt><dd>${d.audience}</dd></div>
      </dl>
    </div>
  </div>
</section>`;
}

/* The cover and TOC are dropped from the source before conversion so the
   printed front matter is not duplicated by the markdown's own heading. */
function stripFrontMatter(md) {
  const lines = md.split('\n');
  let start = 0;
  for (let i = 0; i < lines.length; i++) {
    if (/^---+\s*$/.test(lines[i])) { start = i + 1; break; }
  }
  return lines.slice(start).join('\n');
}

const manifest = [];

const meta = { docVersion: '1.0', appVersion: 'v1.7.0', date: '18 août 2026' };

for (const d of DOCS) {
  const md = fs.readFileSync(path.join(SRC, d.file), 'utf8');
  let { body, toc } = convert(stripFrontMatter(md));

  /* Inline the screenshots: the HTML is written to a build dir, so relative
     paths would not resolve at print time. */
  body = body.replace(/src="(img\/[^"]+)"/g, (whole, rel) => {
    const abs = path.join(SRC, rel);
    if (!fs.existsSync(abs)) {
      console.warn('  ! image manquante: ' + rel);
      return whole;
    }
    const ext = path.extname(abs).slice(1).toLowerCase();
    const mime = ext === 'png' ? 'image/png' : 'image/jpeg';
    return 'src="data:' + mime + ';base64,' + fs.readFileSync(abs).toString('base64') + '"';
  });
  const html = '<!doctype html><html lang="fr"><head><meta charset="utf-8">' +
    '<title>' + d.title + ' — Isogaz</title><style>' + css() + '</style></head><body>' +
    cover(d, meta) +
    buildToc(toc) +
    '<main class="content">' + body + '</main>' +
    '</body></html>';
  fs.writeFileSync(path.join(OUT, d.out + '.html'), html);
  manifest.push({ out: d.out, toc: toc });
  console.log(d.out + '.html  (' + toc.length + ' entrées de sommaire)');
}

/* Consumed by linkify.py to rebuild internal links and bookmarks. */
fs.writeFileSync(path.join(OUT, 'manifest.json'), JSON.stringify(manifest, null, 2));

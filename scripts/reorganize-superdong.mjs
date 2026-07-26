import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');

const report = { moved: [], updated: [], errors: [] };

function ensureDir(dir) {
  fs.mkdirSync(dir, { recursive: true });
}

function moveFile(fromRel, toRel) {
  const from = path.join(root, fromRel);
  const to = path.join(root, toRel);
  if (!fs.existsSync(from)) {
    report.errors.push(`MISSING: ${fromRel}`);
    return;
  }
  ensureDir(path.dirname(to));
  if (fs.existsSync(to)) {
    report.errors.push(`DEST EXISTS: ${toRel}`);
    return;
  }
  fs.renameSync(from, to);
  report.moved.push({ from: fromRel, to: toRel });
}

const scssMoves = [
  ['resources/sources/main/home-v2/_tokens.scss', 'resources/sources/superdong/foundations/_tokens.scss'],
  ['resources/sources/main/home-v2/_base.scss', 'resources/sources/superdong/foundations/_base.scss'],
  ['resources/sources/main/home-v2/_utilities.scss', 'resources/sources/superdong/foundations/_utilities.scss'],
  ['resources/sources/main/home-v2/_topbar.scss', 'resources/sources/superdong/layout/_topbar.scss'],
  ['resources/sources/main/home-v2/_header.scss', 'resources/sources/superdong/layout/_header.scss'],
  ['resources/sources/main/home-v2/_footer.scss', 'resources/sources/superdong/layout/_footer.scss'],
  ['resources/sources/main/home-v2/_float.scss', 'resources/sources/superdong/layout/_float.scss'],
  ['resources/sources/main/home-v2/_region-switcher.scss', 'resources/sources/superdong/layout/_region-switcher.scss'],
  ['resources/sources/main/home-v2/_section-head.scss', 'resources/sources/superdong/components/_section-head.scss'],
  ['resources/sources/main/home-v2/_booking.scss', 'resources/sources/superdong/components/_booking.scss'],
  ['resources/sources/main/home-v2/_components.scss', 'resources/sources/superdong/components/_cards.scss'],
  ['resources/sources/main/home-v2/_hero.scss', 'resources/sources/superdong/sections/_hero.scss'],
  ['resources/sources/main/home-v2/_sections.scss', 'resources/sources/superdong/sections/_sections.scss'],
  ['resources/sources/main/home-v2/_travel-guide.scss', 'resources/sources/superdong/sections/_travel-guide.scss'],
  ['resources/sources/main/home-v2/_vehicle-rental.scss', 'resources/sources/superdong/sections/_vehicle-rental.scss'],
  ['resources/sources/main/home-v2/_gallery.scss', 'resources/sources/superdong/sections/_gallery.scss'],
  ['resources/sources/main/home-v2/_reviews.scss', 'resources/sources/superdong/sections/_reviews.scss'],
  ['resources/sources/main/home-v2/_faq.scss', 'resources/sources/superdong/sections/_faq.scss'],
  ['resources/sources/main/home-v2/_letter.scss', 'resources/sources/superdong/sections/_letter.scss'],
];

scssMoves.forEach(([from, to]) => moveFile(from, to));

const bladeMoves = [
  ['resources/views/main/home-v2/layouts/app.blade.php', 'resources/views/superdong/layout/app.blade.php'],
  ['resources/views/main/home-v2/partials/header.blade.php', 'resources/views/superdong/chrome/header.blade.php'],
  ['resources/views/main/home-v2/partials/footer.blade.php', 'resources/views/superdong/chrome/footer.blade.php'],
  ['resources/views/main/home-v2/partials/topbar.blade.php', 'resources/views/superdong/chrome/topbar.blade.php'],
  ['resources/views/main/home-v2/partials/mobile-nav.blade.php', 'resources/views/superdong/chrome/mobile-nav.blade.php'],
  ['resources/views/main/home-v2/partials/float.blade.php', 'resources/views/superdong/chrome/float.blade.php'],
  ['resources/views/main/home-v2/partials/svg-sprite.blade.php', 'resources/views/superdong/assets/svg-sprite.blade.php'],
  ['resources/views/main/home-v2/partials/footer-icons.blade.php', 'resources/views/superdong/assets/footer-icons.blade.php'],
  ['resources/views/main/home-v2/partials/footer-icon-defs.blade.php', 'resources/views/superdong/assets/footer-icon-defs.blade.php'],
  ['resources/views/main/home-v2/partials/section-head.blade.php', 'resources/views/superdong/ui/section-head.blade.php'],
  ['resources/views/main/home-v2/partials/product-card.blade.php', 'resources/views/superdong/ui/cards/product.blade.php'],
  ['resources/views/main/home-v2/partials/experience-card.blade.php', 'resources/views/superdong/ui/cards/experience.blade.php'],
  ['resources/views/main/home-v2/partials/gallery-lightbox.blade.php', 'resources/views/superdong/ui/gallery-lightbox.blade.php'],
  ['resources/views/main/home-v2/partials/booking.blade.php', 'resources/views/superdong/sections/booking/widget.blade.php'],
  ['resources/views/main/home-v2/partials/hero-shell.blade.php', 'resources/views/superdong/sections/hero/shell.blade.php'],
  ['resources/views/main/home-v2/partials/hero.blade.php', 'resources/views/superdong/sections/hero/content.blade.php'],
  ['resources/views/main/home-v2/partials/trust.blade.php', 'resources/views/superdong/sections/trust.blade.php'],
  ['resources/views/main/home-v2/partials/cta.blade.php', 'resources/views/superdong/sections/cta.blade.php'],
  ['resources/views/main/home-v2/partials/faq.blade.php', 'resources/views/superdong/sections/faq.blade.php'],
  ['resources/views/main/home-v2/partials/reviews.blade.php', 'resources/views/superdong/sections/reviews.blade.php'],
  ['resources/views/main/home-v2/partials/gallery.blade.php', 'resources/views/superdong/sections/gallery.blade.php'],
  ['resources/views/main/home-v2/partials/travel-guide.blade.php', 'resources/views/superdong/sections/travel-guide.blade.php'],
  ['resources/views/main/home-v2/partials/vehicle-rental.blade.php', 'resources/views/superdong/sections/vehicle-rental.blade.php'],
];

bladeMoves.forEach(([from, to]) => moveFile(from, to));

const pageSections = ['quick', 'ferry', 'tours', 'hotels', 'services', 'route-guide', 'blog'];
pageSections.forEach((name) => {
  moveFile(
    `resources/views/main/home-v2/partials/${name}.blade.php`,
    `resources/views/main/home-v2/sections/${name}.blade.php`,
  );
});

const componentsDir = path.join(root, 'resources/views/main/home-v2/components');
if (fs.existsSync(componentsDir)) {
  ensureDir(path.join(root, 'resources/views/superdong/form/fields'));
  fs.readdirSync(componentsDir).forEach((file) => {
    if (!file.endsWith('.blade.php')) return;
    moveFile(
      `resources/views/main/home-v2/components/${file}`,
      `resources/views/superdong/form/fields/${file}`,
    );
  });
}

function patchScssUseTokens(relPath, depth) {
  const full = path.join(root, relPath);
  if (!fs.existsSync(full)) return;
  let content = fs.readFileSync(full, 'utf8');
  const usePath = depth === 0 ? 'tokens' : '../foundations/tokens';
  const next = content.replace(/@use\s+"tokens"\s+as\s+\*;/, `@use "${usePath}" as *;`);
  if (next !== content) {
    fs.writeFileSync(full, next);
    report.updated.push(relPath);
  }
}

[
  'resources/sources/superdong/foundations/_base.scss',
  'resources/sources/superdong/foundations/_utilities.scss',
].forEach((f) => patchScssUseTokens(f, 0));

[
  ...scssMoves.slice(3).map(([, to]) => to),
].forEach((f) => patchScssUseTokens(f, 1));

const includeMap = [
  ['main.home-v2.layouts.app', 'superdong.layout.app'],
  ['main.home-v2.partials.', 'superdong.'],
  ['main.home-v2.components.', 'superdong.form.fields.'],
  ['superdong.partials.', 'superdong.sections.'],
  ['superdong.sections.booking', 'superdong.sections.booking.widget'],
  ['superdong.sections.hero-shell', 'superdong.sections.hero.shell'],
  ['superdong.sections.hero\'', 'superdong.sections.hero.content\''],
  ['superdong.partials.section-head', 'superdong.ui.section-head'],
  ['superdong.partials.product-card', 'superdong.ui.cards.product'],
  ['superdong.partials.experience-card', 'superdong.ui.cards.experience'],
  ['superdong.partials.gallery-lightbox', 'superdong.ui.gallery-lightbox'],
  ['superdong.chrome.header', 'superdong.chrome.header'],
];

function patchBladeIncludes(relPath) {
  const full = path.join(root, relPath);
  if (!fs.existsSync(full)) return;
  let content = fs.readFileSync(full, 'utf8');
  let next = content;

  const replacements = [
    ["@extends('main.home-v2.layouts.app')", "@extends('superdong.layout.app')"],
    ["@include('main.home-v2.layouts.app'", "@include('superdong.layout.app'"],
    ["@include('main.home-v2.partials.topbar')", "@include('superdong.chrome.topbar')"],
    ["@include('main.home-v2.partials.hero-shell')", "@include('superdong.sections.hero.shell')"],
    ["@include('main.home-v2.partials.header')", "@include('superdong.chrome.header')"],
    ["@include('main.home-v2.partials.hero')", "@include('superdong.sections.hero.content')"],
    ["@include('main.home-v2.partials.trust')", "@include('superdong.sections.trust')"],
    ["@include('main.home-v2.partials.booking')", "@include('superdong.sections.booking.widget')"],
    ["@include('main.home-v2.partials.footer')", "@include('superdong.chrome.footer')"],
    ["@include('main.home-v2.partials.float')", "@include('superdong.chrome.float')"],
    ["@include('main.home-v2.partials.mobile-nav')", "@include('superdong.chrome.mobile-nav')"],
    ["@include('main.home-v2.partials.svg-sprite')", "@include('superdong.assets.svg-sprite')"],
    ["@include('main.home-v2.partials.footer-icons')", "@include('superdong.assets.footer-icons')"],
    ["@include('main.home-v2.partials.footer-icon-defs')", "@include('superdong.assets.footer-icon-defs')"],
    ["@include('main.home-v2.partials.section-head'", "@include('superdong.ui.section-head'"],
    ["@include('main.home-v2.partials.product-card'", "@include('superdong.ui.cards.product'"],
    ["@include('main.home-v2.partials.experience-card'", "@include('superdong.ui.cards.experience'"],
    ["@include('main.home-v2.partials.gallery-lightbox'", "@include('superdong.ui.gallery-lightbox'"],
    ["@include('main.home-v2.partials.quick')", "@include('main.home-v2.sections.quick')"],
    ["@include('main.home-v2.partials.ferry')", "@include('main.home-v2.sections.ferry')"],
    ["@include('main.home-v2.partials.tours')", "@include('main.home-v2.sections.tours')"],
    ["@include('main.home-v2.partials.hotels')", "@include('main.home-v2.sections.hotels')"],
    ["@include('main.home-v2.partials.services')", "@include('main.home-v2.sections.services')"],
    ["@include('main.home-v2.partials.travel-guide')", "@include('superdong.sections.travel-guide')"],
    ["@include('main.home-v2.partials.vehicle-rental')", "@include('superdong.sections.vehicle-rental')"],
    ["@include('main.home-v2.partials.gallery')", "@include('superdong.sections.gallery')"],
    ["@include('main.home-v2.partials.reviews')", "@include('superdong.sections.reviews')"],
    ["@include('main.home-v2.partials.faq')", "@include('superdong.sections.faq')"],
    ["@include('main.home-v2.partials.cta')", "@include('superdong.sections.cta')"],
    ["@include('main.home-v2.components.", "@include('superdong.form.fields."],
    ["resources/sources/main/home-v2.scss", "resources/sources/superdong.scss"],
  ];

  replacements.forEach(([from, to]) => {
    next = next.split(from).join(to);
  });

  if (next !== content) {
    fs.writeFileSync(full, next);
    report.updated.push(relPath);
  }
}

function walkBlade(dirRel) {
  const dir = path.join(root, dirRel);
  if (!fs.existsSync(dir)) return;
  fs.readdirSync(dir, { withFileTypes: true }).forEach((entry) => {
    const rel = path.join(dirRel, entry.name).replace(/\\/g, '/');
    if (entry.isDirectory()) walkBlade(rel);
    else if (entry.name.endsWith('.blade.php')) patchBladeIncludes(rel);
  });
}

walkBlade('resources/views/superdong');
walkBlade('resources/views/main/home-v2');

const superdongScss = `@use "superdong/foundations/tokens";
@use "superdong/foundations/base";
@use "superdong/foundations/utilities";
@use "superdong/layout/topbar";
@use "superdong/layout/region-switcher";
@use "superdong/layout/header";
@use "superdong/components/booking";
@use "superdong/components/section-head";
@use "superdong/components/cards";
@use "superdong/sections/hero";
@use "superdong/sections/sections";
@use "superdong/sections/travel-guide";
@use "superdong/sections/vehicle-rental";
@use "superdong/sections/gallery";
@use "superdong/sections/reviews";
@use "superdong/sections/faq";
@use "superdong/sections/letter";
@use "superdong/layout/footer";
@use "superdong/layout/float";
`;

fs.writeFileSync(path.join(root, 'resources/sources/superdong.scss'), superdongScss);
report.updated.push('resources/sources/superdong.scss');

fs.writeFileSync(path.join(root, 'resources/sources/main/home-v2.scss'), '@use "../superdong";\n');
report.updated.push('resources/sources/main/home-v2.scss');

const vitePath = path.join(root, 'vite.config.js');
if (fs.existsSync(vitePath)) {
  let vite = fs.readFileSync(vitePath, 'utf8');
  if (!vite.includes('superdong.scss')) {
    vite = vite.replace(
      "'resources/sources/main/home-v2.scss',",
      "'resources/sources/superdong.scss',\n                'resources/sources/main/home-v2.scss',",
    );
    fs.writeFileSync(vitePath, vite);
    report.updated.push('vite.config.js');
  }
}

// layout stub for backward compat
const stubLayout = `@extends('superdong.layout.app')\n`;
ensureDir(path.join(root, 'resources/views/main/home-v2/layouts'));
fs.writeFileSync(path.join(root, 'resources/views/main/home-v2/layouts/app.blade.php'), stubLayout);
report.updated.push('resources/views/main/home-v2/layouts/app.blade.php (stub)');

fs.writeFileSync(path.join(root, 'reorganize-report.json'), JSON.stringify(report, null, 2));
console.log(JSON.stringify(report, null, 2));

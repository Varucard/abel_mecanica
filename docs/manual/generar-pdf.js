// Genera el PDF del manual a partir de manual.html, con Chrome sin ventana.
// Uso: node docs/manual/generar-pdf.js   (deja Manual_de_Usuario_Mecanica_Abel.pdf en la raíz)
const { chromium } = require('playwright-core');
const fs = require('fs');
const path = require('path');

(async () => {
  const b = await chromium.launch({ executablePath: process.env.CHROME || '/usr/bin/google-chrome', args: ['--no-sandbox'] });
  const p = await b.newPage();
  await p.goto('file://' + path.join(__dirname, 'manual.html'), { waitUntil: 'networkidle' });
  // La versión de la portada sale del archivo VERSION (la sube el workflow al mergear) y el mes, de hoy.
  const version = fs.readFileSync(path.join(__dirname, '..', '..', 'VERSION'), 'utf8').trim();
  const mes = new Date().toLocaleDateString('es-AR', { month: 'long', year: 'numeric' }).replace(' de ', ' ');
  await p.evaluate(([v, m]) => {
    document.querySelector('.js-version').textContent = v;
    document.querySelector('.js-fecha').textContent = m;
  }, [version, mes]);
  const salida = path.join(__dirname, '..', '..', 'Manual_de_Usuario_Mecanica_Abel.pdf');
  await p.pdf({
    path: salida,
    format: 'A4',
    printBackground: true,
    preferCSSPageSize: true,
    displayHeaderFooter: true,
    headerTemplate: '<span></span>',
    // Número de página abajo, salvo en la portada (el CSS no puede ocultarlo: va en blanco sobre blanco).
    footerTemplate: '<div style="width:100%;font-size:8px;color:#8a94a3;text-align:center;font-family:sans-serif">Manual de Usuario — Mecánica Abel · <span class="pageNumber"></span></div>',
  });
  console.log('PDF generado:', salida);
  await b.close();
})().catch((e) => { console.error(e); process.exit(1); });

// Saca las capturas del manual desde una copia de prueba con los datos de ejemplo (semilla.php).
// Uso: BASE=http://127.0.0.1:8099 USUARIO=abel CLAVE=... node docs/manual/capturar.js
// Requiere playwright-core y Google Chrome (ver docs/manual/README.md).
const { chromium } = require('playwright-core');
const path = require('path');

const BASE = process.env.BASE || 'http://127.0.0.1:8099';
const USUARIO = process.env.USUARIO || 'abel';
const CLAVE = process.env.CLAVE || 'Prueba1234!';
const DESTINO = path.join(__dirname, 'capturas');
const CHROME = process.env.CHROME || '/usr/bin/google-chrome';

(async () => {
  require('fs').mkdirSync(DESTINO, { recursive: true });
  const b = await chromium.launch({ executablePath: CHROME, args: ['--no-sandbox'] });
  const errores = [];

  const sesion = async (opciones = {}) => {
    const ctx = await b.newContext({ viewport: { width: 1280, height: 800 }, deviceScaleFactor: 1.5, colorScheme: 'light', locale: 'es-AR', ...opciones });
    const p = await ctx.newPage();
    p.on('pageerror', (e) => errores.push(`${p.url()}: ${e.message}`));
    await p.goto(BASE + '/login');
    await p.evaluate(() => { localStorage.clear(); localStorage.setItem('theme', 'light'); });
    return p;
  };
  const entrar = async (p) => {
    await p.goto(BASE + '/login');
    await p.fill('#usuario', USUARIO); await p.fill('#clave', CLAVE); await p.click('form button[type=submit]');
    await p.waitForLoadState();
  };
  const foto = async (p, nombre, opciones = {}) => {
    await p.mouse.move(0, 0);
    await p.waitForTimeout(350);
    await p.screenshot({ path: path.join(DESTINO, `${nombre}.png`), ...opciones });
    console.log('·', nombre);
  };
  // Solo el contenido de la pantalla (sin el fondo vacío de abajo).
  const contenido = async (p, nombre, extra = 0) => {
    const caja = await p.locator('.app-marco > .card').boundingBox();
    await foto(p, nombre, { clip: { x: 0, y: 0, width: 1280, height: Math.min(caja.y + caja.height + 16 + extra, 2400) }, fullPage: true });
  };
  const ir = async (p, ruta, espera = 300) => { await p.goto(BASE + ruta); await p.waitForTimeout(espera); };
  const tom = async (p, id, texto) => {
    await p.locator(`#${id}-ts-control`).click(); await p.locator(`#${id}-ts-control`).fill(texto);
    await p.waitForTimeout(400); await p.keyboard.press('Enter'); await p.waitForTimeout(300);
  };

  // ---------- 1. Primeros pasos ----------
  let p = await sesion();
  await ir(p, '/login');
  await foto(p, '01-login');
  await entrar(p);
  await ir(p, '/perfil/clave');
  await contenido(p, '02-clave');

  // ---------- 2. La pantalla principal ----------
  await ir(p, '/');
  await contenido(p, '03-inicio');
  await p.locator('.menu-seccion').first().hover(); await p.waitForTimeout(300);
  await foto(p, '04-menu', { clip: { x: 0, y: 0, width: 1280, height: 330 } });
  await ir(p, '/');
  await p.locator('#busqueda_rapida').pressSequentially('gom', { delay: 60 });
  await p.waitForSelector('#busqueda_sugerencias:not([hidden])');
  await foto(p, '05-sugerencias', { clip: { x: 0, y: 0, width: 1280, height: 340 } });
  await ir(p, '/buscar?q=ford');
  await contenido(p, '06-busqueda');
  await ir(p, '/clientes', 1000);
  await contenido(p, '07-listado');
  await ir(p, '/');
  await p.keyboard.press('?'); await p.waitForSelector('.modal.show'); await p.waitForTimeout(400);
  await foto(p, '08-atajos');
  await p.locator('.modal.show .js-modal-aceptar').click(); await p.waitForTimeout(300);

  // ---------- 3. Llegó un auto ----------
  await ir(p, '/recepcion');
  await contenido(p, '10-recepcion-inicio');
  await p.fill('#patente', 'ab 321 cd'); await p.click('.js-buscar-patente'); await p.waitForSelector('#datos_nuevos:not([hidden])');
  await p.fill('#dni', '34555666'); await p.locator('#dni').blur(); await p.waitForTimeout(500);
  await p.fill('#nombre', 'Martín'); await p.fill('#apellido', 'Romero'); await p.fill('#telefono', '011 15 4455-6677');
  await p.fill('#email', 'mromero@ejemplo.com.ar');
  await tom(p, 'marca_id', 'Ford'); await p.waitForTimeout(500); await tom(p, 'modelo_id', 'Ranger');
  await p.fill('#anio', '2021'); await p.fill('#diagnostico', 'Hace ruido la suspensión delantera'); await p.fill('#km_ingreso', '68000');
  await p.locator('#mecanico_id').selectOption({ index: 1 });
  await contenido(p, '11-recepcion-nuevo');
  await ir(p, '/recepcion');
  await p.fill('#patente', 'AB789KL'); await p.click('.js-buscar-patente'); await p.waitForSelector('a:has-text("Seguir con la orden")');
  await contenido(p, '12-recepcion-abierta');
  await ir(p, '/recepcion');
  await p.fill('#patente', 'AE123FG'); await p.click('.js-buscar-patente'); await p.waitForSelector('.js-tras-buscar:not([hidden])');
  await contenido(p, '13-recepcion-conocido');

  // ---------- 4. Clientes ----------
  await ir(p, '/clientes/crear');
  await contenido(p, '14-cliente-nuevo');
  await p.click('.card-body form button[type=submit]'); await p.waitForTimeout(400);
  await contenido(p, '15-errores');
  await ir(p, '/clientes/1');
  await contenido(p, '16-cliente-ficha');

  // ---------- 5. Vehículos ----------
  await ir(p, '/vehiculos/crear');
  await contenido(p, '17-vehiculo-nuevo');
  await ir(p, '/vehiculos/1');
  await contenido(p, '18-vehiculo-ficha');

  // ---------- 6. Turnos ----------
  await ir(p, '/turnos/crear');
  await tom(p, 'cliente_id', 'GOMEZ'); await p.waitForTimeout(600);
  const proximoHabil = await p.evaluate(() => { const d = new Date(); do { d.setDate(d.getDate() + 1); } while (d.getDay() === 0); return d.toLocaleDateString('en-CA'); });
  await p.fill('#fecha', proximoHabil); await p.locator('#fecha').dispatchEvent('change'); await p.waitForTimeout(800);
  await p.locator('.js-horarios button:not([disabled])').nth(2).click();
  await p.fill('#descripcion', 'Cambio de aceite');
  await contenido(p, '20-turno-nuevo');
  await ir(p, '/turnos', 1000);
  await contenido(p, '21-turnos');
  await ir(p, '/turnos/semana');
  await contenido(p, '22-agenda');

  // ---------- 7. Órdenes ----------
  await ir(p, '/ordenes/6');
  await contenido(p, '30-orden-recibida');
  await ir(p, '/ordenes/6/editar');
  await tom(p, 'servicio_id', 'Diagnóstico'); await p.keyboard.press('Escape');
  await tom(p, 'repuesto_id', 'Líquido'); await p.keyboard.press('Escape');
  await p.fill('input[name^="cantidad_repuesto"]', '2'); await p.locator('input[name^="cantidad_repuesto"]').blur();
  await contenido(p, '31-orden-editar');
  await ir(p, '/ordenes/3');
  await contenido(p, '32-orden-reparacion');
  await p.click('button:has-text("Terminar el trabajo")'); await p.waitForSelector('.modal.show'); await p.waitForTimeout(400);
  await foto(p, '33-terminar');
  await p.locator('.modal.show .js-modal-cancelar').click(); await p.waitForTimeout(300);
  await ir(p, '/ordenes/7');
  await p.click('button:has-text("Empezar el trabajo")'); await p.waitForLoadState(); await p.waitForTimeout(400);
  await foto(p, '34-deshacer', { clip: { x: 640, y: 500, width: 640, height: 300 } });
  await p.click('.toast button:has-text("Deshacer")'); await p.waitForLoadState();
  await ir(p, '/ordenes/2');
  await contenido(p, '35-orden-lista');
  await p.click('button:has-text("Imprimir / Enviar")'); await p.waitForTimeout(300);
  await foto(p, '36-imprimir', { clip: { x: 0, y: 0, width: 1280, height: 720 } });
  await ir(p, '/ordenes', 1200);
  await contenido(p, '37-ordenes');

  // ---------- 8. Presupuestos y comprobantes ----------
  await ir(p, '/ordenes/4/presupuesto');
  await foto(p, '40-presupuesto', { fullPage: true });
  await ir(p, '/ordenes/1/entrega');
  await foto(p, '41-entrega', { fullPage: true });

  // ---------- 9. Pagos y deudores ----------
  await ir(p, '/ordenes/2#registrar_pago', 800);
  await foto(p, '42-cobro');
  await ir(p, '/deudores', 800);
  await contenido(p, '43-deudores');

  // ---------- 10. Catálogos ----------
  await ir(p, '/servicios', 800);
  await contenido(p, '50-servicios');
  await ir(p, '/combos/1/editar');
  await contenido(p, '51-combo');
  await ir(p, '/modelos', 800);
  await contenido(p, '52-modelos');

  // ---------- 11. Stock ----------
  await ir(p, '/repuestos', 800);
  await contenido(p, '60-repuestos');
  await ir(p, '/repuestos/5/stock', 800);
  await p.locator('details.mas-datos summary').click();
  await contenido(p, '61-stock');
  await ir(p, '/proveedores/1/editar');
  await contenido(p, '62-proveedor');

  // ---------- 12 y 13. Precios y reportes ----------
  await ir(p, '/precios');
  await p.fill('#porcentaje', '10');
  await p.click('button:has-text("Ver cambios")'); await p.waitForLoadState(); await p.waitForTimeout(500);
  await contenido(p, '70-precios');
  await ir(p, '/reportes');
  await contenido(p, '71-reportes');

  // ---------- 15. Configuración ----------
  for (const seccion of ['taller', 'trabajo', 'turnos', 'notificaciones', 'mensajes', 'portal', 'backups']) {
    await ir(p, `/configuracion/${seccion}`);
    await contenido(p, `80-config-${seccion}`);
  }

  // ---------- 16 y 17. Usuarios, empleados, auditoría, registro ----------
  await ir(p, '/usuarios'); await p.waitForTimeout(600);
  await contenido(p, '90-usuarios');
  await ir(p, '/usuarios/crear');
  await contenido(p, '91-usuario-nuevo');
  await ir(p, '/empleados'); await p.waitForTimeout(600);
  await contenido(p, '92-empleados');
  await ir(p, '/auditoria', 1000);
  await contenido(p, '93-auditoria');
  await ir(p, '/logs', 600);
  await contenido(p, '94-logs');

  // Borrador recuperado.
  await ir(p, '/clientes/crear');
  await p.fill('#nombre', 'Laura'); await p.fill('#apellido', 'Medina'); await p.waitForTimeout(800);
  await p.reload(); await p.waitForTimeout(700);
  await foto(p, '19-borrador', { clip: { x: 0, y: 0, width: 1280, height: 520 } });
  await p.click('button:has-text("Descartar")'); await p.waitForTimeout(400);

  // ---------- 14. Lo que ve el cliente ----------
  const cliente = await b.newContext({ viewport: { width: 420, height: 860 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true, colorScheme: 'light' });
  const pc = await cliente.newPage();
  await pc.goto(BASE + '/seguimiento');
  await pc.screenshot({ path: path.join(DESTINO, '95-portal.png') }); console.log('· 95-portal');
  await pc.fill('#dni', '22333444'); await pc.fill('#patente', 'AB789KL'); await pc.click('form button[type=submit]'); await pc.waitForLoadState();
  await pc.screenshot({ path: path.join(DESTINO, '96-portal-resultado.png') }); console.log('· 96-portal-resultado');

  // ---------- Celular ----------
  const m = await sesion({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true });
  await entrar(m);
  await ir(m, '/');
  await foto(m, '09-celular-inicio');
  await m.locator('.barra-inferior button').click(); await m.waitForTimeout(500);
  await foto(m, '09-celular-menu');
  await ir(m, '/ordenes/3');
  await foto(m, '38-celular-orden');

  console.log(errores.length ? 'ERRORES JS:\n' + errores.join('\n') : 'Sin errores de JS.');
  await b.close();
})().catch((e) => { console.error(e); process.exit(1); });

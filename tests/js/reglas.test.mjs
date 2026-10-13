// Las reglas del navegador (public/assets/js/reglas.js) contra los casos de tests/casos/, los
// mismos que prueba tests/Unit/CasosCompartidosTest.php contra el servidor.
// Uso: node --test "tests/js/*.test.mjs"
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const reglas = require('../../public/assets/js/reglas.js');
const casos = (archivo) => JSON.parse(readFileSync(new URL(`../casos/${archivo}.json`, import.meta.url), 'utf8'));
const nombre = (c) => `«${c.entrada}»${c.nota ? ` (${c.nota})` : ''}`;

const importes = casos('importes');
const telefonos = casos('telefonos');
const patentes = casos('patentes');

for (const c of importes.importe) {
  test(`importe ${nombre(c)}`, () => {
    const leido = reglas.leerImporte(c.entrada);
    // El servidor redondea a 2 decimales; null es "no es un número" (NaN).
    assert.deepEqual(Number.isNaN(leido) ? null : Math.round(leido * 100) / 100, c.esperado);
  });
}
for (const c of importes.cantidadAmbigua) {
  test(`cantidad ambigua ${nombre(c)}`, () => assert.equal(reglas.cantidadAmbigua(c.entrada), c.esperado));
}
for (const c of telefonos.normalizar) {
  test(`teléfono ${nombre(c)}`, () => assert.equal(reglas.normalizarTelefono(c.entrada), c.esperado));
}
for (const c of telefonos.valido) {
  test(`teléfono válido ${nombre(c)}`, () => assert.equal(reglas.telefonoValido(c.entrada), c.esperado));
}
for (const c of patentes.normalizar) {
  test(`patente ${nombre(c)}`, () => assert.equal(reglas.normalizarPatente(c.entrada), c.esperado));
}
for (const c of patentes.valida) {
  test(`patente válida ${nombre(c)}`, () => assert.equal(reglas.patenteValida(c.entrada), c.esperado));
}

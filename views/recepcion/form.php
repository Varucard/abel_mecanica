<?php
/**
 * "Llegó un auto": patente → (si es nuevo: dueño y auto) → motivo → orden abierta.
 *
 * El formulario cambia según lo que se sabe de la patente (data-estado del <form>):
 *   sin-buscar  todavía no se buscó: solo se ve el paso 1
 *   conocido    el auto ya está cargado: se pasa directo al motivo
 *   nuevo       primera vez: se piden el dueño y el auto
 * Los datos de un auto nuevo van en un <fieldset> que se deshabilita cuando no hacen falta:
 * así no se validan ni se envían.
 *
 * @var array<string, mixed>|null $turno
 * @var string $patente
 * @var array{patente: string, valida: bool, vehiculo: array<string, mixed>|null, abiertas: list<array<string, mixed>>}|null $busqueda
 * @var array<string, mixed>|null $clienteDni  cliente encontrado por el DNI que volvió con errores
 * @var list<array<string, mixed>> $marcas
 * @var list<array<string, mixed>> $modelos
 * @var list<array<string, mixed>> $mecanicos
 */
use App\Services\VehiculoService;

$view->script('vehiculos.js');
$view->script('recepcion.js');

$vehiculo = $busqueda['vehiculo'] ?? null;
$estado = match (true) {
  $busqueda === null || !$busqueda['valida'] => 'sin-buscar',
  $vehiculo !== null => 'conocido',
  default => 'nuevo',
};
$marcaId = (int) old('marca_id', 0);
$modeloId = (int) old('modelo_id', 0);
$modeloNuevo = str_starts_with((string) old('modelo_id', ''), VehiculoService::MODELO_NUEVO) ? (string) old('modelo_id') : null;
?>
<form action="<?= url('recepcion') ?>" method="POST" id="form_recepcion" class="recepcion mt-3" data-borrador data-estado="<?= e($estado) ?>"
  data-patente-url="<?= e(url('recepcion/patente')) ?>" data-cliente-url="<?= e(url('recepcion/cliente')) ?>"
  data-modelos-url="<?= e(url('marcas/{id}/modelos')) ?>">
  <?= csrf_field() ?>

  <?php if ($turno): ?>
    <input type="hidden" name="turno_id" value="<?= (int) $turno['id'] ?>">
    <div class="alert alert-info d-flex gap-2 align-items-center">
      <?= icono('calendar-check') ?>
      <span>Viene por el turno del <?= format_date($turno['fecha']) ?> a las <?= e(substr($turno['hora'], 0, 5)) ?>. Al recibirlo, el turno queda como <strong>realizado</strong>.</span>
    </div>
  <?php endif; ?>

  <section class="paso">
    <h2 class="paso-titulo"><span class="paso-numero">1</span> ¿Qué patente tiene el auto?</h2>
    <div class="input-group input-group-lg ancho-max-420">
      <input type="text" class="form-control text-uppercase" id="patente" name="patente" value="<?= e($patente) ?>" required autocomplete="off"
        maxlength="10" placeholder="AB 123 CD" aria-label="Patente" <?= $estado === 'sin-buscar' ? 'autofocus' : '' ?>
        pattern="\s*[A-Za-z]{2}[\s.\-]*[0-9]{3}[\s.\-]*[A-Za-z]{2}\s*|\s*[A-Za-z]{3}[\s.\-]*[0-9]{3}\s*"
        data-error="La patente tiene que ser como AB 123 CD o ABC 123.">
      <button type="button" class="btn btn-seccion js-buscar-patente"><?= icono('search') ?> Buscar</button>
    </div>

    <div id="resultado_patente" class="mt-3" aria-live="polite">
      <?php if ($vehiculo): ?>
        <div class="alert alert-success mb-0">
          <?= icono('check-circle-fill') ?> Ya lo conocemos: <strong><?= e("{$vehiculo['marca']} {$vehiculo['modelo']} {$vehiculo['anio']}") ?></strong>
          de <strong><?= e($vehiculo['cliente']) ?></strong>.
          <a href="<?= url("vehiculos/{$vehiculo['id']}") ?>">Ver ficha</a>
        </div>
        <?php if ($busqueda['abiertas'] !== []): ?>
          <?php $abierta = $busqueda['abiertas'][0]; ?>
          <div class="alert alert-warning mt-2 mb-0 d-flex flex-wrap gap-2 align-items-center">
            <span class="me-auto"><?= icono('exclamation-triangle') ?> Este auto ya está en el taller: tiene la orden #<?= (int) $abierta['id'] ?> abierta.</span>
            <a href="<?= url("ordenes/{$abierta['id']}") ?>" class="btn btn-primary"><?= icono('arrow-right') ?> Seguir con la orden #<?= (int) $abierta['id'] ?></a>
          </div>
        <?php endif; ?>
      <?php elseif ($estado === 'nuevo'): ?>
        <div class="alert alert-info mb-0"><?= icono('info-circle') ?> Es la primera vez que viene este auto: completá los datos del dueño y del auto.</div>
      <?php endif; ?>
    </div>
  </section>

  <fieldset id="datos_nuevos" <?= $estado === 'nuevo' ? '' : 'disabled hidden' ?>>
    <section class="paso">
      <h2 class="paso-titulo"><span class="paso-numero">2</span> ¿De quién es?</h2>
      <div class="row">
        <?= $view->partial('componentes/campo', [
          'nombre' => 'dni', 'etiqueta' => 'DNI del dueño *', 'columna' => 'col-md-4 mb-3',
          'atributos' => ['inputmode' => 'numeric', 'pattern' => '\s*[0-9.\s]{6,10}\s*', 'maxlength' => 10, 'required' => true,
            'data-error' => 'El DNI va solo con números (6 a 8 dígitos).'],
          'ayuda' => 'Si ya es cliente, lo encontramos con el DNI.',
        ]) ?>
        <div class="col-md-8 mb-3 d-flex align-items-center" id="cliente_encontrado" aria-live="polite">
          <?php if ($clienteDni): ?>
            <div class="alert alert-success mb-0 w-100"><?= icono('check-circle-fill') ?> Ya es cliente: <strong><?= e("{$clienteDni['apellido']}, {$clienteDni['nombre']}") ?></strong></div>
          <?php endif; ?>
        </div>
      </div>
      <fieldset id="cliente_nuevo" class="row" <?= $clienteDni ? 'disabled hidden' : '' ?>>
        <?= $view->partial('componentes/campo', [
          'nombre' => 'nombre', 'etiqueta' => 'Nombre *', 'columna' => 'col-md-6 mb-3', 'atributos' => ['maxlength' => 50, 'required' => true, 'autocomplete' => 'off'],
        ]) ?>
        <?= $view->partial('componentes/campo', [
          'nombre' => 'apellido', 'etiqueta' => 'Apellido *', 'columna' => 'col-md-6 mb-3', 'atributos' => ['maxlength' => 50, 'required' => true, 'autocomplete' => 'off'],
        ]) ?>
        <?= $view->partial('componentes/campo', [
          'nombre' => 'telefono', 'etiqueta' => 'Teléfono *', 'tipo' => 'tel', 'columna' => 'col-md-6 mb-3',
          'atributos' => ['inputmode' => 'tel', 'maxlength' => 20, 'placeholder' => 'Ej: 11 2345-6789', 'required' => true, 'data-telefono' => true],
          'ayuda' => 'Con el código de área. Puede ir con 0, con 15, con espacios o guiones.',
        ]) ?>
        <?= $view->partial('componentes/campo', [
          'nombre' => 'email', 'etiqueta' => 'Email (si tiene)', 'tipo' => 'email', 'columna' => 'col-md-6 mb-3',
          'atributos' => ['maxlength' => 255, 'placeholder' => 'ejemplo@correo.com'],
          'ayuda' => 'Sirve para mandarle el presupuesto y avisarle cuando está listo.',
        ]) ?>
      </fieldset>
    </section>

    <section class="paso">
      <h2 class="paso-titulo"><span class="paso-numero">3</span> ¿Qué auto es?</h2>
      <div class="row">
        <div class="col-md-5 mb-3">
          <label for="marca_id" class="form-label">Marca *</label>
          <select class="form-select js-buscable" id="marca_id" name="marca_id" data-crear="<?= e(\App\Services\VehiculoService::MODELO_NUEVO) ?>" data-placeholder="Elegí o escribí la marca" required>
            <option value=""></option>
            <?php foreach ($marcas as $m): ?>
              <option value="<?= (int) $m['id'] ?>" <?= selected($marcaId === (int) $m['id']) ?>><?= e($m['nombre']) ?></option>
            <?php endforeach; ?>
            <?php if (str_starts_with((string) old('marca_id', ''), \App\Services\VehiculoService::MODELO_NUEVO)): ?>
              <option value="<?= e((string) old('marca_id')) ?>" selected><?= e(substr((string) old('marca_id'), strlen(\App\Services\VehiculoService::MODELO_NUEVO))) ?></option>
            <?php endif; ?>
          </select>
        </div>
        <div class="col-md-4 mb-3">
          <label for="modelo_id" class="form-label">Modelo *</label>
          <select class="form-select js-buscable" id="modelo_id" name="modelo_id" data-crear="<?= e(VehiculoService::MODELO_NUEVO) ?>"
            <?php $hayMarca = $marcaId > 0 || str_starts_with((string) old('marca_id', ''), \App\Services\VehiculoService::MODELO_NUEVO); ?>
            data-placeholder="<?= $hayMarca ? 'Elegí o escribí el modelo' : 'Primero elegí la marca' ?>" required <?= $hayMarca ? '' : 'disabled' ?>>
            <option value=""></option>
            <?php foreach ($modelos as $mo): ?>
              <option value="<?= (int) $mo['id'] ?>" <?= selected($modeloId === (int) $mo['id']) ?>><?= e($mo['nombre']) ?></option>
            <?php endforeach; ?>
            <?php if ($modeloNuevo): ?>
              <option value="<?= e($modeloNuevo) ?>" selected><?= e(substr($modeloNuevo, strlen(VehiculoService::MODELO_NUEVO))) ?></option>
            <?php endif; ?>
          </select>
          <small class="form-text text-muted">Si no está, escribilo y elegí «Agregar».</small>
        </div>
        <?= $view->partial('componentes/campo', [
          'nombre' => 'anio', 'etiqueta' => 'Año *', 'tipo' => 'number', 'columna' => 'col-md-3 mb-3',
          'atributos' => ['required' => true, 'min' => (int) $anioMinimo, 'max' => (int) $anioMaximo, 'placeholder' => 'Ej: 2018'],
        ]) ?>
      </div>
    </section>
  </fieldset>

  <div class="js-tras-buscar" <?= $estado === 'sin-buscar' ? 'hidden' : '' ?>>
    <section class="paso">
      <h2 class="paso-titulo"><span class="paso-numero js-numero-motivo"><?= $estado === 'nuevo' ? 4 : 2 ?></span> ¿Qué le pasa?</h2>
      <div class="row">
        <?= $view->partial('componentes/campo', [
          'nombre' => 'diagnostico', 'etiqueta' => 'Lo que cuenta el cliente', 'tipo' => 'textarea', 'columna' => 'col-12 mb-3',
          'valor' => $turno['descripcion'] ?? '',
          'atributos' => ['rows' => 3, 'placeholder' => 'Ej: hace ruido al frenar, service de los 10.000 km, no arranca en frío…'],
        ]) ?>
        <?= $view->partial('componentes/campo', [
          'nombre' => 'km_ingreso', 'etiqueta' => 'Kilómetros que marca', 'tipo' => 'number', 'columna' => 'col-md-4 mb-3',
          'atributos' => ['min' => 0, 'max' => 9999999, 'inputmode' => 'numeric',
            'placeholder' => isset($vehiculo['kilometraje']) ? 'La vez anterior: ' . number_format((float) $vehiculo['kilometraje'], 0, ',', '.') : 'Ej: 125000'],
        ]) ?>
        <?php if ($mecanicos !== []): ?>
          <?= $view->partial('componentes/campo', [
            'nombre' => 'mecanico_id', 'etiqueta' => '¿Quién lo va a atender?', 'tipo' => 'select', 'columna' => 'col-md-8 mb-3',
            'opciones' => ['' => 'Todavía no sé'] + array_column(array_map(fn($m) => [
              (int) $m['id'], trim("{$m['apellido']}" . ($m['nombre'] ? ", {$m['nombre']}" : '')),
            ], $mecanicos), 1, 0),
          ]) ?>
        <?php endif; ?>
      </div>
      <p class="small text-muted">Los servicios y repuestos se cargan después, en la orden, cuando ya se sabe qué hay que hacer.</p>
    </section>

    <?php // Si el auto ya tiene una orden abierta, abrir otra es la opción secundaria y se confirma. ?>
    <?php $hayAbierta = ($busqueda['abiertas'] ?? []) !== []; ?>
    <?php // Vacío siempre: lo completa recepcion.js con el N° de la orden abierta, recién cuando se confirma. ?>
    <input type="hidden" name="confirmar_otra" id="confirmar_otra" value="">
    <div class="d-flex flex-wrap gap-2">
      <button type="submit" class="btn btn-lg js-recibir <?= $hayAbierta ? 'btn-outline-secondary' : 'btn-seccion' ?>"
        data-texto-normal="Recibir el auto" data-texto-otra="Abrir otra orden igual"
        <?= $hayAbierta ? 'data-abierta="' . (int) max(array_column($busqueda['abiertas'], 'id')) . '"' : '' ?>>
        <?= icono('check-lg') ?> <span class="js-recibir-texto"><?= $hayAbierta ? 'Abrir otra orden igual' : 'Recibir el auto' ?></span>
      </button>
      <a href="<?= url('/') ?>" class="btn btn-outline-secondary btn-lg">Cancelar</a>
    </div>
  </div>
</form>

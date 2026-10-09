<?php
/**
 * @var array<string, mixed>|null $vehiculo
 * @var list<array<string, mixed>> $clientes
 * @var list<array<string, mixed>> $marcas
 * @var list<array<string, mixed>> $modelos
 */
$clienteId = (int) old('cliente_id', $vehiculo['cliente_id'] ?? $clienteSugerido);
$marcaId = (int) old('marca_id', $vehiculo['marca_id'] ?? 0);
$modeloId = (int) old('modelo_id', $vehiculo['modelo_id'] ?? 0);
// Modelo escrito a mano que volvió con errores: se vuelve a mostrar elegido.
$modeloNuevo = str_starts_with((string) old('modelo_id', ''), \App\Services\VehiculoService::MODELO_NUEVO) ? (string) old('modelo_id') : null;
$view->script('vehiculos.js');
?>
<?php if (!empty($paraTurno)): ?>
  <div class="alert alert-info mt-3 mb-0"><?= icono('calendar-plus') ?> Turno para un cliente nuevo · paso 2 de 3: el auto. Al guardar, volvés al turno con el cliente ya elegido.</div>
<?php endif; ?>
<div class="card mt-3">
  <div class="card-header">
    <h4 class="mb-0"><?= $vehiculo ? 'Editar vehículo' : 'Nuevo vehículo' ?></h4>
  </div>
  <div class="card-body">
    <form action="<?= url($vehiculo ? "vehiculos/{$vehiculo['id']}" : 'vehiculos') ?>" method="POST" id="form_vehiculo" data-borrador
      data-nuevo="<?= $vehiculo ? '0' : '1' ?>"
      data-vehiculos-url="<?= e(url('clientes/{id}/vehiculos')) ?>"
      data-modelos-url="<?= e(url('marcas/{id}/modelos')) ?>">
      <?= csrf_field() ?>
      <?php if (!empty($paraTurno)): ?><input type="hidden" name="para" value="turno"><?php endif; ?>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="cliente_id" class="form-label">Cliente *</label>
          <select class="form-select js-buscable" id="cliente_id" name="cliente_id" data-placeholder="Escribí el apellido o el DNI" required>
            <option value=""></option>
            <?php foreach ($clientes as $c): ?>
              <option value="<?= (int) $c['id'] ?>" <?= selected($clienteId === (int) $c['id']) ?>>
                <?= e("{$c['apellido']}, {$c['nombre']} - DNI: {$c['dni']}") ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
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
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="modelo_id" class="form-label">Modelo *</label>
          <select class="form-select js-buscable" id="modelo_id" name="modelo_id" data-crear="<?= e(\App\Services\VehiculoService::MODELO_NUEVO) ?>"
            <?php $hayMarca = $marcaId > 0 || str_starts_with((string) old('marca_id', ''), \App\Services\VehiculoService::MODELO_NUEVO); ?>
            data-placeholder="<?= $hayMarca ? 'Elegí o escribí el modelo' : 'Primero elegí la marca' ?>" required <?= $hayMarca ? '' : 'disabled' ?>>
            <option value=""></option>
            <?php foreach ($modelos as $mo): ?>
              <option value="<?= (int) $mo['id'] ?>" <?= selected($modeloId === (int) $mo['id']) ?>><?= e($mo['nombre']) ?></option>
            <?php endforeach; ?>
            <?php if ($modeloNuevo): ?>
              <option value="<?= e($modeloNuevo) ?>" selected><?= e(substr($modeloNuevo, strlen(\App\Services\VehiculoService::MODELO_NUEVO))) ?></option>
            <?php endif; ?>
          </select>
          <small class="form-text text-muted">Si no está en la lista, escribilo y elegí «Agregar».</small>
        </div>
        <?= $view->partial('componentes/campo', [
          'nombre' => 'anio', 'etiqueta' => 'Año *', 'tipo' => 'number', 'valor' => $vehiculo['anio'] ?? '', 'columna' => 'col-md-3',
          'atributos' => ['required' => true, 'min' => (int) $anioMinimo, 'max' => (int) $anioMaximo],
        ]) ?>
        <?= $view->partial('componentes/campo', [
          'nombre' => 'patente', 'etiqueta' => 'Patente *', 'valor' => $vehiculo['patente'] ?? '', 'columna' => 'col-md-3', 'clase' => 'text-uppercase',
          'atributos' => ['required' => true, 'maxlength' => 10, 'pattern' => '\s*[A-Za-z]{2}[\s.\-]*[0-9]{3}[\s.\-]*[A-Za-z]{2}\s*|\s*[A-Za-z]{3}[\s.\-]*[0-9]{3}\s*',
            'placeholder' => 'AB 123 CD', 'data-error' => 'La patente tiene que ser como AB 123 CD o ABC 123.'],
        ]) ?>
      </div>

      <div class="row mb-3">
        <?= $view->partial('componentes/campo', [
          'nombre' => 'kilometraje', 'etiqueta' => 'Kilometraje', 'tipo' => 'number', 'valor' => $vehiculo['kilometraje'] ?? '', 'columna' => 'col-md-3',
          'atributos' => ['min' => 0, 'max' => 9999999, 'step' => 1, 'placeholder' => 'Ej: 125000'],
        ]) ?>
        <?= $view->partial('componentes/campo', [
          'nombre' => 'motor', 'etiqueta' => 'Motor', 'valor' => $vehiculo['motor'] ?? '', 'columna' => 'col-md-3',
          'atributos' => ['maxlength' => 50, 'placeholder' => 'Ej: 1.6 16v'],
        ]) ?>
        <?= $view->partial('componentes/campo', [
          'nombre' => 'combustible', 'etiqueta' => 'Combustible', 'tipo' => 'select', 'valor' => $vehiculo['combustible'] ?? '', 'columna' => 'col-md-3',
          'opciones' => ['' => '—'] + array_combine(
            array_map(fn($c) => $c->value, $combustibles),
            array_map(fn($c) => $c->label(), $combustibles),
          ),
        ]) ?>
        <?= $view->partial('componentes/campo', [
          'nombre' => 'color', 'etiqueta' => 'Color', 'valor' => $vehiculo['color'] ?? '', 'columna' => 'col-md-3',
          'atributos' => ['maxlength' => 30],
        ]) ?>
      </div>

      <div class="row mb-3">
        <?= $view->partial('componentes/campo', [
          'nombre' => 'numero_chasis', 'etiqueta' => 'N° de chasis (VIN)', 'valor' => $vehiculo['numero_chasis'] ?? '', 'columna' => 'col-md-4', 'clase' => 'text-uppercase',
          'atributos' => ['maxlength' => 17],
        ]) ?>
        <?= $view->partial('componentes/campo', [
          'nombre' => 'detalle', 'etiqueta' => 'Observaciones', 'tipo' => 'textarea', 'valor' => $vehiculo['detalle'] ?? '', 'columna' => 'col-md-8',
          'atributos' => ['rows' => 1, 'placeholder' => 'Ej: golpe en paragolpes trasero, usa aceite sintético'],
        ]) ?>
      </div>

      <button type="submit" class="btn btn-seccion"><?= icono('check-lg') ?> <?= $vehiculo ? 'Guardar cambios' : 'Guardar vehículo' ?></button>
      <a href="<?= url($vehiculo ? "vehiculos/{$vehiculo['id']}" : 'vehiculos') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </form>
  </div>
</div>

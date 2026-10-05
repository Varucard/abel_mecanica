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
$view->script('vehiculos.js');
?>
<div class="card mt-3">
  <div class="card-header">
    <h4 class="mb-0"><?= $vehiculo ? 'Editar vehículo' : 'Nuevo vehículo' ?></h4>
  </div>
  <div class="card-body">
    <form action="<?= url($vehiculo ? "vehiculos/{$vehiculo['id']}" : 'vehiculos') ?>" method="POST" id="form_vehiculo"
      data-nuevo="<?= $vehiculo ? '0' : '1' ?>"
      data-vehiculos-url="<?= e(url('clientes/{id}/vehiculos')) ?>"
      data-modelos-url="<?= e(url('marcas/{id}/modelos')) ?>">
      <?= csrf_field() ?>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="cliente_id" class="form-label">Cliente *</label>
          <select class="form-select js-buscable" id="cliente_id" name="cliente_id" data-placeholder="Seleccione un cliente" required>
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
          <select class="form-select js-buscable" id="marca_id" name="marca_id" data-placeholder="Seleccione una marca" required>
            <option value=""></option>
            <?php foreach ($marcas as $m): ?>
              <option value="<?= (int) $m['id'] ?>" <?= selected($marcaId === (int) $m['id']) ?>><?= e($m['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="modelo_id" class="form-label">Modelo *</label>
          <select class="form-select js-buscable" id="modelo_id" name="modelo_id" data-placeholder="Seleccione primero una marca"
            required <?= $modelos === [] ? 'disabled' : '' ?>>
            <option value=""></option>
            <?php foreach ($modelos as $mo): ?>
              <option value="<?= (int) $mo['id'] ?>" <?= selected($modeloId === (int) $mo['id']) ?>><?= e($mo['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?= $view->partial('componentes/campo', [
          'nombre' => 'anio', 'etiqueta' => 'Año *', 'tipo' => 'number', 'valor' => $vehiculo['anio'] ?? '', 'columna' => 'col-md-3',
          'atributos' => ['required' => true, 'min' => (int) $anioMinimo, 'max' => (int) $anioMaximo],
        ]) ?>
        <?= $view->partial('componentes/campo', [
          'nombre' => 'patente', 'etiqueta' => 'Patente *', 'valor' => $vehiculo['patente'] ?? '', 'columna' => 'col-md-3', 'clase' => 'text-uppercase',
          'atributos' => ['required' => true, 'maxlength' => 7, 'pattern' => '[A-Za-z]{2}[0-9]{3}[A-Za-z]{2}|[A-Za-z]{3}[0-9]{3}', 'title' => 'Formato: AB123CD o ABC123'],
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

      <button type="submit" class="btn btn-seccion"><?= icono('check-lg') ?> <?= $vehiculo ? 'Actualizar' : 'Registrar' ?> vehículo</button>
      <a href="<?= url('vehiculos') ?>" class="btn btn-outline-secondary">Ver vehículos registrados</a>
    </form>
  </div>
</div>

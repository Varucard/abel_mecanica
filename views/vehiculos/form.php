<?php
/**
 * @var array<string, mixed>|null $vehiculo
 * @var list<array<string, mixed>> $clientes
 * @var list<array<string, mixed>> $marcas
 * @var list<array<string, mixed>> $modelos
 */
$clienteId = (int) old('cliente_id', $vehiculo['cliente_id'] ?? 0);
$marcaId = (int) old('marca_id', $vehiculo['marca_id'] ?? 0);
$modeloId = (int) old('modelo_id', $vehiculo['modelo_id'] ?? 0);
$view->script('vehiculos.js');
?>
<div class="card mt-3">
  <div class="card-header bg-light">
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
          <select class="form-select js-select2" id="cliente_id" name="cliente_id" data-placeholder="Seleccione un cliente" required>
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
          <select class="form-select js-select2" id="marca_id" name="marca_id" data-placeholder="Seleccione una marca" required>
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
          <select class="form-select js-select2" id="modelo_id" name="modelo_id" data-placeholder="Seleccione primero una marca"
            required <?= $modelos === [] ? 'disabled' : '' ?>>
            <option value=""></option>
            <?php foreach ($modelos as $mo): ?>
              <option value="<?= (int) $mo['id'] ?>" <?= selected($modeloId === (int) $mo['id']) ?>><?= e($mo['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label for="anio" class="form-label">Año *</label>
          <input type="number" class="form-control" id="anio" name="anio" required
            min="<?= (int) $anioMinimo ?>" max="<?= (int) $anioMaximo ?>"
            value="<?= e(old('anio', $vehiculo['anio'] ?? '')) ?>">
        </div>
        <div class="col-md-3">
          <label for="patente" class="form-label">Patente *</label>
          <input type="text" class="form-control text-uppercase" id="patente" name="patente" required maxlength="7"
            pattern="[A-Za-z]{2}[0-9]{3}[A-Za-z]{2}|[A-Za-z]{3}[0-9]{3}" title="Formato: AB123CD o ABC123"
            value="<?= e(old('patente', $vehiculo['patente'] ?? '')) ?>">
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="kilometraje" class="form-label">Kilometraje (opcional)</label>
          <input type="number" class="form-control" id="kilometraje" name="kilometraje" min="0" max="9999999" step="1"
            placeholder="Ej: 125000" value="<?= e(old('kilometraje', $vehiculo['kilometraje'] ?? '')) ?>">
        </div>
      </div>

      <button type="submit" class="btn btn-info"><?= $vehiculo ? 'Actualizar' : 'Registrar' ?> vehículo</button>
      <a href="<?= url('vehiculos') ?>" class="btn btn-secondary">Ver vehículos registrados</a>
    </form>
  </div>
</div>

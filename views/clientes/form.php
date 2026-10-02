<?php /** @var array<string, mixed>|null $cliente */ ?>
<div class="card mt-3">
  <div class="card-header bg-light">
    <h4 class="mb-0"><?= $cliente ? 'Editar cliente' : 'Nuevo cliente' ?></h4>
  </div>
  <div class="card-body">
    <form action="<?= url($cliente ? "clientes/{$cliente['id']}" : 'clientes') ?>" method="POST">
      <?= csrf_field() ?>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="nombre" class="form-label">Nombre *</label>
          <input type="text" class="form-control" id="nombre" name="nombre" maxlength="50" required
            value="<?= e(old('nombre', $cliente['nombre'] ?? '')) ?>">
        </div>
        <div class="col-md-6">
          <label for="apellido" class="form-label">Apellido *</label>
          <input type="text" class="form-control" id="apellido" name="apellido" maxlength="50" required
            value="<?= e(old('apellido', $cliente['apellido'] ?? '')) ?>">
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="dni" class="form-label">DNI *</label>
          <input type="text" class="form-control" id="dni" name="dni" inputmode="numeric"
            pattern="[0-9]{6,8}" maxlength="8" required <?= $cliente ? 'readonly' : '' ?>
            value="<?= e(old('dni', $cliente['dni'] ?? '')) ?>">
          <?php if ($cliente): ?>
            <small class="form-text text-muted">El DNI no se puede modificar.</small>
          <?php endif; ?>
        </div>
        <div class="col-md-6">
          <label for="telefono" class="form-label">Teléfono *</label>
          <input type="tel" class="form-control" id="telefono" name="telefono" inputmode="numeric"
            pattern="[0-9]{10}" maxlength="10" placeholder="1123456789" required
            value="<?= e(old('telefono', $cliente['telefono'] ?? '')) ?>">
          <small class="form-text text-muted">10 dígitos: código de área + número, sin 0 ni 15.</small>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="email" class="form-label">Email (opcional)</label>
          <input type="email" class="form-control" id="email" name="email" maxlength="255"
            placeholder="ejemplo@correo.com" value="<?= e(old('email', $cliente['email'] ?? '')) ?>">
        </div>
        <div class="col-md-6">
          <label for="direccion" class="form-label">Dirección (opcional)</label>
          <input type="text" class="form-control" id="direccion" name="direccion" minlength="5" maxlength="200"
            value="<?= e(old('direccion', $cliente['direccion'] ?? '')) ?>">
        </div>
      </div>

      <button type="submit" class="btn btn-success"><?= $cliente ? 'Actualizar cliente' : 'Registrar cliente' ?></button>
      <a href="<?= url('clientes') ?>" class="btn btn-secondary">Volver al listado</a>
    </form>
  </div>
</div>

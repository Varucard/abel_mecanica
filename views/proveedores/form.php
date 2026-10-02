<?php
/** @var array<string, mixed>|null $proveedor */
$activo = $proveedor === null || (session()->hasOldInput() ? old('activo') : $proveedor['activo']);
$campo = fn(string $k) => e(old($k, $proveedor[$k] ?? ''));
?>
<div class="card mt-3">
  <div class="card-header bg-light">
    <h4 class="mb-0"><?= $proveedor ? 'Editar proveedor' : 'Nuevo proveedor' ?></h4>
  </div>
  <div class="card-body">
    <form action="<?= url($proveedor ? "proveedores/{$proveedor['id']}" : 'proveedores') ?>" method="POST">
      <?= csrf_field() ?>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="nombre" class="form-label">Nombre / razón social *</label>
          <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" required value="<?= $campo('nombre') ?>">
        </div>
        <div class="col-md-3 mb-3">
          <label for="cuit" class="form-label">CUIT</label>
          <input type="text" class="form-control" id="cuit" name="cuit" maxlength="13" placeholder="30-12345678-9" value="<?= $campo('cuit') ?>">
        </div>
        <div class="col-md-3 mb-3">
          <label for="contacto" class="form-label">Persona de contacto</label>
          <input type="text" class="form-control" id="contacto" name="contacto" maxlength="100" value="<?= $campo('contacto') ?>">
        </div>
        <div class="col-md-3 mb-3">
          <label for="telefono" class="form-label">Teléfono</label>
          <input type="tel" class="form-control" id="telefono" name="telefono" maxlength="30" value="<?= $campo('telefono') ?>">
        </div>
        <div class="col-md-4 mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" maxlength="255" value="<?= $campo('email') ?>">
        </div>
        <div class="col-md-5 mb-3">
          <label for="direccion" class="form-label">Dirección</label>
          <input type="text" class="form-control" id="direccion" name="direccion" maxlength="200" value="<?= $campo('direccion') ?>">
        </div>
      </div>
      <div class="mb-3">
        <label for="observaciones" class="form-label">Observaciones</label>
        <textarea class="form-control" id="observaciones" name="observaciones" rows="2"><?= $campo('observaciones') ?></textarea>
      </div>
      <?php if ($proveedor): ?>
        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1" <?= $activo ? 'checked' : '' ?>>
          <label class="form-check-label" for="activo">Proveedor activo</label>
        </div>
      <?php endif; ?>
      <button type="submit" class="btn btn-primary"><?= $proveedor ? 'Guardar cambios' : 'Registrar proveedor' ?></button>
      <a href="<?= url('proveedores') ?>" class="btn btn-secondary">Volver</a>
    </form>
  </div>
</div>

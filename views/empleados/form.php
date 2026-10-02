<?php
/** @var array<string, mixed>|null $empleado */
$campo = fn(string $k) => e(old($k, $empleado[$k] ?? ''));
?>
<div class="card mt-3">
  <div class="card-header bg-light">
    <h4 class="mb-0"><?= $empleado ? 'Editar empleado' : 'Nuevo empleado' ?></h4>
  </div>
  <div class="card-body">
    <form action="<?= url($empleado ? "empleados/{$empleado['id']}" : 'empleados') ?>" method="POST">
      <?= csrf_field() ?>
      <div class="row">
        <div class="col-md-4 mb-3">
          <label for="nombre" class="form-label">Nombre *</label>
          <input type="text" class="form-control" id="nombre" name="nombre" maxlength="50" required value="<?= $campo('nombre') ?>">
        </div>
        <div class="col-md-4 mb-3">
          <label for="apellido" class="form-label">Apellido *</label>
          <input type="text" class="form-control" id="apellido" name="apellido" maxlength="50" required value="<?= $campo('apellido') ?>">
        </div>
        <div class="col-md-4 mb-3">
          <label for="dni" class="form-label">DNI *</label>
          <input type="text" class="form-control" id="dni" name="dni" inputmode="numeric" pattern="[0-9]{6,8}" maxlength="8" required
            <?= $empleado ? 'readonly' : '' ?> value="<?= $campo('dni') ?>">
        </div>
        <div class="col-md-4 mb-3">
          <label for="puesto" class="form-label">Puesto *</label>
          <input type="text" class="form-control" id="puesto" name="puesto" maxlength="50" required list="puestos" value="<?= $campo('puesto') ?>">
          <datalist id="puestos">
            <option value="Mecánico"><option value="Ayudante"><option value="Electricista"><option value="Administrativo">
          </datalist>
        </div>
        <div class="col-md-4 mb-3">
          <label for="telefono" class="form-label">Teléfono</label>
          <input type="tel" class="form-control" id="telefono" name="telefono" inputmode="numeric" maxlength="10" value="<?= $campo('telefono') ?>">
        </div>
        <div class="col-md-4 mb-3">
          <label for="fecha_ingreso" class="form-label">Fecha de ingreso</label>
          <input type="date" class="form-control" id="fecha_ingreso" name="fecha_ingreso" max="<?= date('Y-m-d') ?>" value="<?= $campo('fecha_ingreso') ?>">
        </div>
        <div class="col-md-6 mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" maxlength="255" value="<?= $campo('email') ?>">
        </div>
      </div>
      <button type="submit" class="btn btn-primary"><?= $empleado ? 'Guardar cambios' : 'Registrar empleado' ?></button>
      <a href="<?= url('empleados') ?>" class="btn btn-secondary">Volver</a>
    </form>
  </div>
</div>

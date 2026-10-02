<?php
/**
 * @var array<string, mixed>|null $usuario
 * @var list<\App\Enums\Rol> $roles
 */
$rolActual = old('rol', $usuario['rol'] ?? 'empleado');
$activo = $usuario === null || (session()->hasOldInput() ? old('activo') : $usuario['activo']);
?>
<div class="card mt-3">
  <div class="card-header bg-light">
    <h4 class="mb-0"><?= $usuario ? 'Editar usuario' : 'Nuevo usuario' ?></h4>
  </div>
  <div class="card-body">
    <form action="<?= url($usuario ? "usuarios/{$usuario['id']}" : 'usuarios') ?>" method="POST">
      <?= csrf_field() ?>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="nombre" class="form-label">Nombre y apellido *</label>
          <input type="text" class="form-control" id="nombre" name="nombre" required value="<?= e(old('nombre', $usuario['nombre'] ?? '')) ?>">
        </div>
        <div class="col-md-6 mb-3">
          <label for="usuario" class="form-label">Usuario *</label>
          <input type="text" class="form-control" id="usuario" name="usuario" required autocomplete="off"
            value="<?= e(old('usuario', $usuario['usuario'] ?? '')) ?>">
        </div>
        <div class="col-md-6 mb-3">
          <label for="rol" class="form-label">Rol *</label>
          <select class="form-select" id="rol" name="rol">
            <?php foreach ($roles as $rol): ?>
              <option value="<?= e($rol->value) ?>" <?= selected($rol->value === $rolActual) ?>><?= e($rol->label()) ?></option>
            <?php endforeach; ?>
          </select>
          <small class="form-text text-muted">Los administradores además gestionan usuarios y la configuración del sistema.</small>
        </div>
        <?php if ($usuario): ?>
          <div class="col-md-6 mb-3 d-flex align-items-center">
            <div class="form-check form-switch mt-3">
              <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1" <?= $activo ? 'checked' : '' ?>>
              <label class="form-check-label" for="activo">Usuario activo (puede ingresar)</label>
            </div>
          </div>
        <?php endif; ?>
      </div>
      <?= $view->partial('usuarios/_campos_clave', ['requerida' => $usuario === null]) ?>
      <button type="submit" class="btn btn-primary"><?= $usuario ? 'Guardar cambios' : 'Crear usuario' ?></button>
      <a href="<?= url('usuarios') ?>" class="btn btn-secondary">Volver</a>
    </form>
  </div>
</div>

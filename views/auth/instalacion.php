<p class="text-muted">No hay usuarios todavía. Creá la cuenta del administrador del sistema.</p>
<form action="<?= url('instalacion') ?>" method="POST">
  <?= csrf_field() ?>
  <div class="mb-3">
    <label for="nombre" class="form-label">Nombre y apellido *</label>
    <input type="text" class="form-control" id="nombre" name="nombre" required value="<?= e(old('nombre')) ?>">
  </div>
  <div class="mb-3">
    <label for="usuario" class="form-label">Usuario *</label>
    <input type="text" class="form-control" id="usuario" name="usuario" autocomplete="username" required value="<?= e(old('usuario')) ?>">
  </div>
  <?= $view->partial('usuarios/_campos_clave', ['requerida' => true]) ?>
  <button type="submit" class="btn btn-primary w-100">Crear administrador</button>
</form>

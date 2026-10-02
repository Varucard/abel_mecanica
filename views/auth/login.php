<form action="<?= url('login') ?>" method="POST">
  <?= csrf_field() ?>
  <div class="mb-3">
    <label for="usuario" class="form-label">Usuario</label>
    <input type="text" class="form-control" id="usuario" name="usuario" autocomplete="username" required autofocus
      value="<?= e(old('usuario')) ?>">
  </div>
  <div class="mb-3">
    <label for="clave" class="form-label">Contraseña</label>
    <input type="password" class="form-control" id="clave" name="clave" autocomplete="current-password" required>
  </div>
  <button type="submit" class="btn btn-primary w-100">Ingresar</button>
</form>

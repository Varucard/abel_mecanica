<?php /** @var array<string, mixed> $valores */ $v = fn(string $k) => e(old($k, $valores[$k])); ?>
<div class="row">
  <div class="col-md-6 mb-3">
    <label for="nombre" class="form-label">Nombre del taller *</label>
    <input type="text" class="form-control" id="nombre" name="nombre" required value="<?= $v('nombre') ?>">
  </div>
  <div class="col-md-6 mb-3">
    <label for="cuit" class="form-label">CUIT *</label>
    <input type="text" class="form-control" id="cuit" name="cuit" required pattern="[0-9]{2}-?[0-9]{8}-?[0-9]" title="Formato: 20-12345678-9" value="<?= $v('cuit') ?>">
  </div>
  <div class="col-12 mb-3">
    <label for="direccion" class="form-label">Dirección *</label>
    <input type="text" class="form-control" id="direccion" name="direccion" required value="<?= $v('direccion') ?>">
  </div>
  <div class="col-md-4 mb-3">
    <label for="telefono" class="form-label">Teléfono *</label>
    <input type="text" class="form-control" id="telefono" name="telefono" required value="<?= $v('telefono') ?>">
  </div>
  <div class="col-md-4 mb-3">
    <label for="whatsapp" class="form-label">WhatsApp *</label>
    <input type="text" class="form-control" id="whatsapp" name="whatsapp" required placeholder="+5491136359867" value="<?= $v('whatsapp') ?>">
  </div>
  <div class="col-md-4 mb-3">
    <label for="email" class="form-label">Email *</label>
    <input type="email" class="form-control" id="email" name="email" required value="<?= $v('email') ?>">
  </div>
</div>

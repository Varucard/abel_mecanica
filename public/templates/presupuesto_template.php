<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="icon" type="image/png" href="../assets/img/logo_64.png">
<title>
  Presupuesto #<?= str_pad($orden['id'], 4, '0', STR_PAD_LEFT); ?> - 
  <?= htmlspecialchars($config_taller['nombre']); ?>
</title>
<style>
  :root{
    --accent:#0d6efd;
    --text:#222;
    --muted:#666;
    --paper:#fff;
    --page-bg:#f4f6f8;
    --max-w:800px;
  }
  body{font-family:system-ui,-apple-system,Segoe UI,Roboto,"Helvetica Neue",Arial; background:var(--page-bg); color:var(--text); padding:20px; display:flex; justify-content:center;}
  .card{width:100%; max-width:var(--max-w); background:var(--paper); border-radius:8px; box-shadow:0 6px 18px rgba(10,10,10,0.08); padding:20px; box-sizing:border-box;}
  header{display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:12px;}
  .brand{display:flex; gap:12px; align-items:center;}
  .brand img{height:64px; width:64px; object-fit:contain; border-radius:6px;}
  h1{font-size:20px; margin:0;}
  .meta{font-size:13px; color:var(--muted);}
  .grid{display:grid; grid-template-columns:1fr 1fr; gap:12px; margin:12px 0 18px 0;}
  .box{background:#fbfbfb; padding:12px; border-radius:6px; border:1px solid #efefef; font-size:14px;}
  table{width:100%; border-collapse:collapse; margin-top:8px;}
  th, td{padding:10px 8px; border-bottom:1px solid #eee; text-align:left; font-size:14px;}
  th{background:transparent; color:var(--muted); font-weight:600;}
  tfoot td{border-top:2px solid #ddd; font-weight:700;}
  .right{text-align:right;}
  .small{font-size:12px; color:var(--muted);}
  .notes{margin-top:14px; font-size:13px;}
  .legal{margin-top:14px; padding:10px; background:#fff8e6; border-left:4px solid #ffd54d; border-radius:4px; font-size:13px;}
  .actions{display:flex; gap:10px; margin-top:14px;}
  .btn{display:inline-block; padding:8px 12px; border-radius:6px; text-decoration:none; font-weight:600; font-size:13px;}
  .btn-primary{background:var(--accent); color:white;}
  .btn-outline{background:transparent; border:1px solid #ddd; color:var(--text);}
  /* Print-friendly */
  @media print{
    body{background:white; padding:0;}
    .card{box-shadow:none; border-radius:0; margin:0; width:100%;}
    .brand img{height:48px; width:48px;}
    .actions{display:none;}
  }
  /* Responsive */
  @media (max-width:640px){
    .grid{grid-template-columns:1fr; }
    header{gap:8px;}
    .brand img{height:48px; width:48px;}
  }
</style>
<?php if ($print_mode): ?>
<script>
  window.onload = function() { window.print(); };
</script>
<?php endif; ?>
</head>
<body>
  <?php
  // Preparar email del cliente (evitar NULL)
  $email_cliente = isset($cliente['email']) && $cliente['email'] !== null && $cliente['email'] !== ''
    ? $cliente['email']
    : '—';

  // Preparar kilometraje (evitar NULL pero permitir 0)
  if (isset($orden['kilometraje']) && is_numeric($orden['kilometraje'])) {
    $km = number_format((float)$orden['kilometraje'], 0, ',', '.') . ' km';
  } else {
    $km = '—';
  }
  ?>
  <div class="card" role="document">
    <header>
      <div>
        <div class="brand">
          <img src="<?= htmlspecialchars($config_taller['logo']); ?>" alt="Logo Taller">
          <div>
            <h1>Presupuesto de Servicio</h1>
            <div class="meta">
              <?= htmlspecialchars($config_taller['nombre']); ?>
              — CUIT: <?= htmlspecialchars($config_taller['cuit']); ?>
            </div>
            <div class="meta small">
              <?= htmlspecialchars($config_taller['direccion']); ?>
              · Tel: <?= htmlspecialchars($config_taller['telefono']); ?>
              · <?= htmlspecialchars($config_taller['email']); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="box small">
        <div><strong>Nº Presupuesto:</strong> <?= str_pad($orden['id'], 4, '0', STR_PAD_LEFT); ?></div>
        <div><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($orden['created_at'])); ?></div>
        <div><strong>Validez:</strong> <?= intval($config_trabajo['validez']); ?> días</div>
      </div>
    </header>

    <section class="grid" aria-label="datos">
      <div class="box">
        <strong>Cliente</strong><br/>
        Nombre: <?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?><br/>
        Teléfono: <?= htmlspecialchars($cliente['telefono']); ?><br/>
        DNI/CUIT: <?= htmlspecialchars($cliente['dni']); ?><br/>
        Email: <?= htmlspecialchars($email_cliente); ?>
      </div>
      <div class="box">
        <strong>Vehículo</strong><br/>
        Marca / Modelo / Año:
        <?= htmlspecialchars($orden['marca'] . ' ' . $orden['modelo'] . ' (' . $orden['anio'] . ')'); ?><br/>
        Patente: <?= htmlspecialchars($orden['patente']); ?><br/>
        Kilometraje: <?= htmlspecialchars($km); ?>
      </div>
    </section>

    <section>
      <table aria-label="detalle">
        <thead>
          <tr>
            <th>Descripción</th>
            <th class="right">Cant.</th>
            <th class="right">Precio unit.</th>
            <th class="right">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($detalle as $item): ?>
          <tr>
            <td>
              <?= htmlspecialchars(
                $item['repuesto_id']
                  ? 'Repuesto: ' . $item['repuesto_nombre']
                  : $item['servicio_nombre']
              ); ?>
            </td>
            <td class="right">1</td>
            <td class="right">$ <?= number_format($item['costo'], 2, ',', '.'); ?></td>
            <td class="right">$ <?= number_format($item['costo'], 2, ',', '.'); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3" class="right">Total estimado</td>
            <td class="right">$ <?= number_format($total, 2, ',', '.'); ?></td>
          </tr>
        </tfoot>
      </table>
    </section>

    <div class="notes">
      <strong>Observaciones:</strong>
      <ul>
        <?php if (!empty($config_trabajo['observaciones']) && is_array($config_trabajo['observaciones'])): ?>
          <?php foreach ($config_trabajo['observaciones'] as $obs): ?>
            <li><?= htmlspecialchars($obs); ?></li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
      <p class="small" style="margin-top:8px;">
        <strong>Garantía del trabajo:</strong> <?= intval($config_trabajo['garantia']); ?> días a partir de la entrega del vehículo.
      </p>
    </div>

    <div class="legal">
      <?= htmlspecialchars($config_trabajo['mensaje_legal']); ?>
    </div>

    <div style="display:flex; gap:20px; margin-top:35px; align-items:center; justify-content:space-between; flex-wrap:wrap;">
      <div>
        <div class="small">Firma y conformidad del cliente:</div>
        <div style="
            margin-top:100px;
            border-top:1px dashed #999;
            width:340px;
            padding-top:16px;
            padding-bottom:24px;
            font-size:14px;
          ">
          Nombre y Firma
        </div>
      </div>
      <?php if (!$print_mode): ?>
        <div class="actions">
          <a class="btn btn-primary" href="#" onclick="window.print();return false;">Imprimir</a>
          <a class="btn btn-outline" href="listar_orden.php">Volver</a>
          <a class="btn btn-outline" 
            href="../shields/descargar_presupuesto.php?id=<?= $orden['id']; ?>">
            Descargar PDF
          </a>
        </div>
      <?php endif; ?>
    </div>

  </div>
</body>
</html>

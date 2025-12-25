<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8" />
<style>
  @page {
    margin: 12mm 15mm;
  }
  body {
    font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
    font-size: 11px;
    color: #222;
    background-color: #ffffff;
  }
  .wrapper {
    width: 100%;
    max-width: 760px;
    margin: 0 auto;
    padding: 12px 16px 18px 16px;
    border: 0.75pt solid #e0e0e0;
    border-radius: 8px;
    background-color: #ffffff;
  }
  .header-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
  }
  .header-table td {
    vertical-align: top;
    padding: 0;
  }
  .logo-cell {
    width: 70px;
  }
  .logo-cell img {
    width: 60px;
    height: 60px;
    border-radius: 8px;
  }
  .title {
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 2px;
  }
  .meta {
    font-size: 10px;
    color: #555;
  }
  .box {
    border: 0.5pt solid #e0e0e0;
    border-radius: 6px;
    padding: 6px 8px;
    font-size: 10px;
    background-color: #fbfbfb;
  }
  .section-title {
    font-weight: bold;
    margin-bottom: 4px;
  }
  .two-cols {
    width: 100%;
    border-collapse: separate;
    border-spacing: 6px;
    margin-top: 14px;
  }
  .two-cols td {
    width: 50%;
    vertical-align: top;
  }
  .box-info {
    min-height: 80px;
  }
  .detail-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 16px;
    font-size: 10px;
  }
  .detail-table th,
  .detail-table td {
    border-bottom: 0.5pt solid #e0e0e0;
    padding: 5px 3px;
  }
  .detail-table th {
    font-weight: bold;
    color: #555;
  }
  .right {
    text-align: right;
  }
  tfoot td {
    font-weight: bold;
    border-top: 0.75pt solid #999;
  }
  .notes {
    margin-top: 16px;
    font-size: 10px;
  }
  .notes ul {
    margin: 4px 0 0 14px;
    padding: 0;
  }
  .notes li {
    margin-bottom: 2px;
  }
  .small {
    font-size: 9px;
    color: #555;
  }
  .legal {
    margin-top: 16px;
    padding: 6px 8px;
    background: #fff8e0;
    border-left: 2pt solid #f2c94c;
    font-size: 9px;
  }
  .firma-block {
    margin-top: 70px;
    font-size: 9px;
  }
  .firma-line {
    margin-top: 10px;
    border-top: 0.5pt dashed #999;
    width: 240px;
    padding-top: 6px;
  }
</style>
</head>
<body>
  <div class="wrapper">
    <!-- ENCABEZADO -->
    <table class="header-table">
      <tr>
        <td class="logo-cell">
          <?php if (!empty($logo_data_uri)): ?>
            <img src="<?= htmlspecialchars($logo_data_uri); ?>" alt="Logo">
          <?php else: ?>
            Logo
          <?php endif; ?>
        </td>
        <td>
          <div class="title">Presupuesto de Servicio</div>
          <div class="meta">
            <?= htmlspecialchars($config_taller['nombre']); ?> — CUIT: <?= htmlspecialchars($config_taller['cuit']); ?><br>
            <?= htmlspecialchars($config_taller['direccion']); ?> — Tel: <?= htmlspecialchars($config_taller['telefono']); ?> — <?= htmlspecialchars($config_taller['email']); ?>
          </div>
        </td>
        <td style="width: 150px; padding-left: 8px;">
          <div class="box" style="font-size:9px;">
            <div><strong>Nº Presupuesto:</strong> <?= str_pad($orden['id'], 4, '0', STR_PAD_LEFT); ?></div>
            <div><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($orden['fecha_realizado'])); ?></div>
            <div><strong>Validez:</strong> <?= (int) $config_trabajo['validez']; ?> días</div>
          </div>
        </td>
      </tr>
    </table>

    <!-- CLIENTE / VEHÍCULO -->
    <table class="two-cols">
      <tr>
        <td>
          <div class="box box-info">
            <div class="section-title">Cliente</div>
            Nombre: <?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?><br>
            Teléfono: <?= htmlspecialchars($cliente['telefono']); ?><br>
            DNI/CUIT: <?= htmlspecialchars($cliente['dni']); ?><br>
            Email: <?= htmlspecialchars($email_cliente); ?>
          </div>
        </td>
        <td>
          <div class="box box-info">
            <div class="section-title">Vehículo</div>
            Marca / Modelo / Año:
            <?= htmlspecialchars($orden['marca'] . ' ' . $orden['modelo'] . ' (' . $orden['anio'] . ')'); ?><br>
            Patente: <?= htmlspecialchars($orden['patente']); ?><br>
            Kilometraje: <?= htmlspecialchars($km); ?>
          </div>
        </td>
      </tr>
    </table>

    <!-- DETALLE -->
    <table class="detail-table">
      <thead>
        <tr>
          <th>Descripción</th>
          <th class="right" style="width:40px;">Cant.</th>
          <th class="right" style="width:80px;">Precio unit.</th>
          <th class="right" style="width:80px;">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($servicios as $serv): ?>
        <tr>
          <td>
            <?php
            if (!empty($serv['repuesto_id']) && !empty($serv['repuesto_nombre'])) {
                echo 'Repuesto: ' . htmlspecialchars($serv['repuesto_nombre']);
            } elseif (!empty($serv['servicio_nombre'])) {
                echo htmlspecialchars($serv['servicio_nombre']);
            } else {
                echo 'Ítem sin descripción';
            }
            ?>
          </td>
          <td class="right">1</td>
          <td class="right">$ <?= number_format($serv['costo'], 2, ',', '.'); ?></td>
          <td class="right">$ <?= number_format($serv['costo'], 2, ',', '.'); ?></td>
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

    <!-- OBSERVACIONES -->
    <div class="notes">
      <strong>Observaciones:</strong>
      <ul>
        <?php if (!empty($config_trabajo['observaciones']) && is_array($config_trabajo['observaciones'])): ?>
          <?php foreach ($config_trabajo['observaciones'] as $obs): ?>
            <li><?= htmlspecialchars($obs); ?></li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
      <p class="small" style="margin-top:4px;">
        <strong>Garantía del trabajo:</strong> <?= (int) $config_trabajo['garantia']; ?> días a partir de la entrega del vehículo.
      </p>
    </div>

    <!-- LEGAL -->
    <div class="legal">
      <?= htmlspecialchars($config_trabajo['mensaje_legal']); ?>
    </div>

    <!-- FIRMA -->
    <div class="firma-block">
      <div class="small">Firma y conformidad del cliente:</div>
      <div class="firma-line">Nombre y Firma</div>
    </div>

  </div>
</body>
</html>
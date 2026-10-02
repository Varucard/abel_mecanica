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
          <?php if ($logo !== ''): ?>
            <img src="<?= e($logo) ?>" alt="Logo">
          <?php else: ?>
            Logo
          <?php endif; ?>
        </td>
        <td>
          <div class="title">Presupuesto de Servicio</div>
          <div class="meta">
            <?= e($taller['nombre']) ?> — CUIT: <?= e($taller['cuit']) ?><br>
            <?= e($taller['direccion']) ?> — Tel: <?= e($taller['telefono']) ?> — <?= e($taller['email']) ?>
          </div>
        </td>
        <td style="width: 150px; padding-left: 8px;">
          <div class="box" style="font-size:9px;">
            <div><strong>Nº Presupuesto:</strong> <?= e($numero) ?></div>
            <div><strong>Fecha:</strong> <?= format_date($orden['created_at']) ?></div>
            <div><strong>Validez:</strong> <?= (int) $trabajo['validez'] ?> días</div>
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
            Nombre: <?= e($cliente['nombre'] . ' ' . $cliente['apellido']) ?><br>
            Teléfono: <?= e($cliente['telefono']) ?><br>
            DNI/CUIT: <?= e($cliente['dni']) ?><br>
            Email: <?= e($cliente['email'] ?: '—') ?>
          </div>
        </td>
        <td>
          <div class="box box-info">
            <div class="section-title">Vehículo</div>
            Marca / Modelo / Año:
            <?= e("{$orden['marca']} {$orden['modelo']} ({$orden['anio']})") ?><br>
            Patente: <?= e($orden['patente']) ?><br>
            Kilometraje: <?= e($kilometraje) ?>
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
        <?php foreach ($items as $item): ?>
          <tr>
            <td><?= e($item['repuesto_id'] !== null ? 'Repuesto: ' . $item['repuesto_nombre'] : $item['servicio_nombre']) ?></td>
            <td class="right"><?= qty($item['cantidad']) ?></td>
            <td class="right">$ <?= money($item['precio_unitario']) ?></td>
            <td class="right">$ <?= money($item['costo']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3" class="right">Total estimado</td>
          <td class="right">$ <?= money($total) ?></td>
        </tr>
      </tfoot>
    </table>

    <!-- OBSERVACIONES -->
    <div class="notes">
      <strong>Observaciones:</strong>
      <ul>
        <?php foreach ($trabajo['observaciones'] as $obs): ?>
          <li><?= e($obs) ?></li>
        <?php endforeach; ?>
      </ul>
      <p class="small" style="margin-top:4px;">
        <strong>Formas de pago:</strong> <?= e(implode(' · ', $trabajo['forma_pago'])) ?>
      </p>
      <p class="small" style="margin-top:4px;">
        <strong>Garantía del trabajo:</strong> <?= (int) $trabajo['garantia'] ?> días a partir de la entrega del vehículo.
      </p>
    </div>

    <!-- LEGAL -->
    <div class="legal">
      <?= e($trabajo['mensaje_legal']) ?>
    </div>

    <!-- FIRMA -->
    <div class="firma-block">
      <div class="small">Firma y conformidad del cliente:</div>
      <div class="firma-line">Nombre y Firma</div>
    </div>

  </div>
</body>

</html>
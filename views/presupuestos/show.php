<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" type="image/png" href="<?= asset('img/logo_64.png') ?>">
  <title>Presupuesto #<?= e($numero) ?> - <?= e($taller['nombre']) ?></title>
  <style>
    :root {
      --accent: #0d6efd;
      --text: #222;
      --muted: #666;
      --paper: #fff;
      --page-bg: #f4f6f8;
      --max-w: 800px;
    }

    body {
      font-family: system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial;
      background: var(--page-bg);
      color: var(--text);
      padding: 20px;
      display: flex;
      justify-content: center;
    }

    .card {
      width: 100%;
      max-width: var(--max-w);
      background: var(--paper);
      border-radius: 8px;
      box-shadow: 0 6px 18px rgba(10, 10, 10, 0.08);
      padding: 20px;
      box-sizing: border-box;
    }

    header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      margin-bottom: 12px;
    }

    .brand {
      display: flex;
      gap: 12px;
      align-items: center;
    }

    .brand img {
      height: 64px;
      width: 64px;
      object-fit: contain;
      border-radius: 6px;
    }

    h1 {
      font-size: 20px;
      margin: 0;
    }

    .meta {
      font-size: 13px;
      color: var(--muted);
    }

    .grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin: 12px 0 18px 0;
    }

    .box {
      background: #fbfbfb;
      padding: 12px;
      border-radius: 6px;
      border: 1px solid #efefef;
      font-size: 14px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 8px;
    }

    th,
    td {
      padding: 10px 8px;
      border-bottom: 1px solid #eee;
      text-align: left;
      font-size: 14px;
    }

    th {
      background: transparent;
      color: var(--muted);
      font-weight: 600;
    }

    tfoot td {
      border-top: 2px solid #ddd;
      font-weight: 700;
    }

    .right {
      text-align: right;
    }

    .small {
      font-size: 12px;
      color: var(--muted);
    }

    .notes {
      margin-top: 14px;
      font-size: 13px;
    }

    .legal {
      margin-top: 14px;
      padding: 10px;
      background: #fff8e6;
      border-left: 4px solid #ffd54d;
      border-radius: 4px;
      font-size: 13px;
    }

    .actions {
      display: flex;
      gap: 10px;
      margin-top: 14px;
    }

    .btn {
      border: 0;
      cursor: pointer;
      display: inline-block;
      padding: 8px 12px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      font-size: 13px;
    }

    .btn-primary {
      background: var(--accent);
      color: white;
    }

    .btn-outline {
      background: transparent;
      border: 1px solid #ddd;
      color: var(--text);
    }

    /* Print-friendly */
    @media print {
      body {
        background: white;
        padding: 0;
      }

      .card {
        box-shadow: none;
        border-radius: 0;
        margin: 0;
        width: 100%;
      }

      .brand img {
        height: 48px;
        width: 48px;
      }

      .actions {
        display: none;
      }
    }

    /* Responsive */
    @media (max-width:640px) {
      .grid {
        grid-template-columns: 1fr;
      }

      header {
        gap: 8px;
      }

      .brand img {
        height: 48px;
        width: 48px;
      }
    }
  </style>
</head>

<body>
  <div class="card" role="document">
    <header>
      <div class="brand">
        <img src="<?= asset('img/logo.png') ?>" alt="Logo del taller">
        <div>
          <h1>Presupuesto de Servicio</h1>
          <div class="meta"><?= e($taller['nombre']) ?> — CUIT: <?= e($taller['cuit']) ?></div>
          <div class="meta small">
            <?= e($taller['direccion']) ?> · Tel: <?= e($taller['telefono']) ?> · <?= e($taller['email']) ?>
          </div>
        </div>
      </div>
      <div class="box small">
        <div><strong>Nº Presupuesto:</strong> <?= e($numero) ?></div>
        <div><strong>Fecha:</strong> <?= format_date($orden['created_at']) ?></div>
        <div><strong>Validez:</strong> <?= (int) $trabajo['validez'] ?> días</div>
      </div>
    </header>

    <section class="grid" aria-label="Datos del cliente y del vehículo">
      <div class="box">
        <strong>Cliente</strong><br>
        Nombre: <?= e($cliente['nombre'] . ' ' . $cliente['apellido']) ?><br>
        Teléfono: <?= e($cliente['telefono']) ?><br>
        DNI/CUIT: <?= e($cliente['dni']) ?><br>
        Email: <?= e($cliente['email'] ?: '—') ?>
      </div>
      <div class="box">
        <strong>Vehículo</strong><br>
        Marca / Modelo / Año: <?= e("{$orden['marca']} {$orden['modelo']} ({$orden['anio']})") ?><br>
        Patente: <?= e($orden['patente']) ?><br>
        Kilometraje: <?= e($kilometraje) ?>
      </div>
    </section>

    <section>
      <table aria-label="Detalle">
        <thead>
          <tr>
            <th>Descripción</th>
            <th class="right">Cant.</th>
            <th class="right">Precio unit.</th>
            <th class="right">Subtotal</th>
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
    </section>

    <div class="notes">
      <strong>Observaciones:</strong>
      <ul>
        <?php foreach ($trabajo['observaciones'] as $obs): ?>
          <li><?= e($obs) ?></li>
        <?php endforeach; ?>
      </ul>
      <p class="small" style="margin-top:8px;">
        <strong>Formas de pago:</strong> <?= e(implode(' · ', $trabajo['forma_pago'])) ?>
      </p>
      <p class="small" style="margin-top:8px;">
        <strong>Garantía del trabajo:</strong> <?= (int) $trabajo['garantia'] ?> días a partir de la entrega del vehículo.
      </p>
    </div>

    <div class="legal"><?= e($trabajo['mensaje_legal']) ?></div>

    <div style="display:flex; gap:20px; margin-top:35px; align-items:center; justify-content:space-between; flex-wrap:wrap;">
      <div>
        <div class="small">Firma y conformidad del cliente:</div>
        <div style="margin-top:100px; border-top:1px dashed #999; width:340px; max-width:100%; padding-top:16px; padding-bottom:24px; font-size:14px;">
          Nombre y Firma
        </div>
      </div>
      <div class="actions">
        <button type="button" class="btn btn-primary" onclick="window.print()">Imprimir</button>
        <a class="btn btn-outline" href="<?= url('ordenes') ?>">Volver</a>
        <a class="btn btn-outline" href="<?= url("ordenes/{$orden['id']}/presupuesto/pdf") ?>">Descargar PDF</a>
      </div>
    </div>
  </div>

  <?php if ($imprimir): ?>
    <script>window.addEventListener('load', () => window.print());</script>
  <?php endif; ?>
</body>

</html>

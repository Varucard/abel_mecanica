<?php
/**
 * Layout principal.
 *
 * @var string $content  HTML de la vista
 * @var string $title    Título de la página
 */
$secciones = [
  'clientes' => 'bg-success text-white',
  'deudores' => 'bg-success text-white',
  'vehiculos' => 'bg-info text-white',
  'ordenes' => 'bg-warning text-dark',
  'turnos' => 'bg-warning text-dark',
  'repuestos' => 'bg-dark text-white',
  'proveedores' => 'bg-dark text-white',
];
$headerClass = $secciones[current_section()] ?? 'bg-primary text-white';
$btnHeader = str_contains($headerClass, 'text-dark') ? 'btn-outline-dark' : 'btn-outline-light';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= e(session()->csrfToken()) ?>">
  <title><?= e($title ?? 'Inicio') ?> - Taller Mecánico</title>
  <link rel="icon" type="image/png" href="<?= asset('img/logo_64.png') ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-dt@1.13.11/css/jquery.dataTables.min.css" integrity="sha384-1QCPE0Isvkd7i/DO1KTZ5GqTLhlGv8kAWBNQDo2jFBkImeSuFxZcEPU0SANETyv5" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" integrity="sha384-4Efkt0T8BZbTzoX4+i/jZFCAWVy1BuRte6F0L3awo3Ek6D1L98qwg7ZRe5bxSrF/" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= asset('css/styles.css') ?>">
</head>

<body>
  <script>
    // Aplicar el tema antes de pintar para evitar el parpadeo en modo oscuro.
    try { if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-mode'); } catch (e) {}
  </script>

  <div class="container my-3">
    <div class="card">
      <div class="card-header <?= $headerClass ?>">
        <h1 class="mb-0 d-flex align-items-center gap-3 flex-wrap">
          <?= e($title ?? '') ?>
          <img src="<?= asset('img/logo.png') ?>" alt="Logo del taller" class="rounded-circle" width="80" height="80">
          <span class="ms-auto d-flex align-items-center gap-2 flex-wrap fs-6">
            <?php if ($usuarioActual = auth()->user()): ?>
              <a href="<?= url('perfil/clave') ?>" class="btn btn-sm <?= $btnHeader ?>" title="Cambiar contraseña">👤 <?= e($usuarioActual['nombre']) ?></a>
              <form action="<?= url('logout') ?>" method="POST" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm <?= $btnHeader ?>">Salir</button>
              </form>
            <?php endif; ?>
            <button id="btnDarkMode" class="btn btn-sm <?= $btnHeader ?>" type="button">🌙 Modo oscuro</button>
          </span>
        </h1>
      </div>

      <div class="card-body">
        <?= $view->partial('partials/navbar') ?>
        <?= $view->partial('partials/alerts') ?>
        <?= $content ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js" integrity="sha384-1H217gwSVyLSIfaLxHbE7dRb3v4mYCKbpQvzx0cegeju1MVsGrX5xXxAvs/HgeFs" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.11/js/jquery.dataTables.min.js" integrity="sha384-xbKh5PcHqYD2znaTJ+mPamIq8ERw8yRfp72NI8CGRg51FffJAXidmePtdkmCjEh9" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js" integrity="sha384-O/ymMhrYXP5tgvTj27eAjKZODlpm7nIVeAkhRL7i9mdenmlJGAjstxMy/bKdklFZ" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/i18n/es.js" integrity="sha384-6tNJV1/uMLfOC6yqmzhl7zOboeGjJMWw+Cqu38ifbgWk8FedovjWQJfhSn7K+mx2" crossorigin="anonymous"></script>
  <script src="<?= asset('js/app.js') ?>"></script>
  <?php foreach ($view->scripts() as $script): ?>
    <script src="<?= asset('js/' . $script) ?>"></script>
  <?php endforeach; ?>
</body>

</html>

<?php
/**
 * Layout principal.
 *
 * @var string $content  HTML de la vista
 * @var string $title    Título de la página
 */
$secciones = [
  'clientes' => 'bg-success text-white',
  'vehiculos' => 'bg-info text-white',
  'ordenes' => 'bg-warning text-dark',
  'turnos' => 'bg-warning text-dark',
];
$headerClass = $secciones[current_section()] ?? 'bg-primary text-white';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= e(session()->csrfToken()) ?>">
  <title><?= e($title ?? 'Inicio') ?> - Taller Mecánico</title>
  <link rel="icon" type="image/png" href="<?= asset('img/logo_64.png') ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-dt@1.13.11/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css">
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
              <a href="<?= url('perfil/clave') ?>" class="btn btn-sm btn-outline-light" title="Cambiar contraseña">👤 <?= e($usuarioActual['nombre']) ?></a>
              <form action="<?= url('logout') ?>" method="POST" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-outline-light">Salir</button>
              </form>
            <?php endif; ?>
            <button id="btnDarkMode" class="btn btn-sm btn-outline-light" type="button">🌙 Modo oscuro</button>
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

  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.11/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/i18n/es.js"></script>
  <script src="<?= asset('js/app.js') ?>"></script>
  <?php foreach ($view->scripts() as $script): ?>
    <script src="<?= asset('js/' . $script) ?>"></script>
  <?php endforeach; ?>
</body>

</html>

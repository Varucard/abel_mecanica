<?php
/**
 * Layout de las páginas públicas para clientes (sin menú interno).
 *
 * @var string $content
 * @var array<string, string> $taller
 */
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex">
  <title><?= e($title ?? '') ?> - <?= e($taller['nombre']) ?></title>
  <link rel="icon" type="image/png" href="<?= asset('img/logo_64.png') ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= asset('css/styles.css') ?>">
  <style>
    .seguimiento-pasos { display: flex; gap: .25rem; }
    .seguimiento-pasos span { flex: 1; text-align: center; font-size: .8rem; padding: .3rem .2rem; border-radius: .25rem; background: var(--bs-secondary-bg, #e9ecef); color: var(--bs-secondary-color, #6c757d); }
    .seguimiento-pasos span.hecho { background: #198754; color: #fff; }
  </style>
</head>

<body>
  <main class="container my-4" style="max-width: 820px;">
    <header class="d-flex align-items-center gap-3 mb-4">
      <img src="<?= asset('img/logo.png') ?>" alt="" class="rounded-circle" width="72" height="72">
      <div>
        <h1 class="h3 mb-0"><?= e($taller['nombre']) ?></h1>
        <div class="text-muted"><?= e($title ?? '') ?></div>
      </div>
    </header>

    <?= $view->partial('partials/alerts') ?>
    <?= $content ?>

    <footer class="text-center text-muted small mt-5">
      <?= e($taller['direccion']) ?> · Tel. <?= e($taller['telefono']) ?>
      · <a href="<?= e(whatsapp_url($taller['whatsapp'])) ?>" target="_blank" rel="noopener">WhatsApp</a>
      · <a href="mailto:<?= e($taller['email']) ?>"><?= e($taller['email']) ?></a>
    </footer>
  </main>
</body>

</html>

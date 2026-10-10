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
  <?= $view->partial('partials/head', ['titulo' => ($title ?? '') . ' - ' . $taller['nombre'], 'manifiesto' => 'portal.webmanifest', 'privado' => true]) ?>
</head>

<body>
  <main class="container my-4 ancho-max-820">
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
    <?= $view->partial('partials/pie', ['atajos' => false]) ?>
  </main>
  <script src="<?= asset('vendor/bootstrap.bundle.min.js') ?>"></script>
  <script src="<?= asset('js/app.js') ?>"></script>
</body>

</html>

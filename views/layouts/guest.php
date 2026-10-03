<?php /** Layout de pantallas sin sesión (login, primer ingreso). @var string $content */ ?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? '') ?> - Taller Mecánico</title>
  <link rel="icon" type="image/png" href="<?= asset('img/logo_64.png') ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= asset('css/styles.css') ?>">
</head>

<body>
  <script>
    try { if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-mode'); } catch (e) {}
  </script>
  <main class="container" style="max-width: 440px; margin-top: 8vh;">
    <div class="text-center mb-3">
      <img src="<?= asset('img/logo.png') ?>" alt="Logo del taller" class="rounded-circle" width="110" height="110">
    </div>
    <div class="card">
      <div class="card-body p-4">
        <h1 class="h4 mb-3 text-center"><?= e($title ?? '') ?></h1>
        <?= $view->partial('partials/alerts') ?>
        <?= $content ?>
      </div>
    </div>
  </main>
</body>

</html>

<?php
/**
 * Pie de página: año, nombre del taller (de Configuración > Sistema), quién lo desarrolló y
 * la versión (APP_VERSION en .env). Va en las pantallas con sesión y en el login.
 */
$taller = \App\Core\App::instance()->container->get(\App\Services\ConfiguracionService::class)->seccion('taller')['nombre'];
?>
<footer class="pie">
  <p>&copy; <?= date('Y') ?> <?= e($taller) ?> · Desarrollado por PC Fighter · v<?= e(version_app()) ?>
    <?php if (auth()->user()): ?>
      <span class="d-none d-md-inline">· <button type="button" class="btn btn-link btn-sm p-0 align-baseline pie-atajos js-ver-atajos">Atajos de teclado <kbd>?</kbd></button></span>
    <?php endif; ?>
  </p>
</footer>

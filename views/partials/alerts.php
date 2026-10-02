<?php foreach (session()->messages() as $type => $messages): ?>
  <?php foreach ($messages as $message): ?>
    <div class="alert alert-<?= $type === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show js-autohide" role="alert">
      <?= e($message) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  <?php endforeach; ?>
<?php endforeach; ?>

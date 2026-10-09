<?php
/**
 * Inicio: arriba lo que se hace todo el día; abajo, los números del taller en recuadros
 * grandes que llevan a su listado (no listas: el detalle está en cada pantalla).
 *
 * @var int $turnosHoy
 * @var array{pendiente: int, en_proceso: int} $abiertas
 * @var float $cobradoMes
 * @var int $finalizadasMes
 * @var int $deudores
 * @var float $totalAdeudado
 * @var int $stockBajo
 * @var list<array<string, mixed>> $services
 */
$meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$mes = $meses[(int) date('n')];
$enTaller = $abiertas['pendiente'] + $abiertas['en_proceso'];
$plural = fn(int $n, string $uno, string $varios) => $n . ' ' . ($n === 1 ? $uno : $varios);
// En el inicio los importes van sin centavos: es un resumen, el detalle está en cada listado.
$redondo = fn(float $monto) => '<span class="importe numero-importe">$ ' . number_format(round($monto), 0, ',', '.') . '</span>';

// Lo que se hace todo el día, a un toque: [ruta, ícono, texto, clase, columna]. Buscar no va:
// el buscador ya está arriba de todo. En el celular "Llegó un auto" tampoco: es el botón del
// medio de la barra de abajo.
$acciones = [
  ['recepcion', 'car-front-fill', 'Llegó un auto', 'btn-primary', 'd-none d-md-block col-md-4'],
  ['turnos/crear', 'calendar-plus', 'Nuevo turno', 'btn-outline-secondary', 'col-6 col-md-4'],
  ['deudores', 'cash-coin', 'Cobrar', 'btn-outline-secondary', 'col-6 col-md-4'],
];

// Los números del taller: [ruta, ícono, título, valor (HTML seguro), detalle, color].
$numeros = [
  ['turnos/semana', 'calendar3', 'Turnos de hoy', (string) $turnosHoy, 'Ver la agenda', 'turnos'],
  ['ordenes?estado=abiertas', 'car-front', 'Autos en el taller', (string) $enTaller,
    $plural($abiertas['pendiente'], 'recibido', 'recibidos') . ' · ' . $abiertas['en_proceso'] . ' en reparación', 'ordenes'],
  [auth()->esAdministrador() ? 'reportes' : 'ordenes', 'graph-up-arrow', "Cobrado en {$mes}", $redondo($cobradoMes),
    $plural($finalizadasMes, 'auto listo', 'autos listos') . ' en el mes', 'clientes'],
  ['deudores', 'exclamation-circle', 'Saldo adeudado', $redondo($totalAdeudado), $plural($deudores, 'cliente debe', 'clientes deben'), 'peligro'],
  ['repuestos', 'box-seam', 'Stock bajo mínimo', (string) $stockBajo, $stockBajo === 0 ? 'Todo en orden' : 'Hay que reponer', 'stock'],
];
?>
<h2 class="visually-hidden">¿Qué querés hacer?</h2>
<div class="row g-2 acciones-rapidas">
  <?php foreach ($acciones as [$ruta, $icono, $texto, $clase, $columna]): ?>
    <div class="<?= e($columna) ?>">
      <a href="<?= url($ruta) ?>" class="btn <?= e($clase) ?> w-100 accion-rapida">
        <?= icono($icono) ?><span><?= e($texto) ?></span>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<h2 class="visually-hidden">El taller hoy</h2>
<div class="row row-cols-2 row-cols-lg-5 g-3 mt-1">
  <?php foreach ($numeros as $i => [$ruta, $icono, $titulo, $valor, $detalle, $color]): ?>
    <?php // En el celular van de a dos: el último (impar) ocupa todo el ancho para no dejar un hueco. ?>
    <div class="col <?= $i === count($numeros) - 1 && count($numeros) % 2 === 1 ? 'col-12' : '' ?>">
      <a href="<?= url($ruta) ?>" class="card numero-panel numero-<?= e($color) ?> text-decoration-none h-100 mb-0">
        <div class="card-body">
          <div class="numero-titulo"><?= icono($icono) ?> <?= e($titulo) ?></div>
          <div class="numero-valor"><?= $valor ?></div>
          <div class="numero-detalle"><?= e($detalle) ?> <?= icono('chevron-right') ?></div>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<?php if ($services !== []): ?>
  <div class="card mt-3">
    <div class="card-header"><strong>Services a vencer en los próximos 30 días</strong></div>
    <ul class="list-group list-group-flush">
      <?php foreach ($services as $sv): ?>
        <li class="list-group-item d-flex justify-content-between flex-wrap gap-2">
          <span>
            <strong><?= format_date($sv['proximo_service_fecha']) ?></strong> ·
            <a href="<?= url("vehiculos/{$sv['vehiculo_id']}") ?>"><?= e($sv['patente']) ?></a> · <?= e($sv['cliente']) ?>
            <?= $sv['proximo_service_km'] ? '· ' . number_format((float) $sv['proximo_service_km'], 0, ',', '.') . ' km' : '' ?>
          </span>
          <span class="small <?= $sv['proximo_service_avisado'] ? 'text-success' : 'text-muted' ?>">
            <?= $sv['proximo_service_avisado'] ? icono('check-lg') . ' avisado el ' . format_date($sv['proximo_service_avisado']) : 'sin avisar' ?>
          </span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

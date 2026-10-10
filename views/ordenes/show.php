<?php
/**
 * @var array<string, mixed> $orden
 * @var array<string, mixed> $cliente
 * @var list<array<string, mixed>> $items
 * @var list<array<string, mixed>> $pagos
 * @var float $saldo
 * @var list<string> $formasPago
 * @var \App\Enums\EstadoOrden $estado
 * @var list<array<string, mixed>> $historial
 * @var bool $puedeEnviar
 * @var bool $avisaListo  al terminar se le avisa al cliente que el vehículo está listo
 * @var array<string, mixed>|null $turno  turno del que vino la orden
 */
use App\Enums\EstadoOrden;
use App\Services\OrdenService;

$pagado = (float) $orden['total'] - $saldo;
$id = (int) $orden['id'];
$editable = OrdenService::editable($estado);
$sinItems = $items === [];
// Solo los repuestos que salen del stock (no los que trajo el cliente).
$repuestos = count(array_filter($items, [\App\Services\StockService::class, 'mueveStock']));

/** Botón que cambia el estado de la orden (POST a ordenes/{id}/estado), con su confirmación. */
// Sin $confirmacion se hace al toque y después se ofrece "Deshacer" (OrdenController::cambiarEstado).
$botonEstado = fn(EstadoOrden $nuevo, string $icono, string $texto, string $clase, ?string $confirmacion = null) =>
  '<form action="' . e(url("ordenes/{$id}/estado")) . '" method="POST" class="d-inline"'
  . ($confirmacion ? ' data-confirm="' . e($confirmacion) . '" data-confirm-aceptar="Sí, ' . e(mb_strtolower($texto)) . '"' : '') . '>'
  . csrf_field() . '<input type="hidden" name="estado" value="' . e($nuevo->value) . '">'
  . '<button type="submit" class="btn ' . e($clase) . '">' . icono($icono) . ' ' . e($texto) . '</button></form>';

$alTerminar = array_filter([
  $repuestos > 0 ? ($repuestos === 1 ? 'se descuenta del stock el repuesto de la orden' : "se descuentan del stock los {$repuestos} repuestos de la orden") : null,
  $avisaListo ? "le avisamos al cliente por email que el auto está listo" : null,
]);
$confirmarTerminar = '¿Terminar el trabajo?' . ($alTerminar ? ' Al confirmar, ' . implode(' y ', $alTerminar) . '.' : '');

// Las tres etapas, con los mismos nombres que ve el cliente en su portal.
$pasos = [EstadoOrden::Pendiente, EstadoOrden::EnProceso, EstadoOrden::Finalizado];
$actual = array_search($estado, $pasos, true);
?>
<div class="card mt-3 etapa-orden">
  <div class="card-body">
    <?php if ($estado === EstadoOrden::Cancelado): ?>
      <p class="mb-3"><?= $view->partial('componentes/estado', ['estado' => $estado]) ?> Esta orden está cancelada.</p>
      <?= $botonEstado(EstadoOrden::Pendiente, 'arrow-counterclockwise', 'Volver a abrir la orden', 'btn-outline-primary') ?>
    <?php else: ?>
      <ol class="pasos-orden" aria-label="Etapas de la orden">
        <?php foreach ($pasos as $i => $paso): ?>
          <li class="<?= $i < $actual ? 'hecho' : ($i === $actual ? 'actual' : '') ?>" <?= $i === $actual ? 'aria-current="step"' : '' ?>><?= e($paso->label()) ?></li>
        <?php endforeach; ?>
      </ol>

      <h2 class="h5 mt-3">Qué sigue</h2>
      <?php if ($editable && $sinItems): ?>
        <p class="mb-3">Cuando sepas qué hay que hacer, cargá los trabajos y repuestos: con eso se arma el presupuesto.</p>
        <div class="d-flex flex-wrap gap-2">
          <a href="<?= url("ordenes/{$id}/editar") ?>" class="btn btn-seccion btn-lg"><?= icono('list-check') ?> Cargar trabajos y repuestos</a>
          <?php if ($estado === EstadoOrden::Pendiente): ?>
            <?= $botonEstado(EstadoOrden::EnProceso, 'play-fill', 'Empezar el trabajo', 'btn-outline-secondary btn-lg') ?>
          <?php endif; ?>
        </div>
      <?php elseif ($estado === EstadoOrden::Pendiente): ?>
        <p class="mb-3">El auto está recibido. Mandale el presupuesto al cliente y, cuando arranquen, tocá «Empezar el trabajo».</p>
        <div class="d-flex flex-wrap gap-2">
          <?= $botonEstado(EstadoOrden::EnProceso, 'play-fill', 'Empezar el trabajo', 'btn-seccion btn-lg') ?>
        </div>
      <?php elseif ($estado === EstadoOrden::EnProceso): ?>
        <p class="mb-3">Cuando el auto esté terminado, tocá «Terminar el trabajo».</p>
        <div class="d-flex flex-wrap gap-2">
          <?= $botonEstado(EstadoOrden::Finalizado, 'check2-circle', 'Terminar el trabajo', 'btn-success btn-lg', $confirmarTerminar) ?>
        </div>
      <?php elseif ($saldo > 0): ?>
        <p class="mb-3">El auto está listo. Falta cobrar <strong><?= importe($saldo) ?></strong>.</p>
        <div class="d-flex flex-wrap gap-2">
          <a href="#registrar_pago" class="btn btn-seccion btn-lg" data-enfocar="#monto"><?= icono('cash-coin') ?> Cobrar</a>
          <a href="<?= url("ordenes/{$id}/entrega") ?>" class="btn btn-outline-secondary btn-lg"><?= icono('receipt') ?> Comprobante de entrega</a>
        </div>
      <?php else: ?>
        <p class="mb-3">El auto está listo y pagado. Imprimí el comprobante para entregarlo.</p>
        <div class="d-flex flex-wrap gap-2">
          <a href="<?= url("ordenes/{$id}/entrega") ?>" class="btn btn-seccion btn-lg"><?= icono('receipt') ?> Comprobante de entrega</a>
        </div>
      <?php endif; ?>

      <div class="d-flex flex-wrap gap-3 mt-3 small">
        <?php if ($editable): ?>
          <form action="<?= url("ordenes/{$id}/estado") ?>" method="POST" class="d-inline" >
            <?= csrf_field() ?><input type="hidden" name="estado" value="cancelado">
            <button type="submit" class="btn btn-link btn-sm p-0 text-danger">Cancelar la orden</button>
          </form>
        <?php endif; ?>
        <?php if ($estado === EstadoOrden::EnProceso): ?>
          <form action="<?= url("ordenes/{$id}/estado") ?>" method="POST" class="d-inline" >
            <?= csrf_field() ?><input type="hidden" name="estado" value="pendiente">
            <button type="submit" class="btn btn-link btn-sm p-0">Volver a «<?= e(EstadoOrden::Pendiente->label()) ?>»</button>
          </form>
        <?php elseif ($estado === EstadoOrden::Finalizado): ?>
          <form action="<?= url("ordenes/{$id}/estado") ?>" method="POST" class="d-inline"
            data-confirm="¿Volver a abrir el trabajo?<?= $repuestos > 0 ? ' Los repuestos vuelven al stock hasta que se termine de nuevo.' : '' ?>" data-confirm-aceptar="Sí, volver a abrirlo">
            <?= csrf_field() ?><input type="hidden" name="estado" value="en_proceso">
            <button type="submit" class="btn btn-link btn-sm p-0">Volver a abrir el trabajo</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-3">
  <?php if ($editable): ?>
    <a href="<?= url("ordenes/{$id}/editar") ?>" class="btn btn-outline-primary"><?= icono('pencil') ?> Editar orden</a>
  <?php endif; ?>
  <?php if (!$sinItems): ?>
    <div class="dropdown">
      <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <?= icono('printer') ?> Imprimir / Enviar
      </button>
      <ul class="dropdown-menu">
        <li><h6 class="dropdown-header">Presupuesto</h6></li>
        <li><a class="dropdown-item" href="<?= url("ordenes/{$id}/presupuesto") ?>"><?= icono('file-text') ?> Ver e imprimir</a></li>
        <li><a class="dropdown-item" href="<?= url("ordenes/{$id}/presupuesto/pdf") ?>"><?= icono('file-earmark-pdf') ?> Descargar PDF</a></li>
        <?php if ($puedeEnviar && $editable && $cliente['email']): ?>
          <li>
            <form action="<?= url("ordenes/{$id}/enviar-presupuesto") ?>" method="POST"
              data-confirm="¿Enviar el presupuesto por email a <?= e($cliente['email']) ?>?" data-confirm-aceptar="Sí, enviarlo">
              <?= csrf_field() ?>
              <button type="submit" class="dropdown-item"><?= icono('envelope') ?> Mandar por email al cliente</button>
            </form>
          </li>
        <?php elseif ($puedeEnviar && $editable): ?>
          <?php // Sin email no se puede mandar: se dice por qué y cómo resolverlo, en vez de un botón gris. ?>
          <li><a class="dropdown-item" href="<?= url("clientes/{$cliente['id']}/editar") ?>"><?= icono('envelope-plus') ?> Cargarle el email al cliente para mandárselo</a></li>
        <?php endif; ?>
        <?php if ($editable): ?>
          <li><a class="dropdown-item" href="<?= e(whatsapp_url($cliente['telefono'], "Hola {$cliente['nombre']}, te escribimos del taller por la orden #{$id}.")) ?>" target="_blank" rel="noopener">
            <?= icono('whatsapp') ?> Escribirle por WhatsApp <small class="text-muted">(adjuntá el PDF)</small></a></li>
        <?php endif; ?>
        <?php if ($estado === EstadoOrden::Finalizado): ?>
          <li><hr class="dropdown-divider"></li>
          <li><h6 class="dropdown-header">Comprobante de entrega</h6></li>
          <li><a class="dropdown-item" href="<?= url("ordenes/{$id}/entrega") ?>"><?= icono('receipt') ?> Ver e imprimir</a></li>
          <li><a class="dropdown-item" href="<?= url("ordenes/{$id}/entrega/pdf") ?>"><?= icono('file-earmark-pdf') ?> Descargar PDF</a></li>
        <?php endif; ?>
      </ul>
    </div>
  <?php endif; ?>
  <a href="<?= url('ordenes') ?>" class="btn btn-outline-secondary ms-auto">Volver a órdenes</a>
</div>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-header"><strong>Orden #<?= (int) $orden['id'] ?></strong></div>
      <div class="card-body">
        <p class="mb-1">Estado: <?= $view->partial('componentes/estado', ['estado' => $estado]) ?></p>
        <p class="mb-1">Fecha: <?= format_date($orden['created_at']) ?></p>
        <p class="mb-1">Mecánico: <?= e($orden['mecanico'] ?? 'sin asignar') ?></p>
        <?php if ($orden['presupuesto_respuesta'] === 'aceptado'): ?>
          <p class="mb-1 text-success"><?= icono('check-lg') ?> Presupuesto aceptado por el cliente (<?= format_date($orden['presupuesto_respuesta_en'], 'd/m H:i') ?>)</p>
        <?php elseif ($orden['presupuesto_respuesta'] === 'rechazado'): ?>
          <p class="mb-1 text-danger"><?= icono('x-lg') ?> Presupuesto rechazado por el cliente (<?= format_date($orden['presupuesto_respuesta_en'], 'd/m H:i') ?>)</p>
        <?php elseif ($orden['presupuesto_enviado']): ?>
          <p class="mb-1 text-muted">Presupuesto enviado el <?= format_date($orden['presupuesto_enviado'], 'd/m H:i') ?>, sin respuesta</p>
        <?php endif; ?>
        <?php if ($orden['km_ingreso'] !== null): ?><p class="mb-1">Km al ingresar: <?= number_format((float) $orden['km_ingreso'], 0, ',', '.') ?></p><?php endif; ?>
        <?php if ($turno): ?>
          <p class="mb-1">Vino con <a href="<?= url("turnos/{$turno['id']}/editar") ?>">turno del <?= format_date($turno['fecha']) ?> a las <?= e(substr($turno['hora'], 0, 5)) ?></a></p>
        <?php endif; ?>
        <?php if ($orden['fecha_realizado']): ?><p class="mb-1">Finalizada: <?= format_date($orden['fecha_realizado']) ?></p><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-header"><strong>Cliente</strong></div>
      <div class="card-body">
        <p class="mb-1"><a href="<?= url("clientes/{$cliente['id']}") ?>"><?= e("{$cliente['apellido']}, {$cliente['nombre']}") ?></a></p>
        <p class="mb-1">Tel: <?= e($cliente['telefono']) ?></p>
        <?php if ($cliente['email']): ?><p class="mb-1"><?= e($cliente['email']) ?></p><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-header"><strong>Vehículo</strong></div>
      <div class="card-body">
        <p class="mb-1"><a href="<?= url("vehiculos/{$orden['vehiculo_id']}") ?>"><?= e("{$orden['marca']} {$orden['modelo']} ({$orden['anio']})") ?></a></p>
        <p class="mb-1">Patente: <?= e($orden['patente']) ?></p>
        <?php if ($orden['kilometraje'] !== null): ?><p class="mb-1"><?= number_format((float) $orden['kilometraje'], 0, ',', '.') ?> km</p><?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if ($orden['diagnostico'] || $orden['trabajo_realizado'] || $orden['notas_internas'] || $orden['proximo_service_km'] || $orden['proximo_service_fecha']): ?>
  <div class="card mt-3">
    <div class="card-body">
      <div class="row g-3">
        <?php if ($orden['diagnostico']): ?>
          <div class="col-md-6"><strong>Motivo / diagnóstico</strong><div><?= nl2br(e($orden['diagnostico'])) ?></div></div>
        <?php endif; ?>
        <?php if ($orden['trabajo_realizado']): ?>
          <div class="col-md-6"><strong>Trabajo realizado</strong><div><?= nl2br(e($orden['trabajo_realizado'])) ?></div></div>
        <?php endif; ?>
        <?php if ($orden['proximo_service_km'] || $orden['proximo_service_fecha']): ?>
          <div class="col-md-6">
            <strong>Próximo service</strong>
            <div>
              <?= $orden['proximo_service_km'] ? number_format((float) $orden['proximo_service_km'], 0, ',', '.') . ' km' : '' ?>
              <?= $orden['proximo_service_km'] && $orden['proximo_service_fecha'] ? ' o el ' : '' ?>
              <?= $orden['proximo_service_fecha'] ? format_date($orden['proximo_service_fecha']) : '' ?>
              <?php if ($orden['proximo_service_avisado']): ?><span class="small text-muted">(avisado el <?= format_date($orden['proximo_service_avisado']) ?>)</span><?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
        <?php if ($orden['notas_internas']): ?>
          <div class="col-md-6"><strong>Notas internas</strong><div class="text-muted"><?= nl2br(e($orden['notas_internas'])) ?></div></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endif; ?>

<div class="card mt-3">
  <div class="card-header"><strong>Detalle</strong></div>
  <div class="card-body table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead>
        <tr><th>Ítem</th><th class="text-end">Cantidad</th><th class="text-end">Precio unit.</th><th class="text-end">Subtotal</th></tr>
      </thead>
      <tbody>
        <?php if ($sinItems): ?>
          <tr><td colspan="4" class="text-muted">Todavía no se cargaron trabajos ni repuestos.<?= $editable ? ' <a href="' . e(url("ordenes/{$id}/editar")) . '">Cargarlos</a>' : '' ?></td></tr>
        <?php endif; ?>
        <?php foreach ($items as $item): ?>
          <tr>
            <td>
              <?= e(item_orden($item)) ?>
              <?php if ($item['a_costo']): ?><span class="badge text-bg-light border" title="Se cobra al precio de costo. El cliente no ve esta marca.">a costo</span><?php endif; ?>
            </td>
            <td class="text-end"><?= qty($item['cantidad']) ?></td>
            <td class="text-end"><?= $item['provisto_cliente'] ? '<span class="text-muted">—</span>' : importe($item['precio_unitario']) ?></td>
            <td class="text-end"><?= $item['provisto_cliente'] ? '<span class="text-muted">—</span>' : importe($item['costo']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><th colspan="3" class="text-end">Total</th><th class="text-end"><?= importe($orden['total']) ?></th></tr>
        <tr><td colspan="3" class="text-end">Pagado</td><td class="text-end"><?= importe($pagado) ?></td></tr>
        <tr class="<?= $saldo > 0 ? 'fila-saldo-pendiente' : 'fila-saldo-cero' ?>">
          <th colspan="3" class="text-end">Saldo</th><th class="text-end"><?= importe($saldo) ?></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<div class="card mt-3" id="pagos">
  <div class="card-header"><strong>Pagos</strong></div>
  <div class="card-body">
    <?php if ($pagos === []): ?>
      <?= $view->partial('componentes/vacio', ['icono' => 'cash-coin', 'texto' => 'Todavía no se registraron pagos.']) ?>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr><th>Fecha</th><th>Forma de pago</th><th class="d-none d-md-table-cell">Observación</th><th class="d-none d-md-table-cell">Registró</th><th class="text-end">Monto</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach ($pagos as $p): ?>
              <tr>
                <td><?= format_date($p['fecha']) ?></td>
                <td><?= e($p['forma_pago']) ?></td>
                <td class="d-none d-md-table-cell"><?= e($p['observacion'] ?? '') ?></td>
                <td class="d-none d-md-table-cell"><?= e($p['usuario'] ?? '—') ?></td>
                <td class="text-end"><?= importe($p['monto']) ?></td>
                <td class="text-end">
                  <?php if (auth()->esAdministrador()): ?>
                    <?= $view->partial('partials/delete_button', [
                      'action' => "pagos/{$p['id']}/anular",
                      'label' => 'Anular',
                      'icono' => 'x-circle',
                      'confirm' => '¿Anular el pago de $ ' . money($p['monto']) . '?',
                    ]) ?>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <?php if ($saldo > 0 && $estado->value !== 'cancelado'): ?>
      <h6 class="mt-3" id="registrar_pago" data-enfocar="#monto">Registrar pago</h6>
      <form action="<?= url("ordenes/{$orden['id']}/pagos") ?>" method="POST" class="row g-2 align-items-end">
        <?= csrf_field() ?>
        <div class="col-sm-3">
          <label for="monto" class="form-label">Monto *</label>
          <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="text" inputmode="decimal" autocomplete="off" class="form-control js-importe" id="monto" name="monto" required
              data-min="0.01" data-max="<?= e((string) $saldo) ?>" data-error-max="No podés cobrar más que el saldo: $ <?= e(money($saldo)) ?>."
              value="<?= e(old('monto', money($saldo))) ?>">
          </div>
        </div>
        <div class="col-sm-3">
          <label for="forma_pago" class="form-label">Forma de pago *</label>
          <select class="form-select" id="forma_pago" name="forma_pago" required>
            <?php foreach ($formasPago as $forma): ?>
              <option <?= selected($forma === old('forma_pago')) ?>><?= e($forma) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-sm-2">
          <label for="fecha" class="form-label">Fecha *</label>
          <input type="date" class="form-control" id="fecha" name="fecha" max="<?= date('Y-m-d') ?>" required value="<?= e(old('fecha', date('Y-m-d'))) ?>">
        </div>
        <div class="col-sm-4">
          <label for="observacion" class="form-label">Observación</label>
          <input type="text" class="form-control" id="observacion" name="observacion" maxlength="255" value="<?= e(old('observacion')) ?>">
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-seccion"><?= icono('check-lg') ?> Registrar pago</button>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>

<div class="card mt-3">
  <div class="card-header"><strong>Historial</strong></div>
  <div class="card-body">
    <?php if ($historial === []): ?>
      <?= $view->partial('componentes/vacio', ['icono' => 'clock-history', 'texto' => 'Sin movimientos registrados.']) ?>
    <?php else: ?>
      <ul class="list-unstyled mb-0 small">
        <?php foreach ($historial as $h): ?>
          <li class="mb-1">
            <span class="text-muted"><?= format_date($h['created_at'], 'd/m/Y H:i') ?></span> ·
            <strong><?= e($h['usuario_nombre'] ?? '—') ?></strong> · <?= e($h['descripcion']) ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>

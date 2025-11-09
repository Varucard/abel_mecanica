$(document).ready(() => {
  $('#tabla_servicios').DataTable();
});

function eliminarServicio(id, nombre) {
  if (confirm(`¿Seguro que querés eliminar el servicio "${nombre}"?`)) {
    window.location.href = '../../shields/procesar_servicio.php?action=delete&id=' + id;
  }
}

function cambiarEstadoServicio(id, estado) {
  const nuevo = estado === 'activo' ? 'inactivo' : 'activo';
  window.location.href = '../../shields/procesar_servicio.php?action=estado&id=' + id + '&estado=' + nuevo;
}

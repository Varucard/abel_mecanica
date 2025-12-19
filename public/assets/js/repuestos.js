$(document).ready(() => {
  $('#tabla_repuestos').DataTable();
});

function eliminarRepuesto(id, nombre) {
  if (confirm(`¿Seguro que querés eliminar el repuesto "${nombre}"?`)) {
    window.location.href = '../shields/procesar_repuesto.php?action=delete&id=' + id;
  }
}

# Hoja de ruta

Estado: ✅ hecho · 🚧 en curso · ⏳ pendiente · 💭 a futuro (requiere definiciones)

## Funcionalidades pendientes (de NOTAS.MD)

| # | Ítem | Estado |
| --- | --- | --- |
| 1 | **Seguridad**: login con usuario y clave, roles (administrador / empleado) y gestión de usuarios. | ✅ |
| 2 | **Ficha del cliente**: vehículos, historial de órdenes y turnos, foto y estado de deuda. | ✅ |
| 3 | **Ficha del vehículo**: datos completos, historial de órdenes y turnos, imágenes. | ✅ |
| 4 | **Pagos y deudores**: registrar pagos por orden, saldo pendiente, clientes deudores. | ✅ |
| 5 | **Stock de repuestos**: stock actual y mínimo, ingresos, descuento al finalizar órdenes, alertas. | ✅ |
| 6 | **Proveedores**: ABM y proveedor habitual de cada repuesto; ingresos de stock por proveedor. | ✅ |
| 7 | **Empleados**: ABM (hereda de Persona) y mecánico asignado a cada orden. | ✅ |
| 8 | **Comprobante de entrega / conformidad** al finalizar la orden. | ✅ |
| 9 | **Más datos del vehículo**: motor, combustible, color, número de chasis, observaciones. | ✅ |
| 10 | **Aviso de turnos**: recordatorio por WhatsApp y por email (opcional, con SMTP configurable). | ✅ |

## Mejoras técnicas y de producto

| # | Ítem | Estado |
| --- | --- | --- |
| 11 | **Migraciones versionadas** con ejecución automática al levantar el contenedor. | ✅ |
| 12 | **Integración continua** (GitHub Actions): sintaxis, tests unitarios y de integración. | ✅ |
| 13 | **Tests de integración** de servicios y repositorios contra MySQL. | ✅ |
| 14 | **Panel de inicio**: turnos del día, órdenes abiertas, saldos pendientes, stock bajo. | ✅ |
| 15 | **Cantidades en las órdenes** (hoy cada ítem cuenta como 1). | ✅ |
| 16 | **Backups** de la base con un comando. | ✅ |
| 17 | **Puertos configurables** en Docker y nombres de contenedor sin colisiones. | ✅ |

## Segunda etapa (definida con el negocio)

| # | Ítem | Estado |
| --- | --- | --- |
| 18 | **Todo configurable** desde Configuración: plantillas de mensajes, horario de atención, feriados, turnos simultáneos, stock negativo, portal, canales de aviso. | ✅ |
| 19 | **Confirmación de turnos por email**: link para que el cliente confirme o cancele; se vuelve a pedir si se reprograma. | ✅ |
| 20 | **Recordatorios automáticos** el día hábil anterior, solo en horario laboral argentino y sin feriados (importables desde ArgentinaDatos). | ✅ |
| 21 | **Portal "Seguí tu vehículo"**: consulta pública con DNI (+ patente, configurable) y mini historial de trabajos y turnos. | ✅ |
| 22 | **Canales de aviso intercambiables**: email activo; WhatsApp Business preparado (interfaz y plantillas listas). | ✅ |

## Tercera etapa (mejoras con las herramientas existentes)

| # | Ítem | Estado |
| --- | --- | --- |
| 23 | **Sistema de logs**: archivos diarios JSON con nivel, usuario, IP, ruta e id de petición; visor para administradores. | ✅ |
| 24 | **Auditoría**: quién hizo qué y cuándo (órdenes, pagos, stock, precios, usuarios, configuración). | ✅ |
| 25 | **Aumento masivo de precios** con porcentaje, redondeo, vista previa y selección. | ✅ |
| 26 | **Presupuesto por email** con PDF adjunto y **aceptación online** del cliente. | ✅ |
| 27 | **Km de ingreso, diagnóstico, trabajo realizado y notas internas** en cada orden. | ✅ |
| 28 | **Próximo service** en la orden y **aviso automático** al cliente. | ✅ |
| 29 | **De turno a orden** en un clic. | ✅ |
| 30 | **Combos** de servicios y repuestos. | ✅ |
| 31 | **Búsqueda rápida** por patente, DNI, apellido u orden. | ✅ |
| 32 | **Agenda semanal** con cupos libres. | ✅ |
| 33 | **Reportes** con exportación a Excel (CSV). | ✅ |
| 34 | **Precio de costo y margen** en repuestos. | ✅ |
| 35 | **Menú usable en celulares** y tablets. | ✅ |
| 36 | **Listados paginados desde el servidor**. | ✅ |
| 37 | **Backup automático diario**. | ✅ |

## Revisión de código (PR #6)

Correcciones surgidas de una revisión completa del código, todas con tests:

- **Precios**: el aumento masivo sin selección no modifica nada, no se aplica dos veces si se reenvía el formulario, una rebaja nunca sube un precio por el redondeo y se rechazan importes fuera de rango.
- **Presupuestos**: editar la orden anula la respuesta del cliente; la vigencia se cuenta desde el envío; solo se envía con la orden abierta.
- **Concurrencia**: stock, pagos y cupos de turnos a salvo de pedidos simultáneos.
- **Seguridad**: sesión revalidada en cada pedido (usuarios desactivados o con otro rol), CSV sin fórmulas, bloqueos por intentos que no permiten bloquear a otro usuario, IP real detrás de un proxy, links públicos que vencen y sin caché, SRI en el CDN (hoy las librerías se sirven localmente), imágenes de resolución exagerada rechazadas, phpMyAdmin y MySQL solo en el servidor e imagen de producción sin Xdebug.
- **Datos**: cada orden guarda su cliente (el historial no pasa al nuevo dueño de un vehículo); lo cobrado no incluye órdenes canceladas.
- **Operación**: backup consistente con copia opcional fuera del servidor, tareas periódicas independientes, migraciones retomables y `bin/usuario.php` funcionando de nuevo.

## Cuarta etapa (interfaz y app instalable)

| # | Ítem | Estado |
| --- | --- | --- |
| 38 | **Presupuesto modificado**: si cambia uno ya aceptado, se le avisa al cliente para que lo vuelva a aceptar (también con la orden en proceso). | ✅ |
| 39 | **Del presupuesto al seguimiento** sin pedir DNI ni patente (el link del email ya identifica al cliente). | ✅ |
| 40 | **Aviso de vehículo listo** al finalizar la orden, con el saldo a abonar; una sola vez por orden. | ✅ |
| 41 | **Sistema visual**: colores en tokens, CSS por capas sin `!important`, modo oscuro nativo de Bootstrap y contraste mínimo 4.5:1. | ✅ |
| 42 | **Componentes de vista** (campo, estado, vacío, acciones de fila) y botones según su función. | ✅ |
| 43 | **Librerías locales** (sin CDN): DataTables 2 con Responsive, Tom Select y Bootstrap Icons. | ✅ |
| 44 | **App instalable (PWA)**: barra inferior y menú plegable en el celular, tablas que pliegan columnas, aviso "Sin conexión". | ✅ |
| 45 | **Confirmaciones y avisos propios**: modal en vez de `confirm()`/`alert()`, avisos flotantes de éxito. | ✅ |

## A futuro

| Ítem | Qué falta |
| --- | --- |
| 💭 Activar WhatsApp Business (API de Meta) | Cuenta verificada, número dedicado, plantillas aprobadas e implementar `WhatsAppCanal::enviar()`. |
| 💭 Facturación electrónica (AFIP/ARCA) | Fuera de alcance por ahora. |

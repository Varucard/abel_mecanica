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

## A futuro

| Ítem | Qué falta |
| --- | --- |
| 💭 Activar WhatsApp Business (API de Meta) | Cuenta verificada, número dedicado, plantillas aprobadas e implementar `WhatsAppCanal::enviar()`. |
| 💭 Facturación electrónica (AFIP/ARCA) | Fuera de alcance por ahora. |

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

## Quinta etapa (facilidad de uso)

Pedido del taller: el sistema costaba a quien no tiene práctica con la computadora.

| # | Ítem | Estado |
| --- | --- | --- |
| 46 | **"Llegó un auto"**: patente → dueño y auto (si son nuevos) → orden abierta, en una sola pantalla. | ✅ |
| 47 | **Ficha de la orden guiada**: etapa actual y un botón con el paso que sigue; avisa qué pasa al terminar. | ✅ |
| 48 | **Orden sin ítems** al recibir; se completan cuando se sabe qué hacer. Lo de cierre, plegado. | ✅ |
| 49 | **Menú por uso**: lo diario primero, Configuración al final, un clic a cada sección. | ✅ |
| 50 | **Turnos con horarios para tocar** (con cupos) y sin elegir estado al crear. | ✅ |
| 51 | **Modelo nuevo** escribiéndolo al cargar el auto. | ✅ |
| 52 | **Imprimir / Enviar** en un solo menú en la ficha de la orden. | ✅ |
| 53 | **Panel más limpio**: tres accesos grandes y, debajo, cinco números (turnos de hoy, autos en el taller, cobrado en el mes, saldo adeudado, stock bajo) que llevan a su listado, en lugar de listas. En el celular, "Llegó un auto" en el medio de la barra. | ✅ |
| 57 | **Modo oscuro**: botón y encabezado de Stock visibles; el día de hoy en la agenda semanal se lee en los dos temas. | ✅ |
| 58 | **Menú en colores suaves** (un tono pastel por sección, mismo texto en todos) y línea que separa cada botón de su flecha. Los botones de acción de las filas usan el mismo estilo. | ✅ |
| 59 | **Listados con filtro desde un link**: `/ordenes?estado=abiertas` abre las órdenes ya filtradas (lo usa el número "Autos en el taller"). | ✅ |
| 61 | **Pie de página** con año, nombre del taller, desarrollador y versión (`APP_VERSION`), como en los otros sistemas. | ✅ |
| 62 | **Un color por sección**, el mismo en la barra de título y (suave) en su botón del menú: Inicio azul (también la casita), Órdenes amarillo, Turnos violeta, Clientes verde, Vehículos celeste, Stock terracota y Configuración gris neutro. | ✅ |
| 60 | **Tamaño de letra parejo**: la base sube a 17px en la raíz, así textos, botones y campos ya no mezclan 16 y 17px. | ✅ |
| 54 | **Errores debajo de cada campo**; teléfono y patente como los dicta la gente. | ✅ |
| 55 | **Mismo vocabulario** en todo el sistema y en el portal (Recibido → En reparación → Listo; "Nuevo …", "Guardar …", "Cancelar"). | ✅ |
| 56 | Listado de clientes compacto, listados vacíos con botón para empezar, foto del cliente plegada, letra y botones más grandes. | ✅ |

## Revisión de usabilidad (agente revisor, 2026-10-09)

Hallazgos de una revisión usando la app en computadora y celular, claro y oscuro.

| # | Ítem | Estado |
| --- | --- | --- |
| 63 | **Importes a la argentina**: "10.000" son diez mil (se tomaba como $10) en pagos, precios, costos y cantidades; se muestran como "46.000,00". Misma regla en el navegador (`window.leerImporte`) y en el servidor (`Validator::importe`). | ✅ |
| 64 | **Listados en el celular**: cada tabla dice qué columnas no se pliegan (`data-prioridad`): saldo, estado, stock y acciones quedan a la vista. | ✅ |
| 65 | **Cobrar en un toque**: botón "Cobrar" por deudor y en el historial de la ficha del cliente; la orden abre con el monto listo para escribir. Filas del historial tocables enteras. | ✅ |
| 66 | **Avisos amarillos** cuando lo principal salió bien pero falta algo (turno guardado sin email, aviso al cliente que falló): ya no un error rojo que hacía cargar todo de nuevo. | ✅ |
| 67 | **Confirmaciones con verbos** ("Sí, cancelar la orden" / "No, volver") en vez de "Aceptar" / "Cancelar". | ✅ |
| 68 | **Sin órdenes duplicadas**: si el auto ya está en el taller, lo principal es "Seguir con la orden #N"; abrir otra se confirma (también lo exige el servidor). | ✅ |
| 69 | **Mensajes propios** para los errores del navegador (año, email, montos, teléfono incompleto). | ✅ |
| 70 | **Menú**: marca la sección actual, foco de teclado visible, Configuración abre Sistema (admin) y no se corta en pantallas cortas; en el celular, usuario/tema/salir arriba y solo con ícono. | ✅ |
| 71 | **Acciones de fila**: "Recibir" con texto en los turnos, íconos claros para activar/desactivar (con explicación), tacho separado y botones más grandes en el celular. | ✅ |
| 72 | **Listado de órdenes**: N° como link, "Sin trabajos cargados", sin "Pagada" en órdenes de $0, el estado no queda en blanco y se avisa antes si falta cargar ítems. | ✅ |
| 73 | **Orden**: quitar ítems desde su fila, chips cortos, vehículo fijo al editar, enviar por WhatsApp o cargar el email desde "Imprimir / Enviar", turno de origen con fecha y hora. | ✅ |
| 74 | **Turno para un cliente nuevo** en tres pasos guiados (cliente → auto → turno con todo elegido). | ✅ |
| 75 | **Agenda en el celular** arranca en el día de hoy; franjas libres tocables enteras. **Stock**: "Ajustar por conteo" plegado y explicado. **Buscador**: "5" busca la orden 5. Avisos de 10 s. | ✅ |

## Revisión de front y back (agentes revisores, 2026-10-09)

| # | Ítem | Estado |
| --- | --- | --- |
| 76 | **Importes al volver de un error**: lo que escribió la persona se deja tal cual (antes "10.000" volvía como "10,00"). | ✅ |
| 77 | **Sin órdenes duplicadas en paralelo**: el vehículo se bloquea al recibirlo (dos equipos a la vez ya no abren dos órdenes) y "abrir otra" se confirma con el N° de la orden que se vio. | ✅ |
| 78 | **Cantidades sin ambigüedad**: "1.250" en una cantidad pide aclarar (1250 o 1,25); las cantidades se muestran sin punto de miles. Montos que empiezan con 0 ("0.500") se leen como decimal. | ✅ |
| 79 | **Teléfono con código de área real** (11, 2 o 3): "15 2345 6789" sin el 11 se avisa. | ✅ |
| 80 | **Turno de origen**: un turno cancelado o ya usado no abre otra orden. **Cliente dado de baja** que vuelve con un auto nuevo se reactiva (con aviso). | ✅ |
| 81 | **"Llegó un auto"**: el DNI ya no muestra "Cliente nuevo" y "Ya es cliente" juntos (respuestas desordenadas); la patente no se busca dos veces; con orden abierta, el cursor va a "Seguir con la orden"; marca nueva escribiéndola; la búsqueda de DNI ya no devuelve el teléfono. | ✅ |
| 82 | **Celular**: la patente (órdenes) y el cliente (turnos) se ven sin desplegar la fila. | ✅ |
| 83 | **Turnos**: los avisos por email solo se ofrecen si el cliente tiene email; un solo mensaje al guardar. | ✅ |
| 84 | **Accesibilidad**: "Saltar al contenido", `<main>`, el título sin botones adentro, textos de orden de las tablas en castellano. | ✅ |
| 85 | **Estilos siempre al día**: cada archivo CSS se carga con su versión (`views/partials/head.php`); un cambio de estilos ya no queda tapado por el caché. | ✅ |
| 86 | Detalles: fila "Saldo" legible en oscuro, textos de estado sin cortar, verbos en todas las confirmaciones (también en el portal), vocabulario parejo, búsqueda sin resultados con salidas. | ✅ |

## Pendientes técnicos y mejoras rápidas (2026-10-09)

| # | Ítem | Estado |
| --- | --- | --- |
| 87 | **Colores solo en `tokens.css`**: los `#fff` / `#212529` / `#000` sueltos pasaron a tokens (`--app-sobre-color`, `--app-sobre-claro`, `--app-oscurecer`). | ✅ |
| 88 | **Auditoría atada a la transacción**: la línea del log de texto se escribe recién al confirmar (`Repository::alConfirmar`) y un error de auditoría dentro de una transacción ya no se traga. `conCandado()` avisa si se usa dentro de `transaction()`. | ✅ |
| 89 | **Inicio sin traer listas para contarlas**: `PanelRepository` cuenta en la base (turnos del día, stock bajo, deuda). | ✅ |
| 90 | **Tests**: transacción real de nivel superior (todo o nada sin la transacción de los tests), savepoints, `alConfirmar`, candado, números del inicio, feriado en la agenda, marca/modelo escritos inválidos. | ✅ |
| 91 | **"Deshacer" en vez de "¿Seguro?"** en lo reversible: activar/desactivar clientes, vehículos y empleados; empezar, cancelar y volver a "Recibido" una orden. Se sigue preguntando en lo que no se deshace (eliminar, anular un pago, terminar —avisa al cliente—, enviar presupuesto). | ✅ |
| 92 | **Buscador con sugerencias** mientras se escribe (patrón combobox: flechas, Enter, Escape). | ✅ |
| 93 | **Atajos de teclado**: `/` buscar, `N` llegó un auto, `T` nuevo turno, `O` órdenes, `I` inicio, `?` la lista (también en el pie). | ✅ |
| 94 | **Borradores**: "Llegó un auto", orden, turno, cliente y vehículo se recuperan si se cierra la pestaña o se corta la luz. Se borran al guardar, al salir o a las 8 h. | ✅ |
| 95 | **Tamaño de letra A− / A+**, guardado por navegador (WCAG 1.4.4). | ✅ |

## A futuro

### Mejoras de usabilidad propuestas (estándares de la industria)

Ideas surgidas de las revisiones del 2026-10-09, para cuando haya tiempo. Están ordenadas
de menor a mayor esfuerzo.

| Ítem | Qué es | Por qué |
| --- | --- | --- |
| 💭 **Tablero de órdenes en columnas** | Vista Recibido · En reparación · Listo con tarjetas que se arrastran, a la par del listado. | Es la vista típica de los sistemas de taller: el estado del día de un vistazo. |
| 💭 **Fotos al recibir el auto** | En "Llegó un auto", sacar fotos con la cámara del celular (golpes, rayones, nivel de nafta, km). | Evita discusiones al entregar; ya existe la galería del vehículo. |
| 💭 **Firma del cliente en pantalla** | Conformidad al recibir y al entregar, firmando con el dedo. | Reemplaza el papel firmado; queda en el comprobante. |
| 💭 **WhatsApp con link** | "Presupuesto" y "tu auto está listo" por WhatsApp con el link del portal, en un toque. | La mayoría de los clientes no usa email; hoy el WhatsApp va sin el link. |
| 💭 **Alertas del taller** | Autos listos sin retirar hace X días, presupuestos sin respuesta, services por vencer. | Lo que se olvida y cuesta plata, a la vista en el inicio. |
| 💭 **Tarjetas en el celular** | En vez de tablas, cada orden o turno como una tarjeta con lo esencial y sus botones. | Las tablas, aun priorizando columnas, no son cómodas en pantallas chicas. |
| 💭 **Modo mostrador / tablet con PIN** | Cada empleado entra con un PIN de 4 dígitos, sin usuario y contraseña cada vez. | La PC del mostrador la usan varios; hoy se comparte la sesión. |
| 💭 **Primeros pasos guiados y ayuda "?"** | Al instalar: cargar marcas, servicios y datos del taller paso a paso. Ayuda contextual en cada pantalla. | Baja la curva de aprendizaje de un empleado nuevo. |
| 💭 **Ticket térmico y QR** | Comprobante para impresora térmica, con un QR al seguimiento del auto. | Más barato y rápido que la hoja A4; el cliente sigue el trabajo desde el celular. |
| 💭 **Medir la usabilidad** | Pruebas con 3 a 5 personas del taller haciendo tareas reales y encuesta SUS antes y después. | Con pocas personas aparecen casi todos los problemas (Nielsen); sirve para priorizar lo que sigue. |

### Otros

| Ítem | Qué falta |
| --- | --- |
| 💭 Activar WhatsApp Business (API de Meta) | Cuenta verificada, número dedicado, plantillas aprobadas e implementar `WhatsAppCanal::enviar()`. |
| 💭 Facturación electrónica (AFIP/ARCA) | Fuera de alcance por ahora. |
| 💭 Casos compartidos PHP/JS | Un archivo de casos (importes, teléfonos, patentes) que lean PHPUnit y un test de JS, para que las reglas duplicadas en `Validator`/`ClienteService` y `app.js` no se separen. |
| 💭 Tests de controladores | Cambio de estado por formulario vs. AJAX y las respuestas JSON (`/recepcion/*`, `/turnos/horarios`, `/buscar/sugerencias`): hoy se verifican en el navegador. |

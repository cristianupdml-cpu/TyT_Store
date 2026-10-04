# TyT Store con PHP y MySQL (XAMPP)

## Actualizar la instalación que ya tienes

La migración conserva productos, inventario, proveedores y ventas actuales.

1. En phpMyAdmin, exporta una copia de seguridad de `tyt_store`.
2. Guarda tu carpeta web actual como respaldo y copia esta carpeta en `C:\xampp\htdocs\TyT_Store`.
3. Inicia Apache y MySQL desde el panel de XAMPP.
4. En phpMyAdmin, selecciona la base `tyt_store`, abre **Importar** y ejecuta `database/migracion_auth_ventas_reportes.sql`. Ejecuta esta migración una sola vez.
5. Abre `http://localhost/TyT_Store/`. La primera visita permite crear la cuenta inicial de administrador.
6. Inicia sesión y entra a **Usuarios** para crear las cuentas de vendedor y otros administradores.

Revisa `config/database.php` si tu usuario o contraseña de MySQL difieren de la configuración usual de XAMPP (`root` sin contraseña).

## Instalación desde cero

Inicia Apache y MySQL, importa `database/tyt_store.sql` en phpMyAdmin y copia la carpeta a `C:\xampp\htdocs\TyT_Store`. Luego abre `http://localhost/TyT_Store/` y crea la cuenta inicial. No importes también la migración en una base creada desde cero.

## Funcionalidad

- Las cuentas se crean desde **Usuarios** por un administrador; los roles disponibles son administrador y vendedor.
- Las contraseñas se almacenan con `password_hash`, las páginas requieren sesión y el backend verifica permisos por rol.
- El vendedor puede consultar productos e inventario y registrar ventas. El administrador también gestiona productos, catálogos, proveedores, movimientos, usuarios y reportes.
- Las ventas guardan el detalle de variantes y cantidades, validan el stock y actualizan existencias dentro de una transacción.
- Reportes usa datos de MySQL, permite filtrar por fechas y exportar a CSV. Las ventas históricas que ya existían se conservan, pero no tienen detalle de productos porque la versión anterior no lo guardaba.

PHP debe tener habilitado `pdo_mysql` (normalmente activo en XAMPP). Para uso fuera de tu equipo local, todavía se necesita configurar HTTPS y controles de despliegue.

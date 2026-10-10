# InventarioPro — PHP + MySQL

Aplicación web para gestionar artículos de inventario.

## Campos
- Nombre del artículo
- Marca
- Unidades (entero igual o mayor que cero)
- Bodega de ubicación

## Funciones
- Crear, listar, buscar, editar y eliminar artículos.
- Resumen con total de artículos, unidades y marcas diferentes.
- Búsqueda por nombre, marca o bodega.
- Creación automática de la tabla `articulos` si no existe.
- Consultas preparadas PDO, protección CSRF y escape HTML.

## Requisitos
PHP 8+ con `pdo_mysql` y `mbstring`; MySQL/MariaDB; base de datos ya creada con permisos CRUD y para crear tablas.

## Instalación
1. Descomprime el ZIP.
2. Sube `index.php`, `config.php` y `styles.css` a la carpeta pública de tu hosting.
3. Las credenciales solicitadas ya están configuradas en `config.php`; opcionalmente puedes definir `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASS` como variables de entorno.
4. Abre `index.php` en el navegador. La tabla `articulos` se crea automáticamente si no existe.

## Seguridad
No publiques credenciales en repositorios. En producción, utiliza variables de entorno y HTTPS. La conexión remota no ha sido probada al generar el ZIP; verifica acceso y permisos desde el panel de tu proveedor.

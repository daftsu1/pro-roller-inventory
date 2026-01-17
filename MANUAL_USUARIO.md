# Manual de Usuario - pro roller

Guía completa para usar el sistema de inventario **pro roller**. Este manual está diseñado para usuarios que no tienen conocimientos técnicos.

## 📋 Tabla de Contenidos

1. [Acceso al Sistema](#acceso-al-sistema)
2. [Navegación General](#navegación-general)
3. [Dashboard](#dashboard)
4. [Módulo de Ventas](#módulo-de-ventas)
5. [Gestión de Productos](#gestión-de-productos)
6. [Gestión de Clientes](#gestión-de-clientes)
7. [Gestión de Proveedores](#gestión-de-proveedores)
8. [Gestión de Categorías](#gestión-de-categorías)
9. [Reportes](#reportes)
10. [Movimientos de Inventario](#movimientos-de-inventario)
11. [Gestión de Usuarios (Solo Admin)](#gestión-de-usuarios-solo-admin)

---

## Acceso al Sistema

### 1. Abrir el Sistema

1. Abre tu navegador web (Chrome, Firefox, Edge, etc.)
2. Ve a la dirección del sistema: `http://localhost:8000` (o la que te haya proporcionado tu administrador)
3. Verás la pantalla de inicio de sesión

### 2. Iniciar Sesión

1. Ingresa tu **correo electrónico** en el primer campo
2. Ingresa tu **contraseña** en el segundo campo
3. (Opcional) Marca la casilla "Recordarme" si quieres que el sistema recuerde tu sesión
4. Haz clic en el botón **"Iniciar Sesión"**

### 3. Cerrar Sesión

- En la parte inferior de la barra lateral (lado izquierdo), haz clic en el icono de **salida** (flecha hacia la derecha)

---

## Navegación General

### Barra Lateral (Menú Principal)

En el lado izquierdo de la pantalla encontrarás un menú con todas las opciones del sistema:

- **Dashboard**: Resumen general del sistema
- **Ventas**: Módulo principal para crear y gestionar ventas
- **Catálogo**: Gestión de productos, categorías y proveedores
- **Relaciones**: Gestión de clientes
- **Informes**: Reportes del sistema
- **Historial**: Movimientos de inventario
- **Administración**: Gestión de usuarios (solo para administradores)

### Pantalla Móvil

En dispositivos móviles o tablets:
- Toca el icono de menú (☰) en la esquina superior izquierda para abrir/cerrar el menú
- El menú aparecerá como una capa sobre la pantalla

---

## Dashboard

El Dashboard es la pantalla principal que verás después de iniciar sesión. Muestra un resumen general:

### Tarjetas de Resumen

- **Total Productos**: Cantidad total de productos en el sistema
- **Bajo Stock**: Productos que necesitan reposición (en color amarillo/naranja)
- **Ventas Hoy**: Cantidad de ventas completadas hoy
- **Total Hoy**: Monto total de ventas del día (en color verde)

### Secciones del Dashboard

#### Productos Bajo Stock
Lista de productos que tienen stock igual o menor al stock mínimo configurado. Revisa esta sección regularmente para saber qué productos necesitas comprar.

#### Ventas Recientes
Lista de las últimas ventas realizadas, mostrando:
- Número de factura
- Fecha
- Total de la venta

#### Movimientos Recientes
Historial de los últimos movimientos de inventario (entradas y salidas de productos).

---

## Módulo de Ventas

El módulo de ventas es donde realizas las transacciones con los clientes.

### Crear una Nueva Venta

1. Haz clic en **"Nueva Venta"** (botón verde en la parte superior)
2. Se abrirá un modal (ventana emergente) con la nueva venta
3. La venta se crea automáticamente con un número de factura único

### Completar Datos de la Venta

En el modal de venta encontrarás:

#### Información Básica
- **Fecha**: Se establece automáticamente a la fecha de hoy (puedes cambiarla)
- **Número de Factura**: Se genera automáticamente (ej: VENT-000001)

#### Datos del Cliente

Tienes dos opciones:

**Opción 1: Buscar un Cliente Existente**
1. En el campo **"Buscar Cliente"**, escribe:
   - Nombre del cliente
   - Número de documento
   - Teléfono
2. Aparecerá una lista con los clientes que coincidan
3. Haz clic en el cliente que deseas
4. Los campos de nombre y documento se llenarán automáticamente

**Opción 2: Cliente Sin Registro**
- Puedes dejar los campos de cliente vacíos
- Ingresa manualmente el nombre del cliente si lo conoces
- Los datos se guardarán automáticamente cuando agregues productos

### Agregar Productos a la Venta

1. En el campo **"Buscar Producto"**, escribe:
   - Nombre del producto (ej: "roller duo gris 60x200")
   - Código del producto (ej: "ROLLER-DUO-GRIS-60X200")
   - O simplemente parte del nombre (ej: "gris" o "60x200")
2. Aparecerá una lista con los productos que coincidan
3. Haz clic en el producto que deseas
4. Se mostrará información del producto:
   - Stock disponible
   - Precio de venta
5. Ingresa la **cantidad** que deseas vender
6. Haz clic en **"Agregar a la Venta"**

**Notas Importantes:**
- El sistema validará que haya suficiente stock disponible
- Si no hay suficiente stock, verás un mensaje de error
- El stock se reserva automáticamente cuando agregas un producto

### Ver Productos en la Venta

En la tabla "Productos en la Venta" verás:
- Nombre del producto
- Cantidad
- Precio unitario
- Subtotal (cantidad × precio)
- Botón para eliminar el producto

### Modificar una Venta

- **Cambiar cantidad**: Elimina el producto y agrégalo nuevamente con la cantidad correcta
- **Eliminar producto**: Haz clic en el icono de **basura** (🗑️) junto al producto

### Totales de la Venta

El sistema calcula automáticamente:
- **Subtotal**: Suma de todos los productos
- **IVA** (si aplica): Se calcula automáticamente
- **Total**: Monto final a pagar

### Completar la Venta

Cuando termines de agregar todos los productos:

1. Verifica que todos los datos estén correctos
2. Haz clic en **"Completar Venta"** (botón verde)
3. El sistema:
   - Descontará el stock de los productos
   - Generará un registro de movimiento de inventario
   - Cambiará el estado de la venta a "Completada"
   - Ya no podrás modificar la venta

### Cancelar una Venta (Solo Ventas Completadas)

Si necesitas cancelar una venta que ya fue completada:

1. Abre la venta desde la lista
2. Haz clic en **"Cancelar Venta"** (botón rojo)
3. El sistema:
   - Devolverá el stock de todos los productos
   - Generará un registro de movimiento de inventario
   - Cambiará el estado de la venta a "Cancelada"

### Eliminar una Venta Pendiente

Si quieres eliminar una venta que aún no ha sido completada:

1. Abre la venta desde la lista
2. Haz clic en **"Eliminar Venta"** (botón rojo)
3. Confirma que deseas eliminar la venta
4. La venta y todos sus productos serán eliminados

### Filtros y Búsqueda de Ventas

En la página de ventas puedes:

- **Filtrar por estado**: 
  - Pendientes (ventas sin completar)
  - Completadas (ventas finalizadas)
  - Canceladas (ventas canceladas)
  - Todas
  
- **Buscar por número de factura**: Ingresa el número en el campo de búsqueda

- **Filtrar por fecha**: Selecciona fecha desde y fecha hasta

### Ver Detalle de una Venta

Haz clic en cualquier venta de la lista para ver todos sus detalles en el modal.

---

## Gestión de Productos

### Ver Lista de Productos

1. En el menú lateral, haz clic en **"Productos"** (dentro de "Catálogo")
2. Verás una tabla con todos los productos del sistema

### Búsqueda de Productos

Puedes buscar productos por:
- **Nombre**: Escribe parte del nombre (ej: "gris", "roller duo")
- **Código**: Escribe el código del producto
- **Descripción**: Escribe palabras clave de la descripción

### Filtros de Productos

- **Por Categoría**: Selecciona una categoría del menú desplegable
- **Stock Bajo**: Marca la casilla para ver solo productos con stock bajo

### Crear un Nuevo Producto

1. Haz clic en **"Nuevo Producto"** (botón azul)
2. Completa el formulario:

   **Campos Obligatorios:**
   - **Código**: Código único del producto (ej: ROLLER-DUO-GRIS-60X200)
   - **Nombre**: Nombre completo del producto (ej: roller duo gris 60x200)
   - **Categoría**: Selecciona una categoría
   - **Proveedor**: Selecciona un proveedor
   - **Precio de Venta**: Precio al que se vende el producto

   **Campos Opcionales:**
   - **Descripción**: Descripción detallada del producto
   - **Precio de Compra**: Precio al que compras el producto
   - **Stock Actual**: Cantidad inicial en inventario
   - **Stock Mínimo**: Cantidad mínima antes de alertar (ej: 5)

3. Haz clic en **"Guardar Producto"**

**Importante:** Si ingresas un stock inicial, el sistema creará automáticamente un movimiento de entrada registrando ese stock.

### Editar un Producto

1. En la lista de productos, haz clic en **"Editar"** junto al producto
2. Modifica los campos que necesites
3. Haz clic en **"Actualizar Producto"**

### Ver Detalle de un Producto

Haz clic en **"Ver"** junto al producto para ver toda su información, incluyendo:
- Historial de movimientos
- Ventas relacionadas

### Stock Bajo

Los productos con stock bajo aparecen:
- En color amarillo/naranja en el Dashboard
- Con un icono de advertencia (⚠️)
- En la sección "Productos Bajo Stock" del Dashboard

---

## Gestión de Clientes

### Ver Lista de Clientes

1. En el menú lateral, haz clic en **"Clientes"** (dentro de "Relaciones")
2. Verás una tabla con todos los clientes registrados

### Búsqueda de Clientes

Usa el campo de búsqueda para encontrar clientes por:
- Nombre
- Número de documento
- Teléfono

### Crear un Nuevo Cliente

1. Haz clic en **"Nuevo Cliente"** (botón azul)
2. Se abrirá un modal (ventana emergente)
3. Completa el formulario:

   **Campos Obligatorios:**
   - **Nombre Completo**: Nombre del cliente

   **Campos Opcionales:**
   - **Documento**: Número de documento (DNI, RUT, etc.)
   - **Teléfono**: Número de contacto
   - **Email**: Correo electrónico
   - **Dirección**: Dirección del cliente
   - **Activo**: Marca esta casilla (debe estar marcada)

4. Haz clic en **"Guardar"**

### Editar un Cliente

1. En la lista de clientes, haz clic en **"Editar"** (botón azul)
2. Se abrirá el mismo modal con los datos del cliente
3. Modifica los campos que necesites
4. Haz clic en **"Guardar"**

### Ver Historial de Ventas de un Cliente

1. En la lista de clientes, haz clic en **"Ver"**
2. En la página de detalle verás:
   - Información del cliente
   - **Historial de Ventas**: Lista de todas las ventas realizadas a este cliente
   - Para cada venta verás: número de factura, fecha, total

---

## Gestión de Proveedores

### Ver Lista de Proveedores

1. En el menú lateral, haz clic en **"Proveedores"** (dentro de "Catálogo")
2. Verás una tabla con todos los proveedores

### Crear un Nuevo Proveedor

1. Haz clic en **"Nuevo Proveedor"** (botón azul)
2. Se abrirá un modal
3. Completa el formulario:

   **Campos Obligatorios:**
   - **Nombre**: Nombre del proveedor

   **Campos Opcionales:**
   - **Contacto**: Nombre de la persona de contacto
   - **Teléfono**: Número de contacto
   - **Email**: Correo electrónico
   - **Dirección**: Dirección del proveedor

4. Haz clic en **"Guardar"**

### Editar un Proveedor

1. En la lista de proveedores, haz clic en **"Editar"**
2. Modifica los campos necesarios
3. Haz clic en **"Guardar"**

---

## Gestión de Categorías

### Ver Lista de Categorías

1. En el menú lateral, haz clic en **"Categorías"** (dentro de "Catálogo")
2. Verás una tabla con todas las categorías

### Crear una Nueva Categoría

1. Haz clic en **"Nueva Categoría"** (botón azul)
2. Se abrirá un modal
3. Completa:
   - **Nombre**: Nombre de la categoría (ej: Cortinas, Servicios)
   - **Descripción**: Descripción opcional
4. Haz clic en **"Guardar"**

### Editar una Categoría

1. En la lista de categorías, haz clic en **"Editar"**
2. Modifica los campos necesarios
3. Haz clic en **"Guardar"**

---

## Reportes

Los reportes te permiten analizar la información de tu negocio.

### Acceder a Reportes

1. En el menú lateral, haz clic en **"Informes"**
2. Verás tarjetas con diferentes tipos de reportes disponibles

### Tipos de Reportes Disponibles

#### 1. Reporte de Ventas
- Muestra todas las ventas en un rango de fechas
- Puedes filtrar por fecha desde/hasta
- Muestra: número de factura, fecha, cliente, total

#### 2. Productos Más Vendidos
- Lista de productos ordenados por cantidad vendida
- Útil para saber qué productos son más populares
- Muestra: producto, cantidad vendida, total vendido

#### 3. Stock Bajo
- Lista de productos que necesitan reposición
- Muestra: producto, stock actual, stock mínimo
- Útil para planificar compras

#### 4. Reporte de Clientes
- Lista de clientes con su información y cantidad de ventas
- Ordenado por clientes más frecuentes
- Muestra: cliente, cantidad de ventas, total gastado

#### 5. Resumen General
- Vista general de métricas del sistema
- Productos totales, ventas del período, totales, etc.
- Útil para tener una visión general del negocio

### Filtrar Reportes

La mayoría de reportes permiten filtrar por **rango de fechas**:
1. Selecciona **"Fecha Desde"**
2. Selecciona **"Fecha Hasta"**
3. Haz clic en **"Buscar"** o **"Filtrar"**

---

## Movimientos de Inventario

Los movimientos registran todas las entradas y salidas de productos.

### Ver Movimientos

1. En el menú lateral, haz clic en **"Movimientos"** (dentro de "Historial")
2. Verás una tabla con todos los movimientos:
   - **Fecha**: Cuándo ocurrió el movimiento
   - **Producto**: Qué producto fue afectado
   - **Tipo**: Entrada (➕) o Salida (➖)
   - **Cantidad**: Cuántas unidades
   - **Motivo**: Razón del movimiento
   - **Usuario**: Quién realizó el movimiento

### Crear un Movimiento Manual

Si necesitas ajustar el inventario manualmente:

1. Haz clic en **"Nuevo Movimiento"** (botón azul)
2. Completa el formulario:
   - **Producto**: Selecciona el producto
   - **Tipo**: 
     - **Entrada**: Para agregar stock (ej: compra a proveedor)
     - **Salida**: Para quitar stock (ej: merma, pérdida, ajuste)
   - **Cantidad**: Cantidad a agregar o quitar
   - **Motivo**: Razón del movimiento (ej: "Compra a proveedor", "Ajuste de inventario", "Merma detectada")
   - **Fecha**: Fecha del movimiento (por defecto: hoy)

3. Haz clic en **"Guardar Movimiento"**

**Importante:** 
- Un movimiento de **Entrada** incrementa el stock
- Un movimiento de **Salida** disminuye el stock
- Los movimientos son automáticos cuando completas o cancelas una venta

---

## Gestión de Usuarios (Solo Admin)

⚠️ **Esta sección solo está disponible para usuarios con rol de Administrador.**

### Ver Lista de Usuarios

1. En el menú lateral, haz clic en **"Usuarios"** (dentro de "Administración")
2. Verás una tabla con todos los usuarios del sistema

### Crear un Nuevo Usuario

1. Haz clic en **"Nuevo Usuario"** (botón azul)
2. Completa el formulario:

   **Campos Obligatorios:**
   - **Nombre**: Nombre completo del usuario
   - **Email**: Correo electrónico (debe ser único)
   - **Contraseña**: Contraseña para iniciar sesión
   - **Confirmar Contraseña**: Repite la contraseña

   **Roles:**
   - Selecciona uno o más roles:
     - **Admin**: Acceso completo al sistema
     - **Vendedor**: Solo puede ver productos y crear ventas

   - **Activo**: Marca esta casilla para que el usuario pueda iniciar sesión

3. Haz clic en **"Crear Usuario"**

### Editar un Usuario

1. En la lista de usuarios, haz clic en **"Editar"**
2. Modifica los campos necesarios
3. Para cambiar la contraseña: completa los campos "Contraseña" y "Confirmar Contraseña"
4. Haz clic en **"Actualizar Usuario"**

### Desactivar un Usuario

Para impedir que un usuario inicie sesión sin eliminarlo:

1. Edita el usuario
2. Desmarca la casilla **"Activo"**
3. Guarda los cambios

El usuario no podrá iniciar sesión, pero sus datos y registros se mantendrán.

---

## Consejos y Buenas Prácticas

### Para Vendedores

1. **Antes de crear una venta**: Verifica que haya suficiente stock disponible
2. **Busca productos por código**: Si conoces el código, es más rápido que buscar por nombre
3. **Registra clientes**: Es útil registrar los clientes frecuentes para tener su historial
4. **Revisa antes de completar**: Verifica que todos los productos y cantidades estén correctos antes de completar la venta

### Para Administradores

1. **Revisa el Dashboard diariamente**: Para estar al tanto de productos bajo stock y ventas del día
2. **Revisa movimientos regularmente**: Para detectar inconsistencias en el inventario
3. **Actualiza precios**: Mantén los precios de productos actualizados
4. **Backup regular**: Realiza respaldos de la base de datos regularmente

### Recomendaciones Generales

- **Cierra sesión cuando termines**: Especialmente si trabajas en una computadora compartida
- **No compartas tu contraseña**: Cada usuario debe tener su propia cuenta
- **Reporta errores**: Si encuentras algún problema, repórtalo al administrador
- **Usa la búsqueda**: Aprovecha los campos de búsqueda para encontrar información rápidamente

---

## Preguntas Frecuentes (FAQ)

### ¿Qué pasa si agrego un producto a una venta pero no hay suficiente stock?

El sistema te mostrará un mensaje de error indicando cuántas unidades están disponibles. No podrás agregar más cantidad de la disponible.

### ¿Puedo editar una venta después de completarla?

No. Una vez que una venta está completada, solo puedes cancelarla (lo que devolverá el stock). Si necesitas hacer cambios menores, cancela la venta y crea una nueva.

### ¿Qué es el stock mínimo?

Es la cantidad mínima de un producto que quieres mantener en inventario. Cuando el stock baja a ese nivel, el producto aparecerá en la sección "Bajo Stock" del Dashboard.

### ¿Los movimientos se crean automáticamente?

Sí, cuando completas o cancelas una venta, el sistema crea automáticamente los movimientos correspondientes. Los movimientos manuales solo se crean cuando ajustas el inventario manualmente.

### ¿Puedo vender sin registrar un cliente?

Sí. Puedes crear ventas sin asociar un cliente. Sin embargo, es recomendable registrar clientes frecuentes para tener un historial de sus compras.

### ¿Cómo sé qué productos necesito comprar?

Revisa la sección "Productos Bajo Stock" en el Dashboard. También puedes usar el reporte "Stock Bajo" en el módulo de Informes.

---

## Glosario de Términos

- **Venta Pendiente**: Venta que aún no ha sido completada. Puede ser modificada o eliminada.
- **Venta Completada**: Venta finalizada. El stock ya fue descontado. Solo puede ser cancelada.
- **Stock Actual**: Cantidad de unidades disponibles actualmente de un producto.
- **Stock Mínimo**: Cantidad mínima recomendada de un producto en inventario.
- **Movimiento de Entrada**: Registro que indica que se agregó stock a un producto.
- **Movimiento de Salida**: Registro que indica que se quitó stock de un producto.
- **Dashboard**: Pantalla principal que muestra un resumen del sistema.
- **Modal**: Ventana emergente que aparece sobre la pantalla principal.

---

## Contacto y Soporte

Si tienes dudas o necesitas ayuda:

1. Consulta este manual
2. Contacta al administrador del sistema
3. Revisa la sección de "Preguntas Frecuentes" más arriba

---

**Última actualización**: Enero 2026

**Versión del Manual**: 1.0

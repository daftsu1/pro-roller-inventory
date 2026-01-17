# Solución: Error de Conexión a Base de Datos con ngrok

## 🔴 Problema

Cuando accedes a tu aplicación a través de ngrok, aparece este error:
```
SQLSTATE[HY000] [2002] No se puede establecer una conexión ya que el equipo de destino denegó expresamente dicha conexión
```

## ✅ Solución

El problema **NO es con ngrok**, sino que **MySQL no está corriendo** en XAMPP.

### Pasos para Solucionarlo:

1. **Abre el Panel de Control de XAMPP**
   - Busca "XAMPP Control Panel" en el menú de inicio
   - O navega a la carpeta donde instalaste XAMPP y ejecuta `xampp-control.exe`

2. **Inicia el servicio MySQL**
   - En el panel de XAMPP, busca la fila de "MySQL"
   - Haz clic en el botón **"Start"** (o "Iniciar")
   - Debería cambiar a color verde y mostrar "Running"

3. **Verifica que Apache también esté corriendo** (si usas XAMPP para servir Laravel)
   - Aunque normalmente usas `php artisan serve`, asegúrate de que Apache también esté activo si lo necesitas

4. **Verifica la conexión**
   - Abre phpMyAdmin: http://localhost/phpmyadmin
   - Deberías poder ver tu base de datos `joja_cola_inventario`

5. **Reinicia Laravel** (si estaba corriendo)
   ```powershell
   # Detén el servidor (Ctrl+C)
   # Luego inícialo de nuevo
   php artisan serve
   ```

6. **Prueba de nuevo la URL de ngrok**
   - Ahora debería funcionar correctamente

---

## 🔍 Verificación Rápida

### ¿Cómo saber si MySQL está corriendo?

**Opción 1: Panel de XAMPP**
- Abre XAMPP Control Panel
- Si MySQL muestra "Running" en verde ✅ = Está corriendo
- Si muestra "Stopped" en rojo ❌ = No está corriendo

**Opción 2: Desde PowerShell**
```powershell
# Verificar si MySQL está corriendo
Get-Service -Name "*mysql*" | Select-Object Name, Status
```

**Opción 3: Probar conexión manual**
```powershell
# Intentar conectar a MySQL
mysql -u root -h 127.0.0.1 -P 3306
```

---

## ⚠️ Problemas Comunes

### 1. MySQL no inicia en XAMPP

**Posibles causas:**
- Puerto 3306 ya está en uso por otro servicio
- Error en la configuración de MySQL
- Permisos insuficientes

**Solución:**
1. Cierra cualquier otra aplicación que use MySQL (Workbench, otros servidores MySQL, etc.)
2. Reinicia XAMPP como Administrador
3. Si persiste, revisa los logs de MySQL en `xampp/mysql/data/`

### 2. La base de datos no existe

Si MySQL está corriendo pero la base de datos no existe:

```sql
-- Conéctate a MySQL desde phpMyAdmin o línea de comandos
CREATE DATABASE joja_cola_inventario CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Luego ejecuta las migraciones:
```powershell
php artisan migrate
php artisan db:seed
```

### 3. Credenciales incorrectas en .env

Verifica que tu archivo `.env` tenga:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=joja_cola_inventario
DB_USERNAME=root
DB_PASSWORD=
```

---

## 📝 Checklist Antes de Usar ngrok

Antes de compartir tu aplicación con ngrok, asegúrate de:

- ✅ MySQL está corriendo en XAMPP
- ✅ La base de datos existe y tiene datos
- ✅ Laravel está corriendo (`php artisan serve`)
- ✅ Puedes acceder localmente a `http://localhost:8000`
- ✅ ngrok está corriendo y apuntando al puerto 8000
- ✅ `APP_URL` en `.env` está actualizado con la URL de ngrok (opcional pero recomendado)

---

## 🎯 Flujo Correcto

```powershell
# 1. Iniciar MySQL en XAMPP (desde el panel de control)

# 2. Iniciar Laravel
php artisan serve

# 3. Verificar que funciona localmente
# Abre: http://localhost:8000

# 4. Iniciar ngrok (en otra terminal)
ngrok http 8000

# 5. Copiar la URL de ngrok y actualizar .env
# APP_URL=https://tu-url-ngrok.ngrok-free.app

# 6. Probar la URL de ngrok en el navegador
```

---

## 💡 Nota Importante

**ngrok solo expone tu aplicación Laravel al internet**, pero:
- La base de datos sigue siendo **local** (en tu computadora)
- Solo tú (y quien tenga acceso a tu máquina) puede acceder a la base de datos
- Esto es **normal y seguro** para desarrollo/pruebas

Si necesitas que otros usuarios accedan a la base de datos también, necesitarías:
- Desplegar la aplicación en un servidor (Railway, Render, etc.)
- Configurar una base de datos en la nube (MySQL en la nube)

---

¡Con esto deberías poder acceder a tu aplicación desde ngrok sin problemas! 🚀

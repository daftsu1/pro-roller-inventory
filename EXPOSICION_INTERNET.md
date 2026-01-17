# Cómo Exponer la Aplicación al Internet

Existen varias formas de exponer tu aplicación Laravel al internet. Aquí te presento las opciones más comunes:

## 🚀 Opción 1: ngrok (Recomendado para pruebas rápidas)

**ngrok** es la herramienta más popular para crear túneles seguros a tu servidor local.

### Instalación y Uso:

1. **Descargar ngrok:**
   - Ve a https://ngrok.com/download
   - Descarga la versión para Windows
   - Extrae el archivo `ngrok.exe` en una carpeta (ej: `C:\ngrok\`)

2. **Crear cuenta gratuita (opcional pero recomendado):**
   - Regístrate en https://dashboard.ngrok.com/signup
   - Obtén tu authtoken

3. **Configurar ngrok:**
   ```powershell
   ngrok config add-authtoken TU_AUTH_TOKEN
   ```

4. **Iniciar tu servidor Laravel:**
   ```powershell
   php artisan serve
   ```
   (Normalmente corre en `http://127.0.0.1:8000`)

5. **Crear túnel:**
   ```powershell
   ngrok http 8000
   ```

6. **Obtener URL pública:**
   - ngrok te dará una URL como: `https://abc123.ngrok-free.app`
   - Esta URL es accesible desde cualquier lugar del mundo
   - La URL cambia cada vez que reinicias ngrok (a menos que tengas plan de pago)

### Ventajas:
- ✅ Muy fácil de usar
- ✅ HTTPS incluido
- ✅ Gratis para uso básico
- ✅ No requiere configuración del router

### Desventajas:
- ❌ URL cambia en cada reinicio (plan gratuito)
- ❌ Límite de conexiones simultáneas en plan gratuito

---

## 🌐 Opción 2: Cloudflare Tunnel (Gratuito e Ilimitado)

**Cloudflare Tunnel** (anteriormente Argo Tunnel) es completamente gratuito y sin límites.

### Instalación y Uso:

1. **Descargar cloudflared:**
   - Ve a https://github.com/cloudflare/cloudflared/releases
   - Descarga `cloudflared-windows-amd64.exe`
   - Renómbralo a `cloudflared.exe` y colócalo en una carpeta

2. **Iniciar túnel:**
   ```powershell
   cloudflared tunnel --url http://127.0.0.1:8000
   ```

3. **Obtener URL:**
   - Te dará una URL como: `https://random-words-1234.trycloudflare.com`
   - Esta URL es accesible desde cualquier lugar

### Ventajas:
- ✅ Completamente gratuito
- ✅ Sin límites de conexiones
- ✅ HTTPS incluido
- ✅ URLs más estables que ngrok gratuito

### Desventajas:
- ❌ URL cambia en cada reinicio
- ❌ Requiere descargar un ejecutable

---

## 🔧 Opción 3: localtunnel (Alternativa simple)

**localtunnel** es otra opción gratuita basada en Node.js.

### Instalación y Uso:

1. **Instalar Node.js** (si no lo tienes):
   - Descarga desde https://nodejs.org/

2. **Instalar localtunnel globalmente:**
   ```powershell
   npm install -g localtunnel
   ```

3. **Iniciar túnel:**
   ```powershell
   lt --port 8000
   ```

4. **Obtener URL:**
   - Te dará una URL como: `https://random-name.loca.lt`

### Ventajas:
- ✅ Gratis
- ✅ Fácil de usar
- ✅ Puedes elegir un subdominio personalizado: `lt --port 8000 --subdomain mi-app`

### Desventajas:
- ❌ Requiere Node.js
- ❌ Puede ser menos estable que ngrok

---

## ☁️ Opción 4: Deploy a Producción (Para uso permanente)

Si quieres una solución permanente, puedes desplegar tu aplicación en servicios de hosting:

### Opciones de Hosting Gratuito/Barato:

1. **Railway** (https://railway.app)
   - Plan gratuito disponible
   - Deploy automático desde GitHub
   - Base de datos incluida

2. **Render** (https://render.com)
   - Plan gratuito disponible
   - Deploy desde GitHub
   - Base de datos PostgreSQL gratuita

3. **Heroku** (https://www.heroku.com)
   - Plan gratuito limitado
   - Muy fácil de usar

4. **Vercel** (https://vercel.com)
   - Gratis para proyectos personales
   - Deploy automático

### Pasos generales para deploy:
1. Subir código a GitHub
2. Conectar repositorio con el servicio de hosting
3. Configurar variables de entorno
4. Configurar base de datos
5. Deploy automático

---

## ⚠️ Problema Común: Error de Base de Datos

**Si ves este error al acceder desde ngrok:**
```
SQLSTATE[HY000] [2002] No se puede establecer una conexión...
```

**Solución:** MySQL no está corriendo en XAMPP. 
- Abre XAMPP Control Panel
- Inicia el servicio MySQL (botón "Start")
- Verifica que esté en verde "Running"
- Reinicia Laravel si estaba corriendo

**📄 Ver guía completa:** [SOLUCION_ERROR_BASE_DATOS.md](SOLUCION_ERROR_BASE_DATOS.md)

---

## ⚠️ Consideraciones Importantes

### Seguridad:
- **NUNCA** expongas tu aplicación con datos sensibles en producción usando túneles temporales
- Los túneles son para **desarrollo y pruebas** únicamente
- Para producción, usa un servicio de hosting profesional

### Configuración de Laravel:
Si usas túneles, asegúrate de configurar:

1. **APP_URL en .env (IMPORTANTE):**
   
   **¿Por qué actualizarlo?**
   - Laravel usa `APP_URL` para generar URLs absolutas (enlaces, redirecciones, emails)
   - Si no lo actualizas, puede generar URLs incorrectas con `http://localhost`
   
   **Cómo hacerlo:**
   ```env
   APP_URL=https://tu-url-ngrok.ngrok-free.app
   ```
   
   **⚠️ Nota:** Como ngrok cambia la URL cada vez que lo reinicias, tendrás que:
   - Actualizar `APP_URL` en `.env` cada vez que inicies ngrok con una nueva URL
   - O usar el método alternativo abajo
   
   **Alternativa práctica (sin editar .env cada vez):**
   - Puedes dejar `APP_URL=http://localhost` y Laravel intentará detectar la URL automáticamente
   - Funciona para la mayoría de casos, pero puede fallar en algunos escenarios (emails, webhooks, etc.)
   - Si tienes problemas, actualiza `APP_URL` manualmente

2. **Trust Proxies:**
   - Laravel ya tiene `TrustProxies` middleware configurado ✅
   - Está listo para funcionar con ngrok

3. **CORS (si usas API):**
   - Configura los dominios permitidos en `config/cors.php`

### Performance:
- Los túneles pueden tener latencia adicional
- No son ideales para producción con mucho tráfico

---

## 🎯 Recomendación Rápida

**Para pruebas rápidas:** Usa **ngrok** o **Cloudflare Tunnel**
**Para producción:** Usa **Railway** o **Render**

---

## 📝 Ejemplo Rápido con ngrok

```powershell
# 1. Iniciar Laravel
php artisan serve

# 2. En otra terminal, iniciar ngrok
ngrok http 8000

# 3. Copiar la URL HTTPS que te da ngrok (ej: https://abc123.ngrok-free.app)
# 4. Actualizar APP_URL en .env con esa URL
#    Edita .env y cambia: APP_URL=https://abc123.ngrok-free.app
# 5. Compartir esa URL con quien quieras que vea la app
```

**Flujo completo paso a paso:**
1. Inicia Laravel: `php artisan serve`
2. Inicia ngrok: `ngrok http 8000`
3. Copia la URL HTTPS que aparece (ej: `https://abc123.ngrok-free.app`)
4. Abre `.env` y actualiza: `APP_URL=https://abc123.ngrok-free.app`
5. ¡Listo! Comparte la URL de ngrok

**💡 Tip:** Si solo vas a hacer pruebas rápidas y no usas emails/webhooks, puedes dejar `APP_URL=http://localhost` y funcionará en la mayoría de casos.

¡Listo! Tu aplicación estará accesible desde internet. 🌍

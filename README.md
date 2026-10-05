Este proyecto es una plantilla de laravel que sirva como base para futuros proyectos

## Módulo de Respaldos Automáticos

Para utilizar el módulo de respaldos automáticos del sistema, siga los siguientes pasos:

1. **Configurar las variables de entorno**:
   - Añade la siguiente variable en tu archivo `.env` para definir el disco de almacenamiento de respaldos:
     ```env
     BACKUP_FILESYSTEM_DISK=local
     ```
     - Puedes usar cualquier disco configurado en `config/filesystems.php` (por ejemplo: `local`, `public`, `s3`, etc.).
     - Si usas S3 u otro servicio, asegúrate de configurar también las variables correspondientes (por ejemplo, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, etc.).

2. **Configurar el disco de respaldos en Laravel**:
   - En el archivo `config/filesystems.php` ya existe una sección llamada `backup`. Asegúrate de que esté así:
     ```php
     'backup' => [
         'driver' => env('BACKUP_FILESYSTEM_DISK', 'local'),
     ],
     ```
   - Puedes personalizar los parámetros según el driver que elijas.

3. **Activar el Task Scheduler de Laravel**:
   - Para que los respaldos automáticos se ejecuten, es necesario activar el Task Scheduler de Laravel.
   - Puede hacerlo de las siguientes maneras:
     - **En entornos de desarrollo** (no recomendado en producción):
       Ejecute el siguiente comando para mantener activo el proceso:
       ```bash
       php artisan schedule:work
       ```
     - **En entornos de producción** (recomendado):
       Configure un cron job en Linux con la siguiente línea:
       ```bash
       * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
       ```

## ⚡ Tablero Kanban y Modos de Sincronización

El Tablero Kanban de Administración de Tickets cuenta con un sistema de sincronización dual adaptable a la potencia y arquitectura de tu infraestructura:

### 1. Modo Tiempo Real (WebSockets con Laravel Reverb)
Recomendado para servidores VPS, servidores dedicados o contenedores Docker. Permite que múltiples técnicos y administradores vean movimientos de tarjetas, cambios de prioridad y resoluciones al instante sin recargar la página y sin saturar el servidor con peticiones HTTP.

- **Variables de entorno requeridas en `.env`**:
  ```env
  BROADCAST_CONNECTION=reverb
  REVERB_APP_ID=462547
  REVERB_APP_KEY=bavvfvsrzmxcqc1zf2fq
  REVERB_APP_SECRET=dhsrbum3hckwqkrkof48
  REVERB_HOST="localhost"
  REVERB_PORT=8080
  REVERB_SCHEME=http

  VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
  VITE_REVERB_HOST="${REVERB_HOST}"
  VITE_REVERB_PORT="${REVERB_PORT}"
  VITE_REVERB_SCHEME="${REVERB_SCHEME}"
  ```

- **Iniciar el servidor WebSocket**:
  - **En desarrollo**:
    ```bash
    php artisan reverb:start
    ```
  - **En producción (Guía para Ubuntu / Debian)**:
    Sigue las instrucciones detalladas a continuación para configurar Supervisor y Nginx.

#### A. Configuración con Supervisor (Recomendado en Producción)
Supervisor mantiene el servicio `reverb:start` siempre activo y lo reinicia automáticamente ante fallos o reinicios del servidor.

1. **Instalar Supervisor**:
   ```bash
   sudo apt update
   sudo apt install supervisor -y
   ```

2. **Crear archivo de configuración (`/etc/supervisor/conf.d/reverb.conf`)**:
   ```ini
   [program:reverb]
   process_name=%(program_name)s
   command=php /var/www/tu-proyecto/artisan reverb:start
   autostart=true
   autorestart=true
   user=www-data
   redirect_stderr=true
   stdout_logfile=/var/www/tu-proyecto/storage/logs/reverb.log
   stopwaitsecs=3600
   ```

3. **Cargar e iniciar el servicio**:
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start reverb
   sudo supervisorctl status reverb
   ```

#### B. Configuración de Proxy Inverso en Nginx (SSL / WSS)
Permite conectar mediante `wss://` utilizando el certificado SSL del dominio sin exponer puertos adicionales.

Edita tu bloque `server` de Nginx (ej. `/etc/nginx/sites-available/tickets.conf`):
```nginx
server {
    server_name tickets.tudominio.com;
    root /var/www/tu-proyecto/public;
    index index.php;

    # ... Configuración estándar de Laravel (location / { ... }, location ~ \.php { ... })

    # Proxy Inverso para WebSockets de Laravel Reverb
    location /app {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        proxy_read_timeout 60s;
        proxy_send_timeout 60s;
    }
}
```
Aplica los cambios:
```bash
sudo nginx -t && sudo systemctl reload nginx
```

#### C. Variables `.env` en Producción (con HTTPS)
```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=462547
REVERB_APP_KEY=bavvfvsrzmxcqc1zf2fq
REVERB_APP_SECRET=dhsrbum3hckwqkrkof48
REVERB_HOST="127.0.0.1"
REVERB_PORT=8080
REVERB_SCHEME=http

# Configuración que consumirá el cliente (Vite / Echo)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="tickets.tudominio.com"
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME="https"
```

#### D. Alternativa con Systemd (Sin Supervisor)
Crea `/etc/systemd/system/reverb.service`:
```ini
[Unit]
Description=Laravel Reverb WebSocket Server
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/tu-proyecto
ExecStart=/usr/bin/php /var/www/tu-proyecto/artisan reverb:start
Restart=always
RestartSec=5s
StandardOutput=append:/var/www/tu-proyecto/storage/logs/reverb.log
StandardError=append:/var/www/tu-proyecto/storage/logs/reverb.log

[Install]
WantedBy=multi-user.target
```
Inicia y habilita:
```bash
sudo systemctl daemon-reload
sudo systemctl enable reverb --now
sudo systemctl status reverb
```

#### E. Flujo de Despliegue / Actualizaciones
Tras actualizar código en producción (`git pull`), reinicia Reverb para cargar los nuevos eventos:
```bash
php artisan config:clear
sudo supervisorctl restart reverb
```

---

### 2. Modo Estándar / Compatible (Polling Inteligente)
Ideal para entornos con recursos limitados o hostings compartidos (cPanel) donde no se pueden ejecutar procesos permanentes (daemons).

- No requiere el servidor Reverb corriendo.
- El tablero consulta periódicamente los cambios mediante HTTP cada 5s, 10s, 30s o en modo manual bajo demanda.
- Coexiste perfectamente con el cron tradicional `php artisan schedule:run` encargado de los respaldos automáticos.
- El usuario puede alternar entre ambos modos directamente desde el selector de la cabecera del Kanban.
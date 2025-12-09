# Guía de Implementación VPS: Plugin MandatoryMFA

Esta guía detalla los pasos para instalar y activar el plugin `MandatoryMFA` en un entorno de producción/staging (VPS).

## 1. Requisitos Previos

*   **Plugin Base**: Debe estar instalado `MultipleLocalAuth`.
*   **Acceso**: SSH al servidor VPS y permisos de root/sudo.

## 2. Instalación de Código

### Opción A: Vía Git (Recomendado)
Si el repositorio ya está publicado:

```bash
cd /ruta/a/mapasculturais/plugins
git clone https://github.com/LibreCoopUruguay/plugin-MandatoryMFA.git MandatoryMFA
```

### Opción B: Copia Manual (SCP/Rsync)
Si el código aún no está público, subir la carpeta desde local:

```bash
# Desde tu máquina local
scp -r plugins/MandatoryMFA usuario@vps:/ruta/a/mapasculturais/plugins/
```

> **IMPORTANTE**: La carpeta destino **DEBE** llamarse `MandatoryMFA`.

## 3. Configuración

### 3.1. Registrar Plugins (`config.d/plugins.php`)

Editar el archivo de configuración de plugins (usualmente en `dev/config.d/plugins.php` o `docker/prod/plugins.php` según tu estructura).

Asegurarse de que ambos plugins estén registrados:

```php
return [
    'plugins' => [
        // Dependencia obligatoria
        'MultipleLocalAuth' => [ 'namespace' => 'MultipleLocalAuth' ],
        
        // Plugin MFA
        'MandatoryMFA' => [ 'namespace' => 'MandatoryMFA' ],
        
        // ... otros plugins
    ]
];
```

### 3.2. Activar Proveedor de Identidad (`config.d/auth.php`)

Editar `config.d/auth.php` para cambiar el proveedor activo:

```php
return [
    // Cambiar \MultipleLocalAuth\Provider por:
    'auth.provider' => '\MandatoryMFA\Provider',
    
    // Mantener resto de configuración...
];
```

## 4. Configuración Docker (docker-compose.yml)

Asegurarse de que el volumen del nuevo plugin esté montado en el contenedor.

```yaml
services:
  mapas:
    volumes:
      # ...
      - ../plugins/MultipleLocalAuth:/var/www/src/plugins/MultipleLocalAuth
      - ../plugins/MandatoryMFA:/var/www/src/plugins/MandatoryMFA  # <--- AGREGAR ESTA LÍNEA
```

## 5. Aplicar Cambios

Reiniciar el contenedor y limpiar caché:

```bash
# Reiniciar servicios
docker compose restart mapas

# Limpiar caché interno de Mapas Culturais
docker compose exec mapas rm -rf /var/www/files/cache/*
```

## 6. Verificación

1.  Acceder al sitio web.
2.  Intentar iniciar sesión.
3.  Debería solicitar el código de verificación (MFA) obligatoriamente.

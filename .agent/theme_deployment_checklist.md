# Guía de Despliegue: Migración de Tema (Legacy → Moderno)

Esta guía detalla paso a paso cómo desplegar la nueva versión del tema `themeCulturaenlinea` (Zero Overrides) en un servidor que actualmente corre la versión antigua (php-mod).

> [!WARNING]
> **Riesgo Medio**: Este procedimiento implica cambios en `Dockerfile` y configuración. Se recomienda realizar un **backup completo** antes de empezar.

## 1. Preparación y Backup

1.  **Backup de Base de Datos y Archivos**:
    ```bash
    # En el servidor (RAÍZ DEL PROYECTO)
    cd /ruta/a/mapasculturais
    tar -czf backup-pre-migracion-$(date +%F).tar.gz dev/ docker/ themes/ plugins/
    # Backup de BD (si aplica)
    docker compose exec db pg_dump -U mapas -d mapas > backup-db-$(date +%F).sql
    ```

## 2. Actualización de Código

2.  **Actualizar el Repositorio Base**:
    Asegúrate de estar en la rama correcta (`develop-lt` o `master`).
    ```bash
    git pull origin develop-lt
    ```

3.  **Actualizar el Submódulo del Tema**:
    ```bash
    git submodule update --init --recursive
    cd themes/themeCulturaenlinea
    git fetch origin
    git checkout dev-lt
    git pull origin dev-lt
    ```

## 3. Limpieza de "php-mod" (Crítico)

La versión antigua usaba `php-mod` para sobrescribir archivos del núcleo. La nueva versión NO lo usa. Debemos asegurar que el contenedor no siga copiando esos archivos viejos.

4.  **Editar `Dockerfile` (o `docker/Dockerfile`)**:
    Busca y **ELIMINA** o **COMENTA** líneas como:
    ```dockerfile
    # ❌ ELIMINAR ESTAS LÍNEAS
    # COPY themes/themeCulturaenlinea/php-mod/MapasCulturais/...
    # COPY themes/themeCulturaenlinea/php-mod/ng-mapas/...
    ```
    
    > **Nota**: Solo debes dejar la copia general del tema:
    > `COPY themes /var/www/src/themes`

## 4. Configuración del Tema

5.  **Verificar Configuración**:
    Edita tu archivo de configuración activo (ej. `dev/config.d/0.main.php` o `config.php`).
    Asegúrate de que el tema esté activo y la configuración legacy de tipos de agente/espacio esté limpia (el tema nuevo los carga automáticamente).

    ```php
    return [
        'themes.active' => 'themeCulturaenlinea',
        // ...
    ];
    ```

## 5. Reconstrucción y Despliegue

6.  **Reconstruir el Contenedor**:
    Como cambiamos el `Dockerfile` y actualizamos código, debemos reconstruir.
    Como cambiamos el `Dockerfile` y actualizamos código, debemos reconstruir.
    
    > **IMPORTANTE**: Ejecutar desde la RAÍZ del proyecto (donde está el docker-compose.yml de producción). NO entrar a `dev/`.

    ```bash
    # Asegúrate de estar en la raíz
    pwd 
    # /var/www/mapasculturais (ejemplo)

    docker compose down
    docker compose build --no-cache
    docker compose up -d
    ```

7.  **Limpiar Caché**:
    Es vital para que el sistema reconozca los nuevos hooks y assets.
    ```bash
    docker compose exec mapas rm -rf /var/www/files/cache/*
    docker compose exec mapas rm -rf /var/www/var/cache/*
    ```

## 6. Verificación

8.  **Verificar Funcionamiento**:
    - Entra al sitio.
    - Verifica que el **Footer** diga "Desarrollado por Libre Coop" (indicador del nuevo JS).
    - Verifica un formulario de edición de Agente. Los campos como "Nome Social" o "CPF Anexos" deberían estar ocultos.
    - Verifica que las máscaras de CI/RUT funcionen (ej. al escribir `12345678` se formatee).

## Mantenimiento Futuro

Para futuras actualizaciones, solo necesitarás:
```bash
# Actualizar código
./theme-update.sh

# Reiniciar
docker compose restart mapas
```

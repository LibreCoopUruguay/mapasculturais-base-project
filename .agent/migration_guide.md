# Guía de Migración 2025

Este documento detalla los pasos necesarios para migrar instalaciones existentes del proyecto base a la nueva arquitectura de plugins y configuración.

## 1. Plugin MandatoryMFA Standalone

El plugin `MandatoryMFA` se ha separado de `MultipleLocalAuth` y ahora vive en su propio repositorio.

### Qué cambió
- **Antes**: `MandatoryMFA` era una rama o carpeta dentro de `MultipleLocalAuth`.
- **Ahora**: `MandatoryMFA` es un repositorio independiente. `MultipleLocalAuth` se mantiene como dependencia base, pero sin modificaciones manuales ("hacks").

### Acción Requerida
Asegúrese de clonar el nuevo repositorio en su carpeta de plugins:
```bash
cd plugins
git clone https://github.com/LibreCoopUruguay/plugin-MandatoryMFA.git MandatoryMFA
```

## 2. Limpieza del Dockerfile

Si está utilizando un contendor Docker personalizado (como en entornos de VPS o test), es crítico actualizar su `Dockerfile` para dejar de sobrescribir archivos del plugin.

### Problema
Versiones anteriores del Dockerfile copiaban manualmente archivos modificados ("parches") sobre la instalación del plugin:
```dockerfile
# ❌ ESTO GENERA ERRORES Y DEBE ELIMINARSE
# Modificaciones en /src/plugins/MultipleLocalAuth
COPY themes/themeCulturaenlinea/php-mod/MultipleLocalAuth/Provider.php ...
COPY themes/themeCulturaenlinea/php-mod/MultipleLocalAuth/components/login/template.php ...
...
```

### Solución
**Elimine todo el bloque** referente a "Modificaciones en /src/plugins/MultipleLocalAuth".
La nueva arquitectura carga las personalizaciones directamente desde el plugin `MandatoryMFA`, por lo que ya no es necesario (ni posible, pues los archivos fuente ya no existen) sobrescribir el código base.

Su Dockerfile debe confiar en la copia general de plugins:
```dockerfile
COPY plugins /var/www/src/plugins
```
Esto incluirá automáticamente tanto `MultipleLocalAuth` como `MandatoryMFA`.

## 3. Configuración de Imagen de Email

Si experimenta problemas con la imagen de cabecera en los emails, asegúrese de tener configurada la variable o confíe en el fail-safe automático:

- **Producción**: Defina `AUTH_EMAIL_IMAGE` en su `.env` o configuración del servidor.
- **Fail-safe**: Si olvida configurarlo, el plugin `MandatoryMFA` ahora inyectará automáticamente la imagen local `lc-login.png` para evitar enlaces rotos o imágenes genéricas.

## 4. Configuración de Tipos de Entidad (Legacy php-mod)

Los archivos de configuración que antes vivían en `php-mod/` (como `agent-types.php`, `space-types.php`) ahora residen en la raíz del tema (`themes/themeCulturaenlinea/`).

**IMPORTANTE:** Estos archivos se cargan automáticamente al activar el tema. **NO COPIE** estos archivos a `src/conf/` en su Dockerfile, ya que usan herencia y causarían un error fatal (bucle infinito si intentan cargarse a sí mismos).

### Solución
Simplemente asegúrese de que su archivo `config.php` active el tema correctamente:

```php
'themes.active' => 'themeCulturaenlinea',
```

El núcleo de Mapas Culturais se encargará de cargar los tipos de entidad definidos en la carpeta del tema activo.

## 5. Recomendaciones para Instalaciones Legacy (php-mod)

**Definición de "Legacy"**: Nos referimos a instalaciones que aún basan su despliegue en la carpeta `themes/themeCulturaenlinea/php-mod` y cuyo `Dockerfile` copia manualmente estos archivos para sobrescribir el núcleo o plugins ("parcheo").

Para estas instalaciones antiguas, existen riesgos importantes de compatibilidad si se mezclan con las nuevas actualizaciones.

### 🛑 Lo que NO debe hacer (Riesgo de Rotura)
*   **No actualice el Repo Base**: 
    *   **Repositorio**: `LibreCoopUruguay/mapasculturais-base-project`
    *   **Ramas**: `develop-lt` y `main` (o `master`).
    *   **Riesgo**: Estas ramas ya **borraron** `php-mod`. Si hace `git pull` en un entorno legacy, perderá los archivos fuente que su Dockerfile intenta copiar.
*   **No mezcle plugins**:
    *   **Repositorio**: `LibreCoopUruguay/plugin-MandatoryMFA`
    *   **Riesgo**: No intente instalar este plugin si su `MultipleLocalAuth` aún tiene el código "parcheado". Duplicará la funcionalidad y causará conflictos.

### Estrategia para Entornos "Vivos" con php-mod
Si tiene un VPS en producción funcionando con el método antiguo:

1.  **NO haga `git pull`** del proyecto base todavía. Manténgalo en su versión actual hasta preparar la migración.
### Estrategia Híbrida (Quirúrgica) - RECOMENDADA PARA PRODUCCIÓN
Si necesita mantener el entorno legacy pero activar `MandatoryMFA` ya mismo:

1.  **Edite su Dockerfile**: Busque y elimine/comente **SOLAMENTE** las líneas que copian archivos dentro de `plugins/MultipleLocalAuth`.
    *   *Deje intactas* las copias de `themes/`, `conf/` u otros parches que su sitio necesite para funcionar.
    *   *Objetivo*: Limpiar solo la autenticación para que no choque con el plugin.

2.  **Instale MandatoryMFA**: Clone el plugin nuevo.
3.  **Resultado**: Su sitio sigue siendo "Legacy" en estructura, pero usa el sistema de autenticación "Moderno".

### Estrategia de Migración Completa (Ideal)
Si puede preparar un entorno nuevo:
1.  **Prepare un entorno de Staging**: Clone la nueva versión (`develop-lt`), limpie su Dockerfile siguiendo el punto 2 de esta guía, e instale el plugin `MandatoryMFA` como se indica en el punto 1.
3.  **Abandone `php-mod`**: Considere la carpeta `php-mod` como **código depreciado**. Futuras actualizaciones del tema se harán directamente en `themes/themeCulturaenlinea`, no allí.

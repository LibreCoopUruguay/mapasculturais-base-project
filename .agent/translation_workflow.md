# Flujo de Trabajo: Actualización de Traducciones

Este documento describe el procedimiento para actualizar las cadenas de traducción cuando hay cambios en el núcleo de Mapas Culturais o en los plugins.

## Prerrequisitos
- **Poedit** instalado.
- Carpeta `temp_core/` disponible en la raíz del proyecto (usada para escanear cadenas del núcleo sin ensuciar el repositorio).

## Flujo Paso a Paso

### 1. Actualizar el Código Fuente (`temp_core`)

Antes de escanear, necesitamos asegurar que `temp_core` tenga la última versión del código.

**Opción A: Script Automático (Recomendado)**
Hemos creado un script que hace todo el trabajo sucio por ti. Simplemente ejecuta en la raíz del proyecto:

```bash
./actualizar_traduccion.sh
```

Este script:
1.  Verifica que Docker esté corriendo.
2.  Limpia `temp_core`.
3.  Copia automáticamente el código nuevo desde tu contenedor.

> **Nota**: Este script está en `.gitignore`, así que es seguro tenerlo localmente sin ensuciar el repo.

**Opción Manual (si el script falla):**

**Opción B: Si quieres bajar una versión específica de GitHub**
Si prefieres bajar el código crudo desde GitHub (ej. rama `master`):

```bash
# Limpiar
rm -rf temp_core/*

# Clonar (solo profundidad 1 para rapidez)
git clone --depth 1 https://github.com/mapasculturais/mapasculturais.git temp_core/core_repo
mv temp_core/core_repo/* temp_core/
rm -rf temp_core/core_repo temp_core/.git
```

### 2. Actualizar Catálogo en Poedit

1.  Abre el archivo de traducción de tu tema:
    `themes/themeCulturaenlinea/translations/es_ES.po`
2.  En Poedit, ve a **Catálogo > Propiedades > Rutas de fuentes**.
3.  Asegúrate de que las rutas incluyan:
    - `.` (carpeta del tema)
    - `../../../temp_core` (núcleo de Mapas)
    - `../../../plugins` (tus plugins locales)
4.  Ve a **Palabras clave** y verifica que `i::_e` esté presente.
5.  Haz clic en **Actualizar desde código**.

Poedit escaneará `temp_core` (el núcleo nuevo) y tu tema, y te mostrará las cadenas nuevas o eliminadas.

### 3. Traducir y Guardar

1.  Traduce las nuevas cadenas.
2.  Guarda el archivo. Esto generará `es_ES.mo` automáticamente.

### 4. Verificar en el Tema

El nuevo tema carga automáticamente este archivo `.mo` desde su propia carpeta.

1.  Reinicia el contenedor para limpiar cachés de traducción (opcional pero recomendado):
    ```bash
    docker compose restart mapas
    ```
2.  Verifica los cambios en el navegador.

## Resumen
1. Actualizar `temp_core` (copiar del docker o git clone).
2. Poedit -> Actualizar desde código.
3. Traducir y Guardar.
4. Reiniciar Docker.

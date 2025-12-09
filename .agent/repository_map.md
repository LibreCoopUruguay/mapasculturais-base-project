# Mapa de Repositorios y Ramas

Este documento describe la estructura actual de repositorios para el proyecto **Cultura en Línea (Uruguay)**, detallando su propósito y estrategia de ramas.

## 1. Proyecto Base (`mapasculturais-base-project`)

Es el repositorio principal que contiene el `Dockerfile`, configuración de entorno (`compose`), temas y scripts de despliegue.

*   **URL**: `https://github.com/LibreCoopUruguay/mapasculturais-base-project`

### Ramas
*   **`develop-lt`** (Desarrollo Leonardo Trujillo):
    *   **Uso**: Rama principal de desarrollo y staging. Contiene la última versión limpia sin `php-mod`.
    *   **Estado**: Activa. Todas las PRs deben ir aquí.
*   **`master`**:
    *   **Uso**: Producción estable.
    *   **Estrategia**: Se actualiza mediante merges desde `develop-lt` cuando se libera una versión.

### ⚠️ Advertencia Legacy
No utilice ramas antiguas que contengan la carpeta `themes/themeCulturaenlinea/php-mod` junto con scripts de despliegue modernos, ya que generará conflictos.

---

## 2. Plugins

### MandatoryMFA (`plugin-MandatoryMFA`)
Plugin independiente que gestiona la autenticación obligatoria y la lógica de "Sello Certificador".

*   **URL**: `https://github.com/LibreCoopUruguay/plugin-MandatoryMFA`

### Ramas
*   **`master`**:
    *   **Uso**: Código estable para producción.
    *   **Cuándo usar**: En entornos productivos declarándolo en su script de instalación.
*   **`develop`**:
    *   **Uso**: Desarrollo continuo de nuevas features (ej. mejoras en email, traducciones).
    *   **Cuándo usar**: En entornos de prueba para validar fixes recientes.

### Cómo usar este repo
Dentro de la carpeta `plugins/` de su instalación:
```bash
git clone https://github.com/LibreCoopUruguay/plugin-MandatoryMFA.git MandatoryMFA
```

---

### MultipleLocalAuth
Plugin base del que depende MandatoryMFA. Provee la autenticación local (Gov.br/ID Uruguay simulado).

*   **Repositorio**: Usualmente viene integrado o se usa el fork de LibreCoop.
*   **Nota**: `MandatoryMFA` asume que `MultipleLocalAuth` está instalado en `plugins/MultipleLocalAuth`.

---

## 3. Flujo de Trabajo Recomendado

1.  **Desarrollo**:
    *   Hacer cambios en `plugin-MandatoryMFA` (rama `develop`).
    *   Probar en entorno local montado con `mapasculturais-base-project` (rama `develop-lt`).
2.  **Release**:
    *   Merge `develop` -> `master` en el plugin.
    *   Actualizar referencias en producción.

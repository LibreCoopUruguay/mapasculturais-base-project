# PROMPT DE CONTEXTO - Mapas Culturais Base Project

Este documento sirve como "Brain Dump" para inicializar a cualquier agente de IA con el contexto necesario sobre este proyecto.

---

## 1. Identidad del Proyecto
**Nombre**: `mapasculturais-base-project` (Implementación: Cultura en Línea - Uruguay)
**Propósito**: Wrapper de despliegue y control de versiones para la plataforma Mapas Culturais.
**Stack Tecnológico**:
- **Core**: PHP (Mapas Culturais v6/v7).
- **Base de Datos**: PostgreSQL 14 + PostGIS.
- **Cache/Sesiones**: Redis.
- **Servidor Web**: Nginx.
- **Contenedores**: Docker & Docker Compose.

## 2. Arquitectura de Archivos
*   `/dev`: Entorno de desarrollo. Usa `docker-compose.yml` específico que monta volúmenes locales.
*   `/docker`: Configuraciones de producción (Nginx, PHP confs).
*   `/themes`: Temas personalizados. Submódulos de Git o carpetas simples.
*   `/plugins`: Plugins personalizados. Submódulos o carpetas.
*   `*.sh`: Scripts de utilidad en la raíz (`start.sh`, `update.sh`, `init-letsencrypt.sh`).

## 3. Componentes Críticos
### 3.1 Tema Principal: `themeCulturaenlinea`
- Ubicación: `themes/themeCulturaenlinea`.
- **Hereda de**: `BaseV2`.
- **Personalización**:
    - Oculta campos de Brasil (CPF, RG, etc) vía CSS inyectado en hooks.
    - Implementa máscaras y validaciones para documentos de Uruguay (`js/uruguay-masks.js`).
    - Configuración geográfica: `uruguay.php`.

### 3.2 Plugin: `MultipleLocalAuth`
- **Ubicación**: `plugins/MultipleLocalAuth`.
- **Propósito**: Gestión centralizada de autenticación (Login nativo mejorado + Redes Sociales) y creación de cuentas.
- **Características**:
    - **Registro de Usuarios**: Valida unicidad de email/CPF, complejidad de contraseña y ReCAPTCHA.
    - **Variables de Entorno (.env)**:
        - `AUTH_LOGIN_BY_CPF`: Habilitar/deshabilitar login por documento.
        - `GOOGLE_RECAPTCHA_SITEKEY` / `SECRET`: Configuración de ReCAPTCHA.
        - `AUTH_PASS_*`: Reglas de complejidad de contraseña (longitud, mayúsculas, números, etc.).
    - **Vistas**: `views/auth/` (registro, login, recuperar contraseña).
    - **Clase Principal**: `Provider.php`. Controla el flujo `auth.register`, `auth.login`, `auth.recover`.
- **Otros Plugins**: `AdminLoginAsUser`.

## 4. Estado Actual (Snapshot)
- **Repositorio**: Limpio (sin commits pendientes en `master`/`develop`).
- **Submódulos**: `plugins/MultipleLocalAuth` y `themes/themeCulturaenlinea` están sincronizados y limpios.
- **Archivos No Rastreados (Ignorar)**: Scripts de generación de datos de prueba (`create-test-data.*`, etc.).

## 5. Reglas de Desarrollo
1.  **NO modificar archivos en `vendor/` ni en el core de docker**. Las personalizaciones deben ir en Temas o Plugins.
2.  **Entorno Dev vs Prod**: 
    - `dev/docker-compose.yml` construye la imagen usando el contexto raíz `../`.
    - Producción usa `docker-compose.yml` en la raíz.
3.  **Gestión de Secretos**:
    - **ADVERTENCIA**: Revisar `init-letsencrypt.sh` ya que contiene dominios y emails *hardcoded*.
    - Usar siempre `.env` para credenciales.

## 6. Comandos Frecuentes
- **Iniciar Dev**: `cd dev && ./start.sh`
- **Actualizar Todo**: `./update.sh` (hace pull recursivo y rebuild).
- **Actualizar Tema**: `./theme-update.sh`.

---
*Copia y pega este contenido al inicio de una nueva conversación con tu Agente de IA para restaurar el contexto inmediato.*

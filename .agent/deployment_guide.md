# Guía de Deployment: MandatoryMFA Plugin

## 📋 Índice
1. [Arquitectura de Plugins](#arquitectura-de-plugins)
2. [Relación entre MultipleLocalAuth y MandatoryMFA](#relación-entre-plugins)
3. [Funcionamiento Interno](#funcionamiento-interno)
4. [Configuración del VPS](#configuración-del-vps)
5. [Proceso de Deployment](#proceso-de-deployment)
6. [Verificación y Troubleshooting](#verificación-y-troubleshooting)

---

## 1. Arquitectura de Plugins

### Estructura de Herencia

```
┌─────────────────────────────────────┐
│   MapasCulturais\AuthProvider       │  ← Clase base del framework
│   (Framework Core)                  │
└──────────────┬──────────────────────┘
               │ extiende
               ↓
┌─────────────────────────────────────┐
│   MultipleLocalAuth\Provider        │  ← Plugin base de autenticación
│   - Login nativo                    │
│   - Registro de usuarios            │
│   - MFA opcional                    │
│   - Recuperación de contraseña      │
└──────────────┬──────────────────────┘
               │ extiende
               ↓
┌─────────────────────────────────────┐
│   MandatoryMFA\Provider             │  ← Plugin que fuerza MFA
│   - Hereda toda la funcionalidad    │
│   - Sobreescribe doLogin()          │
│   - Bloquea toggle MFA              │
│   - Fuerza flujo MFA obligatorio    │
└─────────────────────────────────────┘
```

### Principio de Diseño

**Patrón de Herencia (Inheritance Pattern)**:
- `MandatoryMFA` **NO reemplaza** a `MultipleLocalAuth`
- `MandatoryMFA` **extiende y modifica** el comportamiento de `MultipleLocalAuth`
- Ambos plugins deben coexistir en el sistema

---

## 2. Relación entre Plugins

### MultipleLocalAuth (Plugin Padre)

**Ubicación**: `plugins/MultipleLocalAuth/`

**Responsabilidades**:
- Proporciona la clase base `Provider`
- Define constantes estáticas para metadata:
  ```php
  class Provider extends \MapasCulturais\AuthProvider {
      public static $mfaEnabledMetadata = 'mfa_enabled';
      public static $mfaCodeHashMetadata = 'mfa_code_hash';
      public static $passMetaName = 'senha';
      public static $loginAttempMetadata = 'loginAttemp';
      // ... más constantes
  }
  ```
- Implementa lógica de autenticación base
- Proporciona componentes Vue (login, create-account, etc.)
- Gestiona MFA **opcional** (usuario puede activar/desactivar)

**Estado en el sistema**: 
- ✅ Debe estar **presente** en `plugins/`
- ✅ Debe estar **registrado** en `config.d/plugins.php`
- ❌ **NO** es el provider activo cuando MandatoryMFA está habilitado

### MandatoryMFA (Plugin Hijo)

**Ubicación**: `plugins/MandatoryMFA/`

**Responsabilidades**:
- Extiende `MultipleLocalAuth\Provider`
- Sobreescribe métodos específicos para forzar MFA
- Copia completa de componentes Vue (aislamiento)
- Registra metadata adicional
- Gestiona MFA **obligatorio** (no se puede desactivar)

**Estado en el sistema**:
- ✅ Debe estar **presente** en `plugins/`
- ✅ Debe estar **registrado** en `config.d/plugins.php`
- ✅ **ES** el provider activo en `config.d/auth.php`

### Origen del Código (Instalación desde Source)

`MandatoryMFA` está diseñado para ser un repositorio independiente. 

1. **Requisito Previo**: Tener instalado `MultipleLocalAuth`.
2. **Repositorio**: (URL de tu nuevo repositorio remoto cuando lo crees).
3. **Procedimiento**:
    ```bash
    # Clonar el repo en la carpeta correcta
    git clone https://github.com/LibreCoopUruguay/plugin-MandatoryMFA.git plugins/MandatoryMFA
    ```

---

## 3. Funcionamiento Interno

### Flujo de Carga del Sistema

```
1. Mapas Culturais inicia
   ↓
2. Lee config.d/plugins.php
   ↓
3. Carga MultipleLocalAuth
   - Registra clase Provider
   - Define constantes estáticas
   - Registra componentes
   ↓
4. Carga MandatoryMFA
   - Extiende MultipleLocalAuth\Provider
   - Accede a constantes del padre
   - Registra sus propios componentes
   ↓
5. Lee config.d/auth.php
   ↓
6. Instancia el provider activo: \MandatoryMFA\Provider
   - Hereda toda la funcionalidad de MultipleLocalAuth
   - Aplica sus sobreescrituras
```

### Ejemplo de Herencia en Código

**En `MandatoryMFA/Plugin.php`:**
```php
<?php
namespace MandatoryMFA;

use MapasCulturais\i;
use MapasCulturais\App;

class Plugin extends \MapasCulturais\Plugin {
    public function register() {
        // Usa constantes del plugin padre (MultipleLocalAuth)
        $this->registerUserMetadata(
            \MultipleLocalAuth\Provider::$passMetaName,  // ← Accede a constante del padre
            ['label' => i::__('Contraseña')]
        );
        
        $this->registerUserMetadata(
            \MultipleLocalAuth\Provider::$mfaEnabledMetadata,  // ← Accede a constante del padre
            ['label' => i::__('MFA Habilitado')]
        );
    }
}
```

**En `MandatoryMFA/Provider.php`:**
```php
<?php
namespace MandatoryMFA;

class Provider extends \MultipleLocalAuth\Provider {  // ← Hereda del padre
    
    // Sobreescribe método del padre
    protected function doLogin($data) {
        // Llama al método del padre primero
        $result = parent::doLogin($data);
        
        // Modifica el comportamiento: fuerza MFA siempre
        if ($result['success']) {
            // Fuerza flujo MFA independientemente de la configuración del usuario
            return $this->initiateMFAFlow($user);
        }
        
        return $result;
    }
}
```

### Dependencias Críticas

**MandatoryMFA depende de MultipleLocalAuth para:**

1. **Constantes estáticas** (metadata keys):
   - `$mfaEnabledMetadata`
   - `$mfaCodeHashMetadata`
   - `$mfaCodeExpiresMetadata`
   - `$passMetaName`
   - `$loginAttempMetadata`
   - etc.

2. **Métodos heredados**:
   - `doLogin()`
   - `sendMFACode()`
   - `verifyMFACode()`
   - `registerUser()`
   - etc.

3. **Lógica de negocio**:
   - Validación de contraseñas
   - Gestión de intentos de login
   - Recuperación de contraseña
   - Envío de emails

**Si MultipleLocalAuth no está presente**: MandatoryMFA **fallará** con errores como:
- `Class 'MultipleLocalAuth\Provider' not found`
- `Undefined constant MultipleLocalAuth\Provider::$mfaEnabledMetadata`

---

## 4. Configuración del VPS

### Estructura de Directorios Requerida

```
/home/eamestoy/mapasculturais-v76-beta/
├── plugins/
│   ├── MultipleLocalAuth/          ← DEBE ESTAR PRESENTE
│   │   ├── Plugin.php
│   │   ├── Provider.php
│   │   ├── components/
│   │   └── views/
│   │
│   └── MandatoryMFA/               ← NUEVO PLUGIN
│       ├── .git/                   ← (Opcional) Si se instala desde source
│       ├── Plugin.php
│       ├── Provider.php
│       ├── components/
│       │   ├── login/
│       │   ├── mfa-verify/
│       │   ├── create-account/
│       │   └── ...
│       ├── views/
│       │   └── auth/
│       └── assets/
│
└── dev/
    └── config.d/
        ├── plugins.php             ← Registra TODOS los plugins active (MultipleLocalAuth, MandatoryMFA, etc)
        └── auth.php                ← Activa MandatoryMFA como provider
```

### Archivo: `dev/config.d/plugins.php`

```php
```php
<?php
/**
 * Configuración de Plugins
 * 
 * Se consolidan todos los plugins aquí para tener una única fuente de verdad.
 * MultipleLocalAuth es requerido por MandatoryMFA.
 */
return [
    'plugins' => [
        // Plugin base - OBLIGATORIO
        'MultipleLocalAuth' => [ 'namespace' => 'MultipleLocalAuth' ],
        
        // Plugin que extiende MultipleLocalAuth - ACTIVO
        'MandatoryMFA' => [ 'namespace' => 'MandatoryMFA' ],

        // Otros plugins del sistema
        'SamplePlugin' => ['namespace' => 'SamplePlugin'],
        'AdminLoginAsUser',
        'SpamDetector',
    ]
];
```

### Archivo: `dev/config.d/auth.php`

```php
<?php
/**
 * Configuración de Autenticación
 * 
 * El provider activo es MandatoryMFA, que hereda de MultipleLocalAuth
 * y fuerza el flujo de MFA obligatorio.
 */
return [
    'auth.provider' => '\MandatoryMFA\Provider',  // ← Provider activo
];
```

### Archivo: `dev/docker-compose.yml`

**Agregar volumen para MandatoryMFA:**

```yaml
services:
  mapas:
    volumes:
      # Plugins existentes
      - ../plugins/AdminLoginAsUser:/var/www/src/plugins/AdminLoginAsUser
      - ../plugins/MultipleLocalAuth:/var/www/src/plugins/MultipleLocalAuth  # ← DEBE ESTAR
      - ../plugins/MapasBlame:/var/www/src/plugins/MapasBlame
      - ../plugins/SpamDetector:/var/www/src/plugins/SpamDetector
      
      # Nuevo plugin
      - ../plugins/MandatoryMFA:/var/www/src/plugins/MandatoryMFA  # ← AGREGAR
      
      # Temas
      - ../themes/themeCulturaenlinea:/var/www/src/themes/themeCulturaenlinea
```

---

## 5. Proceso de Deployment

### Opción A: Deployment Manual (Recomendado para Testing)

#### Paso 1: Copiar Plugin desde Desarrollo

**Desde tu máquina local:**
```bash
cd /home/leo/mapas2025/repodic2025/mapasculturais-base-project

# Copiar plugin al VPS
scp -r plugins/MandatoryMFA root@vmi475679.contaboserver.net:/home/eamestoy/mapasculturais-v76-beta/plugins/
```

#### Paso 2: Verificar MultipleLocalAuth en VPS

**En el VPS:**
```bash
ssh root@vmi475679.contaboserver.net

cd /home/eamestoy/mapasculturais-v76-beta/plugins/MultipleLocalAuth

# Verificar versión
git log --oneline -1

# Verificar que tenga las constantes de MFA
grep -n "mfaEnabledMetadata" Provider.php
grep -n "mfaCodeHashMetadata" Provider.php
```

**Si NO tiene las constantes de MFA**, necesitas actualizar MultipleLocalAuth:

```bash
cd /home/eamestoy/mapasculturais-v76-beta/plugins/MultipleLocalAuth

# Cambiar remote a fork de LibreCoopUruguay
git remote set-url origin https://github.com/LibreCoopUruguay/plugin-MultipleLocalAuth.git

# Actualizar
git fetch origin
git checkout develop
git pull origin develop
```

#### Paso 3: Actualizar Configuración

**Crear/editar `dev/config.d/plugins.php`:**
```bash
nano /home/eamestoy/mapasculturais-v76-beta/dev/config.d/plugins.php
```

Contenido:
```php
```php
<?php
return [
    'plugins' => [
        'MultipleLocalAuth' => [ 'namespace' => 'MultipleLocalAuth' ],
        'MandatoryMFA' => ['namespace' => 'MandatoryMFA'],
        'SamplePlugin' => ['namespace' => 'SamplePlugin'],
        'AdminLoginAsUser',
        'SpamDetector',
    ]
];
```

**Editar `dev/config.d/auth.php`:**
```bash
nano /home/eamestoy/mapasculturais-v76-beta/dev/config.d/auth.php
```

Contenido:
```php
<?php
return [
    'auth.provider' => '\MandatoryMFA\Provider',
];
```

**Editar `dev/docker-compose.yml`:**
```bash
nano /home/eamestoy/mapasculturais-v76-beta/dev/docker-compose.yml
```

Agregar en la sección `volumes` del servicio `mapas`:
```yaml
- ../plugins/MandatoryMFA:/var/www/src/plugins/MandatoryMFA
```

#### Paso 4: Reiniciar Servicios

```bash
cd /home/eamestoy/mapasculturais-v76-beta/dev

# Reiniciar contenedor
docker compose restart mapas

# Limpiar caché
docker compose exec mapas rm -rf /var/www/files/cache/*

# Ver logs
docker compose logs -f mapas
```

### Opción B: Deployment desde Git (Recomendado para Producción)

```bash
# Backup del ambiente actual
cd /home/eamestoy
sudo mv mapasculturais-v76-beta mapasculturais-v76-beta.backup.$(date +%Y%m%d)

# Clonar repositorio actualizado
git clone --recursive -b develop-lt \
  https://github.com/LibreCoopUruguay/mapasculturais-base-project.git \
  mapasculturais-v76-beta

cd mapasculturais-v76-beta

# Restaurar configuraciones sensibles del backup
cp ../mapasculturais-v76-beta.backup.*/dev/.env dev/.env

# Verificar que los plugins estén correctos
ls -la plugins/MultipleLocalAuth/
ls -la plugins/MandatoryMFA/

# Levantar servicios
cd dev
docker compose up -d --build
```

---

## 6. Verificación y Troubleshooting

### Checklist de Verificación Post-Deployment

```bash
# 1. Verificar que ambos plugins existan
ls -la /home/eamestoy/mapasculturais-v76-beta/plugins/MultipleLocalAuth/
ls -la /home/eamestoy/mapasculturais-v76-beta/plugins/MandatoryMFA/

# 2. Verificar configuración de plugins
cat /home/eamestoy/mapasculturais-v76-beta/dev/config.d/plugins.php

# 3. Verificar configuración de auth
cat /home/eamestoy/mapasculturais-v76-beta/dev/config.d/auth.php

# 4. Verificar que el contenedor esté corriendo
cd /home/eamestoy/mapasculturais-v76-beta/dev
docker compose ps

# 5. Verificar logs por errores
docker compose logs mapas | grep -i "error\|fatal\|exception"

# 6. Verificar que los plugins se carguen
docker compose logs mapas | grep -i "MandatoryMFA\|MultipleLocalAuth"

# 7. Limpiar caché
docker compose exec mapas rm -rf /var/www/files/cache/*

# 8. Probar acceso web
curl -I http://localhost/
```

### Errores Comunes y Soluciones

#### Error 1: `Class 'MultipleLocalAuth\Provider' not found`

**Causa**: MultipleLocalAuth no está presente o no está registrado.

**Solución**:
```bash
# Verificar que existe
ls -la plugins/MultipleLocalAuth/Provider.php

# Verificar que esté registrado en plugins.php
grep -i "MultipleLocalAuth" dev/config.d/plugins.php

# Si falta, agregarlo
nano dev/config.d/plugins.php
```

#### Error 2: `Undefined constant MultipleLocalAuth\Provider::$mfaEnabledMetadata`

**Causa**: La versión de MultipleLocalAuth es muy antigua y no tiene las constantes de MFA.

**Solución**: Actualizar MultipleLocalAuth al fork de LibreCoopUruguay:
```bash
cd plugins/MultipleLocalAuth
git remote set-url origin https://github.com/LibreCoopUruguay/plugin-MultipleLocalAuth.git
git fetch origin
git checkout develop
git pull origin develop
```

#### Error 3: `Component mfa-verify not found`

**Causa**: El componente no se está cargando correctamente.

**Solución**: Verificar que MandatoryMFA tenga el componente:
```bash
ls -la plugins/MandatoryMFA/components/mfa-verify/
cat plugins/MandatoryMFA/components/mfa-verify/template.php
```

#### Error 4: Página en blanco o 500 Error

**Causa**: Error de PHP no capturado.

**Solución**: Ver logs detallados:
```bash
docker compose logs mapas | tail -100
docker compose exec mapas tail -f /var/www/src/protected/application/logs/error.log
```

### Verificación Funcional

**Pruebas a realizar:**

1. **Login Básico**:
   - Ir a `/autenticacao/`
   - Ingresar credenciales válidas
   - Verificar redirección a `/autenticacao/mfa`

2. **Página MFA**:
   - Verificar que muestre "Verificación de Seguridad"
   - Verificar input de código centrado
   - Verificar link "¿No recibiste el código? Reenviar"

3. **Verificación de Código**:
   - Revisar email con código de 6 dígitos
   - Ingresar código
   - Verificar acceso al panel

4. **Registro de Usuario**:
   - Ir a `/autenticacao/register`
   - Crear cuenta nueva
   - Verificar email de confirmación
   - Verificar que MFA se active automáticamente

---

## 7. Rollback en Caso de Problemas

### Rollback Rápido

Si algo falla, volver a MultipleLocalAuth:

```bash
# Editar auth.php
nano /home/eamestoy/mapasculturais-v76-beta/dev/config.d/auth.php
```

Cambiar a:
```php
<?php
return [
    'auth.provider' => '\MultipleLocalAuth\Provider',  // ← Volver al original
];
```

```bash
# Reiniciar
cd /home/eamestoy/mapasculturais-v76-beta/dev
docker compose restart mapas
docker compose exec mapas rm -rf /var/www/files/cache/*
```

### Rollback Completo

Si necesitas volver al backup completo:

```bash
cd /home/eamestoy
sudo rm -rf mapasculturais-v76-beta
sudo mv mapasculturais-v76-beta.backup.YYYYMMDD mapasculturais-v76-beta
cd mapasculturais-v76-beta/dev
docker compose up -d
```

---

## 8. Resumen Ejecutivo

### ¿Qué hace cada plugin?

| Plugin | Rol | Estado Requerido |
|--------|-----|------------------|
| **MultipleLocalAuth** | Plugin base de autenticación con MFA opcional | ✅ Presente y registrado |
| **MandatoryMFA** | Extiende MultipleLocalAuth para forzar MFA | ✅ Presente, registrado y activo como provider |

### Configuración Mínima Requerida

1. **Ambos plugins** en `plugins/`
2. **Ambos registrados** en `config.d/plugins.php`
3. **MandatoryMFA activo** en `config.d/auth.php`
4. **Volúmenes montados** en `docker-compose.yml`

### Comandos Esenciales

```bash
# Copiar plugin
scp -r plugins/MandatoryMFA root@VPS:/path/to/mapas/plugins/

# Reiniciar
docker compose restart mapas

# Limpiar caché
docker compose exec mapas rm -rf /var/www/files/cache/*

# Ver logs
docker compose logs -f mapas
```

---

**Documento creado**: 2025-12-08  
**Versión**: 1.0  
**Autor**: Asistente IA  
**Proyecto**: Mapas Culturais - Cultura en Línea Uruguay

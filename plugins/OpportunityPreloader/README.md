# OpportunityPreloader

Este plugin de Mapas Culturais permite precargar de forma automática la información del perfil del agente (usuario) en los campos del formulario de inscripción a una convocatoria.

## ¿Cómo funciona?

### 1. Backend (`Plugin.php`)
- Escucha el gancho (hook) `GET(<<registration>>.<<*>>):before` cuando se accede a un borrador de inscripción.
- Recupera los datos del perfil del agente autenticado (`$app->user->profile`).
- Inyecta estos datos en el cliente en la variable `$MAPAS.opportunityPreloaderAgentData`.
- Encola el script de cliente `js/opportunity-preloader.js`.

### 2. Frontend (`opportunity-preloader.js`)
- Espera a que AngularJS termine de cargar la vista y exponga el controlador `RegistrationFieldsController`.
- Busca dinámicamente los campos cargados de tipo `agent-owner-field`.
- Identifica a qué propiedad del agente corresponde cada campo (`entityField` como `name`, `emailPublico`, `telefonePublico`, etc.).
- Asigna el valor del perfil del agente directamente en la estructura reactiva de AngularJS (`scope.entity[field.fieldName]`).
- Dispara un ciclo `$apply()` de AngularJS para actualizar la vista y mostrar los valores en pantalla de forma inmediata.

## Configuración en Base de Datos

Para que el precargador funcione, la convocatoria (oportunidad) debe configurarse correctamente con los campos asociados al agente:

1. **Creación de Campos**: Se deben añadir campos de tipo **Agente Titular** (`agent-owner-field`) al formulario de inscripción.
2. **Propiedad Vinculada**: En la configuración del campo, se debe definir la clave `entityField` con el atributo del agente a mapear (ej: `name`, `nomeCompleto`, `documento`, `telefonePublico`, `emailPublico`).
3. **Paso de Inscripción (Steps)**: Si la convocatoria tiene activados los pasos de inscripción, **cada campo nuevo debe tener asociado un paso de inscripción válido** (ej: `Step ID`), de lo contrario la plataforma los ocultará por defecto.

## Desarrollo y Verificación

1. Activa el plugin en la configuración de Mapas Culturais (`plugins.php`):
   ```php
   'OpportunityPreloader' => ['namespace' => 'OpportunityPreloader'],
   ```
2. Asegúrate de reiniciar el contenedor de Docker para limpiar la caché de OPcache tras realizar modificaciones en los archivos PHP:
   ```bash
   docker compose -f dev/docker-compose.yml restart mapas
   ```

# OpportunityPreloader

Este plugin de Mapas Culturais permite precargar de forma automática la información del perfil del agente (usuario) en los campos del formulario de inscripción a una convocatoria.

---

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

---

## Guía para el Administrador: Configuración de Convocatorias con Precarga

Sigue estos pasos detallados desde cero para crear una nueva convocatoria (oportunidad) y configurar su formulario de inscripción para que los datos del postulante se completen de manera automática:

### Paso 1: Crear la Convocatoria (Oportunidad) desde cero
1. **Iniciar sesión:** Accede a la plataforma con tu usuario administrador.
2. **Crear oportunidad:** En la barra superior o en tu menú de usuario, haz clic en **Criar Oportunidade** (Crear Oportunidad).
3. **Completar datos principales:**
   * **Nombre:** Define el título de la convocatoria (ej: *“Fondo de Estímulo Cultural 2026”*).
   * **Descripción corta:** Un resumen visible en los listados.
   * **Descripción larga:** Las bases completas de la convocatoria.
   * **Fechas del periodo:** Define el rango exacto de inicio y fin durante el cual los usuarios podrán postularse.
4. **Habilitar inscripciones:** En el menú lateral derecho, asegúrate de activar la casilla **Utilizar inscrições** (Usar inscripciones). Sin esto, la oportunidad será puramente informativa y no tendrá formulario.
5. **Definir categorías (Opcional):** Si la convocatoria está dirigida a distintos tipos de postulantes (ej: *Categoría Individual* o *Categoría Colectiva*), agrégalas en el espacio correspondiente.
6. **Guardar:** Haz clic en **Salvar** (Guardar) para crear el borrador inicial de la oportunidad.

### Paso 2: Crear las Etapas (Pasos) de Inscripción
Mapas Culturais utiliza "Etapas" para dividir formularios extensos en secciones legibles (por ejemplo: *1. Datos de Identificación*, *2. Propuesta del Proyecto*, etc.). **Debes crear al menos una etapa para poder colocar campos en ella.**
1. En la página de la oportunidad (en modo edición), busca la sección **Etapas de inscrição** (Etapas de inscripción).
2. Haz clic en **Adicionar Etapa** (Añadir etapa).
3. Configura:
   * **Nombre de la etapa:** Escribe un título (ej: *“1. Datos Básicos del Postulante”*).
   * **Orden:** Ponle `1` si es la primera etapa.
4. Haz clic en **Salvar**.

### Paso 3: Configurar el Formulario y Vincular los Campos del Agente
En esta fase crearemos los campos específicos que le pedirán datos al postulante. Para que el sistema los precargue automáticamente, debemos utilizar un tipo de campo especial conectado a su perfil.
1. Ve a la sección **Formulário de Inscrição** (Formulario de inscripción) en la misma página de la oportunidad.
2. Haz clic en **Adicionar campo** (Añadir campo).
3. Configura las opciones del campo recién creado de la siguiente manera:
   * **Título:** Escribe el nombre que verá el usuario en pantalla (ej: *“Nombre del Postulante”*).
   * **Tipo de campo:** Selecciona **Agente Titular** en la lista desplegable. *(Esta opción indica que el campo está enlazado directamente al perfil del usuario responsable de la inscripción)*.
   * **Asociar a Etapa:** Selecciona la etapa que creaste en el Paso 2 (ej: *“1. Datos Básicos del Postulante”*). 
     > [!WARNING]
     > **¡Importante!** Si dejas la casilla de la etapa en blanco o sin seleccionar, el sistema no sabrá dónde colocar el campo y este **no se mostrará en pantalla**.
   * **Obligatorio:** Marca la casilla si deseas que el usuario no pueda enviar la postulación sin completar este dato.
4. **Mapeo de la Propiedad (entityField):** En la configuración específica del campo, debes elegir qué dato concreto del perfil del agente debe leerse. Utiliza las siguientes opciones estándares de la plataforma:
   * Selecciona `name` si quieres precargar el **Nombre de Fantasía o Nombre de Perfil**.
   * Selecciona `nomeCompleto` si quieres precargar el **Nombre Completo Legal o Razón Social**.
   * Selecciona `documento` si quieres precargar el **RUT / Documento de Identidad / Cédula / CPF**.
   * Selecciona `telefonePublico` si quieres precargar el **Teléfono de Contacto**.
   * Selecciona `emailPublico` si quieres precargar el **Correo Electrónico**.
5. Haz clic en **Salvar** dentro de la sección del formulario para confirmar los campos. Puedes añadir tantos campos de **Agente Titular** como necesites para capturar la información del perfil del usuario.

### Paso 4: Publicar la Convocatoria
1. En la parte superior de la página de edición de la oportunidad, cambia el estado de la oportunidad a **Publicada**.
2. Guarda los cambios. Una vez publicada y dentro de las fechas configuradas, la convocatoria estará lista para recibir postulantes.

---

## Desarrollo y Verificación (Entorno Local)

1. Activa el plugin en la configuración de Mapas Culturais (`plugins.php`):
   ```php
   'OpportunityPreloader' => ['namespace' => 'OpportunityPreloader'],
   ```
2. Asegúrate de reiniciar el contenedor de Docker para limpiar la caché de OPcache tras realizar modificaciones en los archivos PHP:
   ```bash
   docker compose -f dev/docker-compose.yml restart mapas
   ```
3. Inicia sesión con un **usuario común** (no administrador), dirígete a la convocatoria publicada y haz clic en **Inscrever-se** para comprobar la precarga en tiempo real.

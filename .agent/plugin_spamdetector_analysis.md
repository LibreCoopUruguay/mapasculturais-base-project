# Documentación: Plugin SpamDetector (Uruguay Edition)
> **Ubicación:** `plugins/SpamDetector/`  
> **Estado:** Modificado localmente (Forks internos)

Este documento extrae y consolida el conocimiento técnico sobre el plugin `SpamDetector` oficial, incluyendo las modificaciones estructurales realizadas para su despliegue óptimo en **Mapas Culturais - Uruguay Cultura en Línea**.

---

## 1. Naturaleza del Plugin (Original)
El plugin nativo monitorea de forma continua (hook `entity.save:before` y `after`) la creación y actualización de Agentes, Espacios, Proyectos, Eventos y Oportunidades. Contrasta sus campos de texto principales (y redes sociales) contra dos listas de palabras clave:

* **Lista de Notificación (Nivel 1):** Agrega el estado `spam_status = 1` y manda un correo a todos los administradores.
* **Lista de Bloqueo (Nivel 2):** Elimina el evento, pero destructivamente inhabilitaba (`lockEntityTree`) todos los otros registros del usuario creador y dejaba inactiva su cuenta.

### Problemas identificados en Infraestructura UY
1. **Destrucción de Perfiles (Nivel 2):** Ante un falso positivo o sabotaje menor, todo el perfil y la red de agentes del usuario se borraban lógicamente. Severidad excesiva.
2. **Avalanchas de Correos SMTP:** Si un script malicioso creaba 1,000 entidades, el plugin enviaría 1,000 correos a *todos* los funcionarios con rol Admin, colapsando o provocando el baneo del servicio de envío (Mailhog/Mandrill).
3. **Mala detección Lexical:** El motor de Regex partía palabras a la mitad y generaba falsos positivos si no había espacios claros.
4. **Acumulación en Servidor:** Todo lo bloqueado se apilaba indefinidamente con `status = -10` en PostgreSQL.

---

## 2. Refactorización y Mejoras "Enterprise" (LibreCoop Uruguay)

Se intervinieron funciones clave en `Plugin.php` para resolver los problemas críticos de seguridad operativa:

### Fase 1: Suavizado del "Castigo" en cascada
Se eliminó la llamada destructiva `$plugin->lockEntityTree($this->ownerUser)` y la inhabilitación del operador `$this->ownerUser->setStatus(-10)`. El plugin ahora aísla la entidad tóxica, mandándola a la papelera (`status = -10`), pero permite que el ciudadano siga operando con sus demás creaciones impunemente.

### Fase 2: Rate Limit de Alertas (Cooldown Redis)
Se integró una llamada a la conexión Redis principal (`\MapasCulturais\App::i()->cache`). 
Al detectar un spam, antes de entrar en el loop que reenvía correos a todos los admins, se verifica si pasaron más de **15 minutos** desde el último ataque. Si no, la entidad se penaliza silenciosamente.

### Fase 3: Mejoras en detección Regex (\b)
Se cambió el motor de partición por expresiones de Word Boundaries y Lookarounds negativos con soporte UTF-8 (`/iu`): `/(?<![\p{L}\p{N}_])/`. Esto previene que buscar "venda" bloquee inocentes referenciando al verbo "revenda", pero garantizando detección de minúsculas/mayúsculas.

### Fase 4: Opciones Avanzadas Administrativas
* **Inmunidad Admin:** El hook `save:before` ahora evalúa `App::i()->user->is('admin')`. Los funcionarios pueden escribir términos prohibidos sin temor a que el sistema censure reportes legales oficiales dentro de la plataforma.
* **Soporte Regex en Panel UI:** Si desde el panel de control se agrega una palabra bordeada por barras `/`, el plugin no la sanitizará. Esto permite atrapar URLs o teléfonos con Regex avanzado (ej. `/http.*\.ru/` para enlaces rusos).
* **Nuevo Panel "Spam Log":** Se creó una nueva consulta `UNION ALL` y un controlador `SpamDetector\Controllers\Admin` que renderiza una lista de todas las entidades bajo sospecha pendientes en la pestaña `/spamdetector/log`.
* **Auto-limpieza (Cron/CLI):** Se desarrolló la clase `PurgeSpamCommand`. Registrada en el comando de consola de php como `spam:purge-old`. Si se corre periódicamente por un Crontab, ejecutará un `em->remove()` (DELETE físico) a todo elemento en la papelera de spam que tenga más de 30 días de antigüedad.

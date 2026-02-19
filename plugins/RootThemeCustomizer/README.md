# RootThemeCustomizer Plugin

Plugin para Mapas Culturais que permite personalizar la página de inicio sin modificar archivos del core ("Zero Overrides").

## Funcionalidades

| Funcionalidad | Descripción |
|---|---|
| **Textos** | Título y subtítulo del hero |
| **Imagen hero** | URL de imagen de fondo del banner principal |
| **Reordenamiento** | Control numérico del orden visual de las secciones |
| **Visibilidad** | Toggle para mostrar/ocultar secciones |
| **Imágenes de fondo** | URL de imagen de fondo por sección |

## Secciones disponibles

- **Destacado** (`featured`) — `.home-feature`
- **Eventos** (`events`) — Card dentro de `.home-entities`
- **Agentes** (`agents`) — Card dentro de `.home-entities`
- **Espacios** (`spaces`) — Card dentro de `.home-entities`
- **Proyectos** (`projects`) — Card dentro de `.home-entities`
- **Oportunidades** (`opportunities`) — Card dentro de `.home-entities`
- **Desarrolladores** (`developers`) — `.home-developers`
- **Regístrate** (`register`) — `.home-register`

## Arquitectura

```
RootThemeCustomizer/
├── Plugin.php                  # Lógica principal, hooks e inyección JS
├── Controllers/Admin.php       # Controlador CRUD del panel admin
├── views/root-theme-customizer/
│   └── index.php               # Vista del panel admin con CSS inline
└── README.md                   # Esta documentación
```

### Flujo de datos

```
home.json (persistente)
    │
    ├── PHP: applyHomeOverrides() ──→ $app->config (textos del hero)
    │
    └── PHP: injectFrontendCustomization() ──→ <script> inyectado en <head>
                                                    │
                                                    └── JS: setInterval polling
                                                            → espera Vue mount
                                                            → aplica CSS order
                                                            → aplica display:none
                                                            → aplica background-image
```

### Almacenamiento

El archivo de configuración se guarda en:

```
/var/www/html/files/config/home.json
```

Mapeado al volumen Docker:

```
./docker-data/public-files/config/home.json
```

Esto garantiza **persistencia entre rebuilds** del contenedor.

### Formato de `home.json`

```json
{
    "title": "Cultura en Línea",
    "subtitle": "El mapa cultural de Uruguay",
    "hero_image": "",
    "sections": {
        "featured": { "visible": true, "order": 1, "image": "" },
        "events":   { "visible": true, "order": 2, "image": "" },
        "agents":   { "visible": true, "order": 2, "image": "" },
        "spaces":   { "visible": true, "order": 3, "image": "" },
        "projects": { "visible": true, "order": 4, "image": "" },
        "opportunities": { "visible": true, "order": 5, "image": "" },
        "developers": { "visible": false, "order": 6, "image": "" },
        "register": { "visible": true, "order": 8, "image": "" }
    }
}
```

## Hooks utilizados

| Hook | Propósito |
|---|---|
| `app.register:after` | Cargar `home.json` y sobreescribir textos del hero |
| `template(site.index.head):end` | Inyectar `<script>` con la lógica de personalización |
| `panel.nav` | Agregar enlace "Personalizar Home" al menú de admin |
| `view.title(root-theme-customizer.index)` | Establecer título de la página admin |
| `GET(root-theme-customizer.index)` | Encolar estilos del panel |

## Mecanismo de inyección frontend

El script se inyecta en el `<head>` de la home pero las secciones son **componentes Vue** que se montan asincrónicamente. Para resolver este problema de timing:

1. Se usa `setInterval` con polling cada **200ms** (máximo 50 intentos = 10s)
2. En cada iteración, se busca `.home-entities` como indicador de que Vue montó los componentes
3. Una vez detectado, se aplican los estilos:
   - `display: flex; flex-direction: column;` en `#main-app` para habilitar CSS `order`
   - `order: N` en cada sección según configuración
   - `display: none` para secciones ocultas
   - `background-image` para imágenes personalizadas

### Cards de entidades

Las cards internas (Eventos, Agentes, etc.) se identifican buscando su texto label dentro de `.home-entities__content--cards` y navegando al contenedor padre con `.closest()`.

## Panel de administración

Accesible en `/root-theme-customizer/` para usuarios admin.

**Características del panel:**
- Tabla con todas las secciones, su orden, imagen y toggle de visibilidad
- Campos para título, subtítulo e imagen hero
- Modal de ayuda con instrucciones
- POST-Redirect-Get (302) para evitar reenvío de formulario

## Compatibilidad

- **Mapas Culturais** v7.x (BaseV2 theme con Vue.js)
- **PHP** 7.4+
- **Docker** compatible (volúmenes persistentes)

## Consideraciones para migración

- Si la estructura DOM cambia en una nueva versión (ej: `.home-entities` se renombra), actualizar los selectores en `Plugin.php` → `injectFrontendCustomization()`
- Si `home.json` no existe, el plugin usa defaults sin errores
- No modifica archivos core: toda la lógica vive en hooks

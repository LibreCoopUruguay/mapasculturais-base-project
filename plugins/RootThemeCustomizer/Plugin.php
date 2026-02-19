<?php
namespace RootThemeCustomizer;

use MapasCulturais\App;
use MapasCulturais\i;

class Plugin extends \MapasCulturais\Plugin {
    
    public function _init() {
        $app = App::i();
        
        // Hook to load config and override texts
        $self = $this;
        $app->hook('app.register:after', function() use($app, $self) {
            $self->applyHomeOverrides();
        });

        // Hook to inject CSS/JS for sections
        // Inyectamos al final del <head> de la vista site.index (home)
        $app->hook('template(site.index.head):end', function() use($app, $self) {
             $self->injectFrontendCustomization();
        });

        // Register Admin Controller route
         $app->hook('GET(root-theme-customizer.index)', function () use ($app) {
            $app->view->enqueueStyle('app', 'root-theme-customizer', 'css/root-theme-customizer.css');
        });

        // Add link to panel nav (BaseV2 usa hook 'panel.nav' con array por referencia)
        $app->hook('panel.nav', function (&$nav_items) use ($app) {
            if ($app->user->is('admin')) {
                $nav_items['admin']['items'][] = [
                    'route'  => 'root-theme-customizer/index',
                    'icon'   => 'config',
                    'label'  => i::__('Personalizar Home'),
                ];
            }
        });

        // Registrar título para el controller custom (evita crash de getReadableName en Theme.php)
        $app->hook('view.title(root-theme-customizer.index)', function (&$title) {
            $title = i::__('Personalizar Home') . ' - ' . App::i()->siteName;
        });
    }

    public function register() {
        $app = App::i();
        // Register Admin Controller
        $app->registerController('root-theme-customizer', 'RootThemeCustomizer\Controllers\Admin');

        // (El item de menú se agrega via hook panel.nav en _init())
    }

    private function applyHomeOverrides() {
        $app = App::i();
        $configFile = BASE_PATH . 'files/config/home.json';

        if (!file_exists($configFile)) {
            return;
        }

        $json = file_get_contents($configFile);
        $config = json_decode($json, true);

        if (!$config || !is_array($config)) {
            return;
        }

        // Override Home Title and Subtitle
        // The keys are determined by Theme::text() lookup logic
        if (!empty($config['title'])) {
            $app->_config['text:home: title'] = $config['title']; // Generic fallback
            $app->_config['text:part(home-search).home: title'] = $config['title']; // Specific part
        }

        if (!empty($config['subtitle'])) {
             $app->_config['text:home: welcome'] = $config['subtitle'];
             $app->_config['text:part(home-search).home: welcome'] = $config['subtitle'];
        }
        
        // Save config in app for other uses if needed
        $app->config['root_theme_customizer'] = $config;
    }

    private function injectFrontendCustomization() {
        $app = App::i();
        // Obtener configuración (cargada por applyHomeOverrides en app.register:after)
        $config = $app->config['root_theme_customizer'] ?? [];
        
        // Mapeo de secciones a sus selectores reales en el DOM
        // Secciones TOP-LEVEL (hijos directos de #main-app):
        //   .home-entities, .home-feature, .home-register, .home-developers
        // Cards de entidades (dentro de .home-entities__content--cards):
        //   Son divs con clase entity-card y un label con el nombre
        
        $topSections = [
            'entities'   => ['selector' => '.home-entities',   'default_order' => 3],
            'featured'   => ['selector' => '.home-feature',    'default_order' => 4],
            'register'   => ['selector' => '.home-register',   'default_order' => 5],
            'developers' => ['selector' => '.home-developers', 'default_order' => 6],
        ];
        
        // Cards de entidades (buscar por label text)
        $entityCards = ['events', 'agents', 'spaces', 'projects', 'opportunities'];
        
        // Labels en español para buscar cards
        $entityLabels = [
            'events' => 'Eventos',
            'agents' => 'Agentes',
            'spaces' => 'Espacios',
            'projects' => 'Proyectos',
            'opportunities' => 'Oportunidades',
        ];
        
        // Construir config JS
        $jsTopSections = [];
        foreach ($topSections as $key => $def) {
            $conf = $config['sections'][$key] ?? [];
            if (is_bool($conf)) $conf = ['visible' => $conf];
            $jsTopSections[$key] = [
                'selector' => $def['selector'],
                'visible'  => $conf['visible'] ?? true,
                'order'    => intval($conf['order'] ?? $def['default_order']),
                'image'    => $conf['image'] ?? '',
            ];
        }
        
        $jsEntityCards = [];
        foreach ($entityCards as $key) {
            $conf = $config['sections'][$key] ?? [];
            if (is_bool($conf)) $conf = ['visible' => $conf];
            $jsEntityCards[$key] = [
                'label'    => $entityLabels[$key],
                'visible'  => $conf['visible'] ?? true,
                'order'    => intval($conf['order'] ?? 99),
                'image'    => $conf['image'] ?? '',
            ];
        }
        
        $jsonTop = json_encode($jsTopSections);
        $jsonCards = json_encode($jsEntityCards);
        
        error_log("RTC Debug TopSections: " . $jsonTop);
        error_log("RTC Debug EntityCards: " . $jsonCards);

        $script = <<<HTML
<!-- RTC INJECTED -->
<script>
(function() {
    var topSections = $jsonTop;
    var entityCards = $jsonCards;
    
    console.log('RTC: Config loaded', {topSections: topSections, entityCards: entityCards});

    // Polling: esperar a que Vue monte los componentes
    var attempts = 0;
    var maxAttempts = 50; // 50 * 200ms = 10 segundos max
    var pollId = setInterval(function() {
        attempts++;
        var target = document.querySelector('.home-entities');
        if (!target && attempts < maxAttempts) {
            return; // Seguir esperando
        }
        clearInterval(pollId);
        
        if (!target) {
            console.warn('RTC: Timeout - .home-entities never appeared');
            return;
        }
        
        console.log('RTC: Vue components detected after', attempts, 'polls. Applying customizations.');
        
        var mainApp = document.getElementById('main-app');
        if (!mainApp) {
            console.warn('RTC: #main-app not found, aborting');
            return;
        }
        
        // 1. Reordenar secciones TOP-LEVEL usando flexbox
        mainApp.style.display = 'flex';
        mainApp.style.flexDirection = 'column';
        
        // Aplicar order, visibilidad e imagen a secciones top-level
        for (var key in topSections) {
            var cfg = topSections[key];
            var el = document.querySelector(cfg.selector);
            if (!el) {
                console.warn('RTC: Top section not found:', key, cfg.selector);
                continue;
            }
            
            el.style.order = cfg.order;
            console.log('RTC: Set order', cfg.order, 'for', key);
            
            if (cfg.visible === false || cfg.visible === 'false' || cfg.visible === 0) {
                el.style.display = 'none';
                console.log('RTC: Hidden', key);
            }
            
            if (cfg.image && cfg.image.trim() !== '') {
                el.style.setProperty('background-image', 'url("' + cfg.image + '")', 'important');
                el.style.backgroundSize = 'cover';
                el.style.backgroundPosition = 'center';
                el.classList.add('rtc-custom-bg');
                console.log('RTC: Applied bg image to', key);
            }
        }
        
        // Fijar orden de header/footer
        var header = mainApp.querySelector('.main-header');
        if (header) header.style.order = 0;
        var homeHeader = mainApp.querySelector('.home-header');
        if (homeHeader) homeHeader.style.order = 1;
        var messages = mainApp.querySelector('.messages');
        if (messages) messages.style.order = 2;
        var footer = mainApp.querySelector('.main-footer');
        if (footer) footer.style.order = 99;
        
        // 2. Cards de entidades
        var cardsContainer = document.querySelector('.home-entities__content--cards');
        if (cardsContainer) {
            cardsContainer.style.display = 'flex';
            cardsContainer.style.flexDirection = 'column';
            
            for (var cardKey in entityCards) {
                var cardCfg = entityCards[cardKey];
                var cardEl = null;
                var labels = cardsContainer.querySelectorAll('label.title, .entity-card label, .card label');
                for (var i = 0; i < labels.length; i++) {
                    if (labels[i].textContent.trim() === cardCfg.label) {
                        cardEl = labels[i].closest('.entity-card') || labels[i].closest('.card') || labels[i].parentElement.parentElement;
                        break;
                    }
                }
                
                if (!cardEl) {
                    console.warn('RTC: Entity card not found:', cardKey, cardCfg.label);
                    continue;
                }
                
                cardEl.style.order = cardCfg.order;
                console.log('RTC: Set card order', cardCfg.order, 'for', cardKey);
                
                if (cardCfg.visible === false || cardCfg.visible === 'false' || cardCfg.visible === 0) {
                    cardEl.style.display = 'none';
                    console.log('RTC: Hidden card', cardKey);
                }
                
                if (cardCfg.image && cardCfg.image.trim() !== '') {
                    cardEl.style.setProperty('background-image', 'url("' + cardCfg.image + '")', 'important');
                    cardEl.style.backgroundSize = 'cover';
                    cardEl.style.backgroundPosition = 'center';
                    cardEl.classList.add('rtc-custom-bg');
                    console.log('RTC: Applied bg image to card', cardKey);
                }
            }
        } else {
            console.warn('RTC: .home-entities__content--cards not found');
        }
        
        console.log('RTC: Customization complete');
    }, 200);
})();
</script>
<style>
.rtc-custom-bg {
    background-size: cover !important;
    background-position: center !important;
}
</style>
HTML;
        echo $script;

        // Inyectar CSS global para Hero de forma independiente (PHP) para evitar FOUC
        if (!empty($config['hero_image'])) {
            echo "<style>#home-intro { background-image: url('{$config['hero_image']}') !important; }</style>";
        }
    }
}

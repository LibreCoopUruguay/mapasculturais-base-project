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

        // Hook to inject CSS for sections
        $app->hook('view.render(site/index):before', function() use($app, $self) {
            $self->applySectionOverrides();
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

    private function applySectionOverrides() {
        $app = App::i();
        $config = $app->config['root_theme_customizer'] ?? null;
        
        if (!$config) return;

        $css = '<style>';
        
        // Sections
        $sections = ['events', 'agents', 'spaces', 'projects', 'opportunities', 'developers'];
        
        foreach ($sections as $section) {
            if (isset($config['sections'][$section]) && ($config['sections'][$section] === false || $config['sections'][$section] === 0 || $config['sections'][$section] === '0')) {
                // Hide the section
                // The ID is usually #home-{section}
                $css .= "#home-{$section} { display: none !important; } ";
                // Also hide the filter in search box if possible? 
                // The filter uses IDs like #events-filter
                $css .= "#{$section}-filter { display: none !important; } ";
            }
        }

        if (!empty($config['hero_image'])) {
             // Override hero image background
             // #home-intro { background-image: url(...) }
             // We need to ensure the path is correct. 
             // If local file, it might be in files/
             $css .= "#home-intro { background-image: url('{$config['hero_image']}') !important; } ";
        }

        $css .= '</style>';

        echo $css;
    }
}

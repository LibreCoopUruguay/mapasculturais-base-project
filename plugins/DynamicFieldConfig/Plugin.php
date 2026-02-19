<?php
namespace DynamicFieldConfig;

use MapasCulturais\App;
use MapasCulturais\i;

class Plugin extends \MapasCulturais\Plugin {
    
    public function _init() {
        // DEBUG: Verificar si el plugin carga
        // die('DYNAMIC FIELD CONFIG CARGADO'); 
        error_log("DynamicFieldConfig: _init() ejecutado correctamente.");

        $app = App::i();
        
        // Hook after registration to modify metadata (for labels and required)
        $self = $this;
        $app->hook('app.register:after', function() use($app, $self) {
            $self->applyFieldOverrides();
        });

        // Hook into metadata params to hide fields
        // This is done per entity type
        $entities = ['Agent', 'Space', 'Project', 'Event', 'Opportunity'];
        foreach ($entities as $entity) {
            $app->hook("entity($entity).metadata.params", function(&$params) use($entity) {
                $this->filterMetadataParams($params, "MapasCulturais\\Entities\\$entity");
            });
        }

        // Register Admin Controller route assets
        $app->hook('GET(dynamic-field-config.index)', function () use ($app) {
            $app->view->enqueueStyle('app', 'dynamic-field-config', 'css/dynamic-field-config.css');
            // $app->view->enqueueScript('app', 'dynamic-field-config', 'js/dynamic-field-config.js');
        });

        // Add link to panel nav (BaseV2 usa hook 'panel.nav' con array por referencia)
        $app->hook('panel.nav', function (&$nav_items) use ($app) {
            if ($app->user->is('admin')) {
                $nav_items['admin']['items'][] = [
                    'route'  => 'dynamic-field-config/index',
                    'icon'   => 'config',
                    'label'  => i::__('Configurar Campos'),
                ];
            }
        });

        // Registrar título para el controller custom (evita crash de getReadableName en Theme.php)
        $app->hook('view.title(dynamic-field-config.index)', function (&$title) {
            $title = i::__('Configurar Campos') . ' - ' . App::i()->siteName;
        });
    }

    public function register() {
        $app = App::i();
        // Register Admin Controller
        $app->registerController('dynamic-field-config', 'DynamicFieldConfig\Controllers\Admin');

        // (El item de menú se agrega via hook panel.nav en _init())
    }

    private function applyFieldOverrides() {
        $app = App::i();
        $configFile = BASE_PATH . 'files/config/field-overrides.json';

        if (!file_exists($configFile)) {
            return;
        }

        $json = file_get_contents($configFile);
        $overrides = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($overrides)) {
            return;
        }
        
        foreach ($overrides as $entityClass => $fields) {
            if (!is_array($fields)) continue;
            
            foreach ($fields as $key => $config) {
                 $def = $app->getRegisteredMetadataByMetakey($key, $entityClass);
                 
                 if ($def) {
                     if (!empty($config['label'])) {
                         $def->label = $config['label'];
                     }
                     
                     if (isset($config['required'])) {
                         if ($config['required'] == 1 || $config['required'] === true) {
                             $def->validations['required'] = i::__('Este campo es obligatorio.');
                         } else {
                             unset($def->validations['required']);
                         }
                     }
                 }
            }
        }
    }

    /**
     * Filters metadata parameters to hide fields marked as disabled
     */
    private function filterMetadataParams(&$params, $entityClass) {
        $configFile = BASE_PATH . 'files/config/field-overrides.json';
        if (!file_exists($configFile)) {
            return;
        }

        $json = file_get_contents($configFile);
        $overrides = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($overrides) || !isset($overrides[$entityClass])) {
            return;
        }

        $entityOverrides = $overrides[$entityClass];

        foreach ($params as $key => $def) {
            if (isset($entityOverrides[$key]['enabled']) && ($entityOverrides[$key]['enabled'] == 0 || $entityOverrides[$key]['enabled'] === false)) {
                unset($params[$key]);
            }
        }
    }
}

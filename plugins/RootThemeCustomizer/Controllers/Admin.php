<?php
namespace RootThemeCustomizer\Controllers;

use MapasCulturais\App;
use MapasCulturais\Controller;

class Admin extends Controller {

    public function __construct() {
        $this->layout = 'panel';
    }
    
    public function GET_index() {
        $app = App::i();
        $this->requireAuthentication();
        if (!$app->user->is('admin')) { $app->halt(403); }
        
        // Load config
        $configFile = BASE_PATH . 'files/config/home.json';
        $savedConfig = [];
        if (file_exists($configFile)) {
            $json = file_get_contents($configFile);
            $savedConfig = json_decode($json, true) ?: [];
        }

        // Definición de todas las secciones soportadas con sus valores por defecto
        $sectionsDef = [
            'events' => ['label' => 'Eventos', 'selector' => '#home-events', 'default' => true, 'image_hint' => '800×400px (card)'],
            'agents' => ['label' => 'Agentes', 'selector' => '#home-agents', 'default' => true, 'image_hint' => '800×400px (card)'],
            'spaces' => ['label' => 'Espacios', 'selector' => '#home-spaces', 'default' => true, 'image_hint' => '800×400px (card)'],
            'projects' => ['label' => 'Proyectos', 'selector' => '#home-projects', 'default' => true, 'image_hint' => '800×400px (card)'],
            'opportunities' => ['label' => 'Oportunidades', 'selector' => '#home-opportunities', 'default' => true, 'image_hint' => '800×400px (card)'],
            'developers' => ['label' => 'Desarrolladores', 'selector' => '.home-developers', 'default' => true, 'image_hint' => '1920×400px (fondo)'],
            'featured' => ['label' => 'Destacado', 'selector' => '.home-feature', 'default' => true, 'image_hint' => '1920×400px (fondo)'],
            'register' => ['label' => 'Regístrate y Colabora', 'selector' => '.home-register', 'default' => true, 'image_hint' => '1920×400px (fondo)'],
        ];

        // Construir secciones por defecto con estructura avanzada
        $defaultSections = [];
        $i = 1;
        foreach ($sectionsDef as $key => $def) {
            $defaultSections[$key] = [
                'visible' => $def['default'],
                'order' => $i++,
                'image' => '',
                'label' => $def['label'],
                'selector' => $def['selector'],
                'image_hint' => $def['image_hint'] ?? '',
            ];
        }

        // Merge inteligente para migrar configuración antigua
        $sections = $defaultSections;
        if (isset($savedConfig['sections']) && is_array($savedConfig['sections'])) {
            foreach ($savedConfig['sections'] as $key => $val) {
                if (!isset($sections[$key])) continue;

                if (is_bool($val)) {
                    // Migración legacy: "key": true/false
                    $sections[$key]['visible'] = $val;
                } elseif (is_array($val)) {
                    // Estructura nueva: merge
                    $sections[$key] = array_merge($sections[$key], $val);
                }
            }
        }

        // Ordenar para la presenación en el admin
        uasort($sections, function($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        // Configuración final para la vista
        $config = [
            'title' => $savedConfig['title'] ?? 'Cultura en Línea',
            'subtitle' => $savedConfig['subtitle'] ?? 'El mapa cultural de Uruguay',
            'hero_image' => $savedConfig['hero_image'] ?? '',
            'sections' => $sections
        ];
        
        $this->render('index', ['config' => $config]);
    }
    
    public function POST_save() {
        $app = App::i();
        $this->requireAuthentication();
        if (!$app->user->is('admin')) { $app->halt(403); }

        // Mapear datos desde $_POST['config'] ya que el form usa name="config[xx]"
        $postData = $_POST['config'] ?? [];

        $config = [
            'title' => filter_var($postData['title'] ?? '', FILTER_SANITIZE_STRING),
            'subtitle' => filter_var($postData['subtitle'] ?? '', FILTER_SANITIZE_STRING),
            'hero_image' => filter_var($postData['hero_image'] ?? '', FILTER_SANITIZE_URL),
            'sections' => []
        ];

        // Procesar las secciones enviadas
        if (isset($postData['sections']) && is_array($postData['sections'])) {
            foreach ($postData['sections'] as $key => $data) {
                // Validación básica de claves permitidas
                $allowedKeys = ['events', 'agents', 'spaces', 'projects', 'opportunities', 'developers', 'featured', 'register'];
                if(!in_array($key, $allowedKeys)) continue;

                $config['sections'][$key] = [
                    'visible' => isset($data['visible']) && ($data['visible'] == '1' || $data['visible'] == 'on'),
                    'order' => intval($data['order'] ?? 0),
                    'image' => filter_var($data['image'] ?? '', FILTER_SANITIZE_URL)
                ];
            }
        }
        
        // Guardar JSON
        $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $configFile = BASE_PATH . 'files/config/home.json';
        file_put_contents($configFile, $json);
        
        $app->redirect($app->createUrl('root-theme-customizer', 'index'), 302);
    }
}

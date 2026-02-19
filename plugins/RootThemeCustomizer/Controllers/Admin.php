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
        $config = [];
        if (file_exists($configFile)) {
            $json = file_get_contents($configFile);
            $config = json_decode($json, true) ?: [];
        }

        // Defaults
        $defaults = [
            'title' => '',
            'subtitle' => '',
            'hero_image' => '',
            'sections' => [
                'events' => true,
                'agents' => true,
                'spaces' => true,
                'projects' => true,
                'opportunities' => true,
                'developers' => true
            ]
        ];

        $data = array_replace_recursive($defaults, $config);
        
        $this->render('index', ['config' => $data]);
    }
    
    public function POST_save() {
        $app = App::i();
        $config = $this->data['config'] ?? [];
        
        // Basic sanitization
        
        // Sanitize sections to boolean
        if (isset($config['sections']) && is_array($config['sections'])) {
            foreach ($config['sections'] as $key => $val) {
                // If it was sent, it's '1' (true). If it wasn't sent, it's not here (handled by merge with defaults usually, but let's be safe)
                // Actually, unchecked checkboxes are NOT sent in POST. 
                // We need to handle that.
                $config['sections'][$key] = (bool) $val;
            }
        }

        // We need to ensure we have all sections, setting missing ones to false
        $allSections = ['events', 'agents', 'spaces', 'projects', 'opportunities', 'developers'];
        foreach ($allSections as $section) {
            if (!isset($config['sections'][$section])) {
                $config['sections'][$section] = false;
            }
        }

        
        $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        $configFile = BASE_PATH . 'files/config/home.json';
        file_put_contents($configFile, $json);
        
        $app->redirect($app->createUrl('root-theme-customizer', 'index'), 200);
    }
}

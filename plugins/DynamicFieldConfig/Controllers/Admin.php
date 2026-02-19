<?php
namespace DynamicFieldConfig\Controllers;

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
        
        // List of entities to configure
        $entities = [
            'MapasCulturais\Entities\Agent' => 'Agentes',
            'MapasCulturais\Entities\Space' => 'Espacios',
            'MapasCulturais\Entities\Project' => 'Proyectos',
            'MapasCulturais\Entities\Event' => 'Eventos',
            'MapasCulturais\Entities\Opportunity' => 'Oportunidades'
        ];
        
        $fieldsConfig = [];
        
        // Load current config
        $configFile = BASE_PATH . 'files/config/field-overrides.json';
        $currentConfig = [];
        if (file_exists($configFile)) {
            $json = file_get_contents($configFile);
            $currentConfig = json_decode($json, true) ?: [];
        }
        
        // Criterios de agrupación de campos por categoría
        $groupKeywords = [
            'Identificación'  => ['nomeCompleto','nomeSocial','documento','cpf','cnpj','rg','rne','passaporte','identidade','nome'],
            'Datos Personales' => ['dataNascimento','genero','raca','escolaridade','renda','pessoaDeficiente','comunidades'],
            'Contacto'        => ['email','telefone','fax','site','facebook','twitter','instagram','youtube','linkedin','vimeo','spotify'],
            'Dirección'       => ['En_','address','endereco','CEP','cep','municipio','estado','pais'],
        ];

        foreach ($entities as $class => $label) {
            $metadata = $app->getRegisteredMetadata($class);
            $fields = [];
            
            if (is_array($metadata)) {
                foreach ($metadata as $key => $def) {
                    // Determine group
                    $group = 'Otros';
                    foreach ($groupKeywords as $groupName => $keywords) {
                        foreach ($keywords as $kw) {
                            if (stripos($key, $kw) !== false) {
                                $group = $groupName;
                                break 2;
                            }
                        }
                    }
                    $fields[$key] = [
                        'label'          => $def->label,
                        'original_label' => $def->label,
                        'required'       => isset($def->validations['required']),
                        'type'           => $def->type,
                        'group'          => $group,
                    ];
                }
            }

            // Sort fields by group for stable display
            uasort($fields, function($a, $b) {
                $order = ['Identificación'=>0,'Datos Personales'=>1,'Contacto'=>2,'Dirección'=>3,'Otros'=>4];
                return ($order[$a['group']] ?? 99) <=> ($order[$b['group']] ?? 99);
            });
            
            $fieldsConfig[$class] = [
                'label'     => $label,
                'fields'    => $fields,
                'overrides' => $currentConfig[$class] ?? []
            ];
        }
        
        $this->render('index', ['entities' => $fieldsConfig]);
    }
    
    public function POST_save() {
        $app = App::i();
        $config = $this->data['config'] ?? [];
        
        // Validation Layer
        $validEntities = [
            'MapasCulturais\Entities\Agent',
            'MapasCulturais\Entities\Space',
            'MapasCulturais\Entities\Project',
            'MapasCulturais\Entities\Event',
            'MapasCulturais\Entities\Opportunity'
        ];

        $sanitizedConfig = [];

        foreach ($config as $entityClass => $fields) {
            if (!in_array($entityClass, $validEntities)) {
                continue;
            }

            if (!is_array($fields)) {
                continue;
            }

            foreach ($fields as $fieldKey => $fieldConfig) {
                // Ensure field exists in original metadata to avoid arbitrary key injection
                $def = $app->getRegisteredMetadataByMetakey($fieldKey, $entityClass);
                if (!$def) {
                    continue;
                }

                $sanitizedConfig[$entityClass][$fieldKey] = [
                    'label' => filter_var($fieldConfig['label'] ?? '', FILTER_SANITIZE_STRING),
                    'required' => (isset($fieldConfig['required']) && $fieldConfig['required'] == '1'),
                    'enabled' => !(isset($fieldConfig['enabled']) && $fieldConfig['enabled'] == '0')
                ];
            }
        }
        
        $json = json_encode($sanitizedConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        $configFile = BASE_PATH . 'files/config/field-overrides.json';
        
        // Ensure directory exists
        if (!is_dir(dirname($configFile))) {
            mkdir(dirname($configFile), 0755, true);
        }

        file_put_contents($configFile, $json);
        
        $app->redirect($app->createUrl('dynamic-field-config', 'index'), 200);
    }
}

<?php
return [
    'plugins' => [
        'DynamicFieldConfig' => ['namespace' => 'DynamicFieldConfig'],
        'RootThemeCustomizer' => ['namespace' => 'RootThemeCustomizer'],
        'MandatoryMFA' => ['namespace' => 'MandatoryMFA'],
        // Asegurar que otros plugins necesarios también estén aquí si no se cargan por defecto
        'MultipleLocalAuth' => ['namespace' => 'MultipleLocalAuth'],
        'WpForumSso' => ['namespace' => 'WpForumSso'],
        'SpamDetector' => [
            'namespace' => 'SpamDetector',
            'config' => [
                'entities' => [
                    'Event',
                    'Agent',
                    'Space',
                    'Project',
                    'Opportunity',
                ]
            ]
        ],
        'OpportunityPreloader' => ['namespace' => 'OpportunityPreloader'],
    ]
];

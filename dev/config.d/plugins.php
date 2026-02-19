<?php
return [
    'plugins' => [
        'DynamicFieldConfig' => ['namespace' => 'DynamicFieldConfig'],
        'RootThemeCustomizer' => ['namespace' => 'RootThemeCustomizer'],
        // Asegurar que otros plugins necesarios también estén aquí si no se cargan por defecto
        'MultipleLocalAuth' => ['namespace' => 'MultipleLocalAuth'],
    ]
];

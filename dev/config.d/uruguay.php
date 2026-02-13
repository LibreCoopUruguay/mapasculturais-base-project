<?php
return [
    // Configuración Regional y de Idioma (Debe cargarse antes que los módulos)
    'app.locale' => 'es_ES',
    
    // Configuración de Direcciones (Uruguay)
    'address.defaultCountryCode' => 'UY',
    'address.countryFieldEnabled' => false,
    
    // Etiquetas de niveles territoriales (claves 1-6 requeridas para evitar warnings en CountryLocalizations)
    'address.defaultLevelsLabels' => [
        1 => 'Región',            // No usado en UI pero requerido por backend
        2 => 'Departamento',      // Nivel principal (Artigas, Canelones, etc.)
        3 => 'Zona',              // No usado en UI
        4 => 'Municipio',         // Nivel secundario (Municipio A, B, etc.)
        5 => 'Distrito',          // No usado en UI
        6 => 'Barrio',            // Nivel terciario (Barrios de Montevideo)
    ],

    // Configuración legacy de Estados y Ciudades (usado por algunos componentes viejos)
    'statesAndCities.file' => 'uruguay.php',
    'statesAndCities.enable' => true,
    'statesAndCities.countryCode' => 'UY',
];

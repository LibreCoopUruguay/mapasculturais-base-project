<?php 

return [
    'app.siteName' => 'Cultura En Linea',
    'app.siteDescription' => '',
    
    // Configuración de idioma
    // NOTA: app.lcode NO se configura porque la versión 7.6.22 no soporta
    // traducción de URLs de forma estable (rompe API).
    // Ver: conclusion_urls.md para más detalles
    // 'app.lcode' => 'es_ES',
    
    // Define o tema ativo no site principal. Deve ser informado o namespace do tema e neste deve existir uma classe Theme.
    'themes.active' => 'themeCulturaenlinea',

    // Ids dos selos verificadores. Para utilizar múltiplos selos informe os ids separados por vírgula.
    'app.verifiedSealsIds' => '1', 

];
#!/bin/bash

# Script para crear datos de prueba en Mapas Culturais
# Ejecutar desde el contenedor Docker

set -e

echo "========================================="
echo "Creando datos de prueba para Mapas Culturais"
echo "========================================="

echo ""
echo "Generando script PHP para crear datos..."

cat > create-test-data.php << 'EOPHP'
<?php
/**
 * Script para crear datos de prueba en Mapas Culturais
 * Ejecutar: php create-test-data.php
 */

require_once '/var/www/src/protected/application/bootstrap.php';

$app = \MapasCulturais\App::i();
$em = $app->em;

echo "========================================\n";
echo "Creando datos de prueba\n";
echo "========================================\n\n";

// Obtener usuario admin
$admin = $app->repo('User')->find(1);
if (!$admin) {
    die("Error: No se encontró usuario administrador\n");
}

$app->auth->authenticateUser($admin);

// Departamentos de Uruguay
$departamentos = [
    'Montevideo', 'Canelones', 'Maldonado', 'Rocha', 'Treinta y Tres',
    'Cerro Largo', 'Rivera', 'Artigas', 'Salto', 'Paysandú',
    'Río Negro', 'Soriano', 'Colonia', 'San José', 'Flores',
    'Florida', 'Durazno', 'Tacuarembó', 'Lavalleja'
];

$areas = [
    'Artes Visuales', 'Audiovisual', 'Circo', 'Danza',
    'Literatura', 'Música', 'Teatro', 'Artes Integradas'
];

// 1. Crear Agentes
echo "1. Creando 15 agentes de prueba...\n";
$agentes = [];
for ($i = 1; $i <= 15; $i++) {
    $agent = new \MapasCulturais\Entities\Agent($admin);
    $agent->name = "Agente Cultural $i";
    $agent->type = 1; // Individual
    $agent->shortDescription = "Agente cultural de prueba #$i";
    $agent->longDescription = "Este es un agente cultural de prueba creado para testing del sistema.";
    
    // Datos uruguayos
    $agent->En_Estado = $departamentos[array_rand($departamentos)];
    $agent->emailPublico = "agente$i@test.uy";
    $agent->telefonePublico = sprintf("099%06d", $i);
    
    // Términos de área
    $agent->terms = ['area' => [$areas[array_rand($areas)]]];
    
    $agent->save(true);
    $agentes[] = $agent;
    echo "  ✓ Creado: {$agent->name} (ID: {$agent->id})\n";
}

// 2. Crear Espacios
echo "\n2. Creando 10 espacios culturales...\n";
$espacios = [];
$tiposEspacio = ['Teatro', 'Galería de Arte', 'Centro Cultural', 'Sala de Ensayo', 'Biblioteca'];
for ($i = 1; $i <= 10; $i++) {
    $space = new \MapasCulturais\Entities\Space($admin);
    $space->name = $tiposEspacio[array_rand($tiposEspacio)] . " $i";
    $space->type = 10; // Tipo genérico
    $space->shortDescription = "Espacio cultural de prueba #$i";
    $space->longDescription = "Este es un espacio cultural de prueba creado para testing del sistema.";
    
    // Ubicación en Uruguay
    $dept = $departamentos[array_rand($departamentos)];
    $space->En_Estado = $dept;
    $space->En_Municipio = $dept;
    $space->endereco = "Calle de Prueba $i";
    $space->location = new \MapasCulturais\Types\GeoPoint(-32.5 + (rand(-100, 100) / 100), -55.7 + (rand(-100, 100) / 100));
    
    $space->emailPublico = "espacio$i@test.uy";
    $space->telefonePublico = sprintf("099%06d", 1000 + $i);
    
    $space->save(true);
    $espacios[] = $space;
    echo "  ✓ Creado: {$space->name} (ID: {$space->id})\n";
}

// 3. Crear Proyectos
echo "\n3. Creando 5 proyectos culturales...\n";
$proyectos = [];
for ($i = 1; $i <= 5; $i++) {
    $project = new \MapasCulturais\Entities\Project($admin);
    $project->name = "Proyecto Cultural $i";
    $project->type = 1;
    $project->shortDescription = "Proyecto cultural de prueba #$i";
    $project->longDescription = "Este es un proyecto cultural de prueba creado para testing del sistema.";
    
    // Fechas
    $project->registrationFrom = new \DateTime('now');
    $project->registrationTo = new \DateTime('+30 days');
    
    $project->save(true);
    $proyectos[] = $project;
    echo "  ✓ Creado: {$project->name} (ID: {$project->id})\n";
}

// 4. Crear Eventos
echo "\n4. Creando 8 eventos...\n";
$eventos = [];
for ($i = 1; $i <= 8; $i++) {
    $event = new \MapasCulturais\Entities\Event($admin);
    $event->name = "Evento Cultural $i";
    $event->type = 1;
    $event->shortDescription = "Evento cultural de prueba #$i";
    $event->longDescription = "Este es un evento cultural de prueba creado para testing del sistema.";
    
    // Asignar espacio aleatorio
    if (!empty($espacios)) {
        $event->space = $espacios[array_rand($espacios)];
    }
    
    $event->save(true);
    
    // Crear ocurrencia
    $occurrence = new \MapasCulturais\Entities\EventOccurrence();
    $occurrence->space = $event->space;
    $occurrence->event = $event;
    $occurrence->rule = [
        'startsAt' => (new \DateTime('+' . rand(1, 30) . ' days'))->format('Y-m-d'),
        'startsOn' => '19:00',
        'duration' => 120,
        'frequency' => 'once'
    ];
    $occurrence->save(true);
    
    $eventos[] = $event;
    echo "  ✓ Creado: {$event->name} (ID: {$event->id})\n";
}

// 5. Crear Oportunidad (Llamado) con inscripciones
echo "\n5. Creando llamado con 10 inscripciones...\n";

$opportunity = new \MapasCulturais\Entities\Opportunity($admin);
$opportunity->name = "Llamado de Prueba - Apoyo a Proyectos Culturales 2025";
$opportunity->type = 1;
$opportunity->shortDescription = "Llamado de prueba para testing del sistema de inscripciones";
$opportunity->longDescription = "Este es un llamado de prueba creado para verificar el funcionamiento del sistema de inscripciones. Incluye 10 inscripciones de prueba con diferentes estados.";

// Fechas de inscripción
$opportunity->registrationFrom = new \DateTime('-5 days');
$opportunity->registrationTo = new \DateTime('+25 days');

// Configuración de inscripción
$opportunity->registrationCategories = json_encode(['Individual', 'Colectivo']);
$opportunity->useRegistrations = true;

$opportunity->save(true);
echo "  ✓ Creado: {$opportunity->name} (ID: {$opportunity->id})\n";

// Crear 10 inscripciones
echo "\n  Creando inscripciones...\n";
$estados = [1, 2, 3, 8, 10]; // Pendiente, Inválida, Enviada, Válida, Suplente
for ($i = 0; $i < 10; $i++) {
    $registration = new \MapasCulturais\Entities\Registration();
    $registration->opportunity = $opportunity;
    $registration->owner = $agentes[$i];
    $registration->category = ($i % 2 == 0) ? 'Individual' : 'Colectivo';
    $registration->number = sprintf('%04d', $i + 1);
    
    // Datos del formulario
    $registration->field_1 = "Proyecto de prueba #" . ($i + 1);
    $registration->field_2 = "Descripción del proyecto cultural de prueba número " . ($i + 1);
    
    $registration->save(true);
    
    // Asignar estado aleatorio
    $registration->status = $estados[array_rand($estados)];
    $registration->save(true);
    
    echo "    ✓ Inscripción #{$registration->number} - {$agentes[$i]->name} (Estado: {$registration->status})\n";
}

echo "\n========================================\n";
echo "✓ Datos de prueba creados exitosamente\n";
echo "========================================\n\n";

echo "Resumen:\n";
echo "  - Agentes: 15\n";
echo "  - Espacios: 10\n";
echo "  - Proyectos: 5\n";
echo "  - Eventos: 8\n";
echo "  - Llamados: 1 (con 10 inscripciones)\n\n";

echo "Acceda al sistema para ver los datos creados:\n";
echo "  - Llamado: http://localhost/oportunidade/{$opportunity->id}\n";
echo "  - Inscripciones: http://localhost/oportunidade/{$opportunity->id}/inscricoes\n\n";

EOPHP

echo "✓ Script PHP generado: create-test-data.php"
echo ""
echo "Para ejecutarlo:"
echo "  docker cp create-test-data.php dev-mapas-1:/var/www/"
echo "  docker exec -it dev-mapas-1 php /var/www/create-test-data.php"
echo ""

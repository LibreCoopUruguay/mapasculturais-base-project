<?php
require_once '/var/www/public/bootstrap.php';

global $app;
if (!isset($app)) {
    $app = \MapasCulturais\App::i();
}

$user = $app->repo('User')->find(1);
if (!$user) {
    $user = $app->repo('User')->findOneBy(['status' => \MapasCulturais\Entities\User::STATUS_ACTIVE]);
}
if (!$user) {
    echo "Error: Inicia sesion en la pagina primero para tener un usuario.\n";
    exit;
}

$app->disableAccessControl();

$agent = new \MapasCulturais\Entities\Agent;
$agent->name = 'Agente de Prueba LibreCoop';
$agent->shortDescription = 'Agente para probar el sistema.';
$agent->type = 1;
$agent->user = $user;
$agent->status = 1;
$agent->save(true);

$project = new \MapasCulturais\Entities\Project;
$project->name = 'Proyecto de Prueba';
$project->shortDescription = 'Proyecto padre para oportunidades.';
$project->type = 1;
$project->owner = $agent;
$project->status = 1;
$project->save(true);

echo "=====================================\n";
echo "Creando 3 Oportunidades de Prueba...\n";

$opportunities = [];
for ($i = 1; $i <= 3; $i++) {
    // Usamos ProjectOpportunity porque pertenecen a un Proyecto
    $opp = new \MapasCulturais\Entities\ProjectOpportunity;
    $opp->name = "Oportunidad de Búsqueda #$i";
    $opp->shortDescription = "Llamado de prueba número $i para comprobar el buscador.";
    $opp->type = 1;
    $opp->owner = $agent;
    $opp->project = $project;
    $opp->status = 1;
    $opp->publishedRegistration = true;
    $opp->registrationFrom = new \DateTime();
    $opp->registrationTo = (new \DateTime())->add(new \DateInterval('P30D'));
    $opp->save(true);
    $opportunities[] = $opp;
    echo "✓ Oportunidad #$i creada (ID: {$opp->id})\n";
}

$app->em->flush();

echo "=====================================\n";
echo "¡TODAS LAS ENTIDADES CREADAS EXITOSAMENTE!\n";
echo "Agente Padre ID: {$agent->id}\n";
echo "Proyecto Padre ID: {$project->id}\n";
echo "=====================================\n\n";

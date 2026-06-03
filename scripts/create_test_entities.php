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

$app->disableAccessControl();

$agent = clone $app->repo('Agent')->findOneBy([]);
if (!$agent) {
    $agent = new \MapasCulturais\Entities\Agent;
    $agent->name = 'Agente Base';
    $agent->shortDescription = 'Agente base para prueba.';
    $agent->type = 1;
    $agent->user = $user;
    $agent->status = 1;
    $agent->save(true);
}

$project = new \MapasCulturais\Entities\Project;
$project->name = 'Proyecto Contenedor de Oportunidades';
$project->shortDescription = 'Proyecto padre.';
$project->type = 1;
$project->owner = $agent;
$project->status = 1;
$project->save(true);

echo "=====================================\n";
echo "Creando 3 Oportunidades de Prueba...\n";

for ($i = 1; $i <= 3; $i++) {
    $opp = new \MapasCulturais\Entities\ProjectOpportunity;
    $opp->name = "Oportunidad de Búsqueda #$i";
    $opp->shortDescription = "Llamado de prueba número $i para comprobar el buscador.";
    $opp->type = 1;
    $opp->owner = $agent;
    $opp->project = $project;
    $opp->parent = $project;
    // Hack just in case Doctrine is complaining about direct properties
    $opp->object_id = $project->id;
    
    $opp->status = 1;
    $opp->publishedRegistration = true;
    $opp->registrationFrom = new \DateTime();
    $opp->registrationTo = (new \DateTime())->add(new \DateInterval('P30D'));
    $opp->save(true);
    echo "✓ Oportunidad #$i creada (ID: {$opp->id})\n";
}

$app->em->flush();

echo "=====================================\n";
echo "¡TODAS LAS ENTIDADES CREADAS EXITOSAMENTE!\n";
echo "=====================================\n\n";

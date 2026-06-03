<?php
// Usar el bootstrap público que inicializa TODA la configuración y el EM ($app->init($config))
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

$space = new \MapasCulturais\Entities\Space;
$space->name = 'Espacio de Prueba';
$space->shortDescription = 'Espacio para probar busquedas.';
$space->type = 10;
$space->owner = $agent;
$space->status = 1;
$space->save(true);

$project = new \MapasCulturais\Entities\Project;
$project->name = 'Proyecto de Prueba';
$project->shortDescription = 'Proyecto padre para oportunidades.';
$project->type = 1;
$project->owner = $agent;
$project->status = 1;
$project->save(true);

$opportunity = new \MapasCulturais\Entities\Opportunity;
$opportunity->name = 'Oportunidad de Busqueda';
$opportunity->shortDescription = 'Oportunidad de prueba.';
$opportunity->type = 1;
$opportunity->owner = $agent;
$opportunity->project = $project;
$opportunity->status = 1;
$opportunity->publishedRegistration = true;
$opportunity->registrationFrom = new \DateTime();
$opportunity->registrationTo = (new \DateTime())->add(new \DateInterval('P30D'));
$opportunity->save(true);

$app->em->flush();

echo "\n=====================================\n";
echo "¡ENTIDADES CREADAS EXITOSAMENTE!\n";
echo "Agente ID: {$agent->id}\n";
echo "Espacio ID: {$space->id}\n";
echo "Proyecto ID: {$project->id}\n";
echo "Oportunidad ID: {$opportunity->id}\n";
echo "=====================================\n\n";

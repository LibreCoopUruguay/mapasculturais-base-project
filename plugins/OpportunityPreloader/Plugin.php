<?php

namespace OpportunityPreloader;

use MapasCulturais\App;

class Plugin extends \MapasCulturais\Plugin
{
    public function _init()
    {
        $app = App::i();
        $plugin = $this;

        /**
         * Hook en el formulario de inscripción (registro):
         * Se dispara en GET /inscricao/<id>/ y en la creación GET /oportunidade/<id>/inscrever/
         * El hook <<registration>>.<<*>> cubre: view, create, edit, single, etc.
         */
        $app->hook('GET(<<registration>>.<<*>>):before', function () use ($app, $plugin) {
            $userId = $app->user->id ?? null;
            if (!$userId) {
                return;
            }

            $agent = $app->user->profile;
            if (!$agent) {
                return;
            }

            // Preparar los datos del agente con todos los campos requeridos
            $app->disableAccessControl();
            $agentData = [
                'id'                => $agent->id,
                'name'              => $agent->name,
                'email'             => $agent->email,
                'fullPrivateAddress'=> $agent->fullPrivateAddress,
                'phoneNumber'       => $agent->phoneNumber,
                'gender'            => $agent->gender,
                'document'          => $agent->document,
                'type'              => $agent->type,
                'legalName'         => $agent->legalName,
                'shortDescription'  => $agent->shortDescription,
                'longDescription'   => $agent->longDescription,
                'site'              => $agent->site,
                'facebook'          => $agent->facebook,
                'instagram'         => $agent->instagram,
                'twitter'           => $agent->twitter,
                'youtube'           => $agent->youtube,
                'vimeo'             => $agent->vimeo,
                'linkedin'          => $agent->linkedin,
                'area'              => $agent->area,
                'terms'             => $agent->terms,
                'occupation'        => $agent->occupation,
                'capacities'        => $agent->capacities,
                'seal'              => $agent->seal,
                'taxonomy'          => $agent->taxonomy,
                'location'          => $agent->location,
                'en_the_agent'      => $agent->en_the_agent,
                'es_the_agent'      => $agent->es_the_agent,
                'fr_the_agent'      => $agent->fr_the_agent,
                'agent_private_info'=> $agent->agent_private_info,
            ];
            $app->enableAccessControl();

            // Inyectar en $MAPAS para que el JS pueda acceder antes del mount de Vue
            $app->view->jsObject['opportunityPreloaderAgentData'] = $agentData;

            // Encolar el script del plugin (usar $plugin, ya que $this aquí es el Controller)
            $app->view->enqueueScript('app', 'opportunity-preloader', 'js/opportunity-preloader.js');
        });
    }

    public function register()
    {
        // No se requiere registro especial
    }
}

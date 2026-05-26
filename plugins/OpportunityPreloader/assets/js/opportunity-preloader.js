(function () {
    'use strict';

    // Los datos del agente los inyecta PHP en $MAPAS.opportunityPreloaderAgentData
    if (typeof $MAPAS === 'undefined' || !$MAPAS.opportunityPreloaderAgentData) {
        return;
    }

    const agentData = $MAPAS.opportunityPreloaderAgentData;

    /**
     * Mapea las propiedades del agente recibidas a la estructura interna.
     */
    const PROPERTY_MAPPING = {
        'name': 'name',
        'email': 'emailPublico',
        'phoneNumber': 'telefonePublico',
        'fullPrivateAddress': 'fullPrivateAddress',
        'gender': 'genero',
        'document': 'documento',
        'legalName': 'nomeCompleto',
        'shortDescription': 'shortDescription',
        'longDescription': 'longDescription',
        'site': 'site',
        'facebook': 'facebook',
        'instagram': 'instagram',
        'twitter': 'twitter',
        'youtube': 'youtube',
        'vimeo': 'vimeo',
        'linkedin': 'linkedin',
        'area': 'area',
        'terms': 'terms',
        'occupation': 'occupation',
        'capacities': 'capacities',
        'seal': 'seal',
        'taxonomy': 'taxonomy',
        'en_the_agent': 'en_the_agent',
        'es_the_agent': 'es_the_agent',
        'fr_the_agent': 'fr_the_agent',
        'agent_private_info': 'agent_private_info',
    };

    /**
     * Precargar los datos del agente en el entity de la inscripción.
     * Funciona sobre el scope de AngularJS de RegistrationFieldsController.
     */
    function preloadAgentData() {
        const el = document.querySelector('[ng-controller="RegistrationFieldsController"]');
        if (!el || typeof angular === 'undefined') {
            return false;
        }

        const scope = angular.element(el).scope();
        if (!scope || !scope.entity || !scope.data || !scope.data.fields) {
            return false;
        }

        let preloadedCount = 0;

        scope.data.fields.forEach(field => {
            if (field.fieldType === 'agent-owner-field') {
                const prop = field.config.entityField;
                const fieldName = field.fieldName; // Ej: field_123

                // Obtener el valor correspondiente del agente
                let value = agentData[prop];

                // Si no se encuentra con la clave directa, probar con el mapeo
                if (value === undefined || value === null) {
                    const mappedKey = Object.keys(PROPERTY_MAPPING).find(key => PROPERTY_MAPPING[key] === prop);
                    if (mappedKey) {
                        value = agentData[mappedKey];
                    }
                }

                if (value !== undefined && value !== null && value !== '') {
                    // Solo precargar si el campo está vacío para no pisar datos ingresados
                    if (scope.entity[fieldName] === undefined || scope.entity[fieldName] === null || scope.entity[fieldName] === '') {
                        scope.entity[fieldName] = value;
                        preloadedCount++;
                    }
                }
            }
        });

        if (preloadedCount > 0) {
            scope.$apply();
            console.info('[OpportunityPreloader] Precargados ' + preloadedCount + ' campos del agente en el formulario.');
            return true;
        }

        return false;
    }

    /**
     * Estrategia de inicialización:
     * Reintentar periódicamente hasta que el controlador de AngularJS esté listo y el scope disponible.
     */
    function tryPreload(attempts) {
        if (preloadAgentData()) {
            return;
        }
        if (attempts > 0) {
            setTimeout(() => tryPreload(attempts - 1), 200);
        }
    }

    // Intentar inmediatamente y luego con reintentos (max 5s)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => tryPreload(25));
    } else {
        tryPreload(25);
    }

})();
-- Script para corregir los campos object_type y object_id de la oportunidad ID 6
-- Estos campos deben apuntar a la propia oportunidad, no al agente
-- Ejecutar: docker exec -i dev-db-1 psql -U mapas -d mapas < fix-opportunity-6.sql

BEGIN;

-- Corregir object_type y object_id
UPDATE opportunity 
SET 
    object_type = 'MapasCulturais\Entities\Opportunity',
    object_id = id
WHERE id = 6;

-- Verificar el cambio
SELECT id, name, type, agent_id, status, object_type, object_id, published_registrations 
FROM opportunity 
WHERE id = 6;

COMMIT;

-- Resultado esperado:
-- object_type debería ser: MapasCulturais\Entities\Opportunity
-- object_id debería ser: 6

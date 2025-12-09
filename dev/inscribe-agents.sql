-- Script para inscribir 6 agentes al llamado ID 6
-- Ejecutar: docker exec -i dev-db-1 psql -U mapas -d mapas < inscribe-agents.sql

BEGIN;

DO $$
DECLARE
    v_opportunity_id INTEGER := 6;
    v_agent_ids INTEGER[] := ARRAY[126, 127, 128, 129, 130, 131]; -- Primeros 6 agentes creados
    v_registration_id INTEGER;
    i INTEGER;
    v_counter INTEGER := 1;
BEGIN
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Inscribiendo agentes al llamado ID %', v_opportunity_id;
    RAISE NOTICE '========================================';
    RAISE NOTICE '';
    
    FOR i IN 1..6 LOOP
        INSERT INTO registration (
            opportunity_id, agent_id, category, number,
            status, create_timestamp, sent_timestamp
        ) VALUES (
            v_opportunity_id,
            v_agent_ids[i],
            CASE WHEN i % 2 = 0 THEN 'Individual' ELSE 'Colectivo' END,
            LPAD(i::TEXT, 4, '0'),
            CASE (i % 3)
                WHEN 0 THEN 1  -- Pendiente
                WHEN 1 THEN 3  -- Enviada
                WHEN 2 THEN 8  -- Válida
            END,
            NOW() - (i || ' hours')::INTERVAL,
            NOW() - (i || ' hours')::INTERVAL + INTERVAL '1 minute'
        ) RETURNING id INTO v_registration_id;
        
        -- Agregar metadatos básicos
        INSERT INTO registration_meta (object_id, key, value)
        VALUES 
            (v_registration_id, 'field_1', '"Proyecto de prueba #' || i || '"'),
            (v_registration_id, 'field_2', '"Descripción del proyecto de prueba número ' || i || '"');
        
        RAISE NOTICE '  ✓ Inscripción #% - Agente ID % (Estado: %)', 
            LPAD(i::TEXT, 4, '0'), 
            v_agent_ids[i],
            CASE (i % 3)
                WHEN 0 THEN 'Pendiente'
                WHEN 1 THEN 'Enviada'
                WHEN 2 THEN 'Válida'
            END;
    END LOOP;
    
    RAISE NOTICE '';
    RAISE NOTICE '========================================';
    RAISE NOTICE '✓ Inscripciones creadas exitosamente';
    RAISE NOTICE '========================================';
    RAISE NOTICE '';
    RAISE NOTICE 'Ver inscripciones en:';
    RAISE NOTICE '  http://localhost/oportunidade/%/inscricoes', v_opportunity_id;
    RAISE NOTICE '';
END $$;

COMMIT;

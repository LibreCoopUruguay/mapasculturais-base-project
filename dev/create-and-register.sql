-- Script para crear 6 agentes e inscribirlos al llamado ID 6
-- Ejecutar: docker exec -i dev-db-1 psql -U mapas -d mapas < create-and-register.sql

BEGIN;

DO $$
DECLARE
    v_user_id INTEGER := 1;
    v_opportunity_id INTEGER := 6;
    v_agent_ids INTEGER[];
    v_registration_id INTEGER;
    i INTEGER;
BEGIN
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Creando 6 agentes e inscribiéndolos';
    RAISE NOTICE '========================================';
    
    -- 1. Crear 6 Agentes
    RAISE NOTICE '';
    RAISE NOTICE '1. Creando 6 agentes de prueba...';
    
    FOR i IN 1..6 LOOP
        INSERT INTO agent (
            name, type, short_description, long_description,
            status, create_timestamp, update_timestamp, user_id
        ) VALUES (
            'Agente Prueba ' || i,
            1,
            'Agente de prueba #' || i,
            'Este es un agente de prueba creado para inscripciones.',
            1,
            NOW(),
            NOW(),
            v_user_id
        );
        
        v_agent_ids := array_append(v_agent_ids, currval('agent_id_seq'));
        RAISE NOTICE '  ✓ Creado: Agente Prueba % (ID: %)', i, currval('agent_id_seq');
    END LOOP;
    
    -- 2. Inscribir agentes al llamado
    RAISE NOTICE '';
    RAISE NOTICE '2. Inscribiendo agentes al llamado ID %...', v_opportunity_id;
    
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
        
        RAISE NOTICE '  ✓ Inscripción #% - Agente Prueba % (Estado: %)', 
            LPAD(i::TEXT, 4, '0'), 
            i,
            CASE (i % 3)
                WHEN 0 THEN 'Pendiente'
                WHEN 1 THEN 'Enviada'
                WHEN 2 THEN 'Válida'
            END;
    END LOOP;
    
    RAISE NOTICE '';
    RAISE NOTICE '========================================';
    RAISE NOTICE '✓ Proceso completado exitosamente';
    RAISE NOTICE '========================================';
    RAISE NOTICE '';
    RAISE NOTICE 'Ver inscripciones en:';
    RAISE NOTICE '  http://localhost/oportunidade/%/inscricoes', v_opportunity_id;
    RAISE NOTICE '';
END $$;

COMMIT;

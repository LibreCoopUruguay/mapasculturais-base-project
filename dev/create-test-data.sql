-- Script SQL para crear datos de prueba en Mapas Culturais
-- Ejecutar: docker exec -i dev-db-1 psql -U mapas -d mapas < create-test-data.sql
-- NOTA: Este script crea solo agentes y un llamado con inscripciones

BEGIN;

DO $$
DECLARE
    v_user_id INTEGER := 1;
    v_agent_ids INTEGER[];
    v_opportunity_id INTEGER;
    v_registration_id INTEGER;
    i INTEGER;
BEGIN
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Creando datos de prueba';
    RAISE NOTICE '========================================';
    
    -- 1. Crear 15 Agentes
    RAISE NOTICE '';
    RAISE NOTICE '1. Creando 15 agentes de prueba...';
    
    FOR i IN 1..15 LOOP
        INSERT INTO agent (
            name, type, short_description, long_description,
            status, create_timestamp, update_timestamp, user_id
        ) VALUES (
            'Agente Cultural ' || i,
            1,
            'Agente cultural de prueba #' || i,
            'Este es un agente cultural de prueba creado para testing del sistema.',
            1,
            NOW(),
            NOW(),
            v_user_id
        );
        
        v_agent_ids := array_append(v_agent_ids, currval('agent_id_seq'));
        RAISE NOTICE '  ✓ Creado: Agente Cultural % (ID: %)', i, currval('agent_id_seq');
    END LOOP;
    
    -- 2. Crear Oportunidad (Llamado) con 10 inscripciones
    RAISE NOTICE '';
    RAISE NOTICE '2. Creando llamado con 10 inscripciones...';
    
    INSERT INTO opportunity (
        name, type, short_description, long_description,
        registration_from, registration_to,
        registration_categories, agent_id, published_registrations, object_type,
        status, create_timestamp, update_timestamp
    ) VALUES (
        'Llamado de Prueba - Apoyo a Proyectos Culturales 2025',
        1,
        'Llamado de prueba para testing del sistema de inscripciones',
        'Este es un llamado de prueba creado para verificar el funcionamiento del sistema de inscripciones. Incluye 10 inscripciones de prueba con diferentes estados.',
        NOW() - INTERVAL '5 days',
        NOW() + INTERVAL '25 days',
        '["Individual", "Colectivo"]',
        v_agent_ids[1],
        false,
        'MapasCulturais\Entities\Opportunity',
        1,
        NOW(),
        NOW()
    ) RETURNING id INTO v_opportunity_id;
    
    RAISE NOTICE '  ✓ Creado: Llamado de Prueba (ID: %)', v_opportunity_id;
    
    -- Crear 10 inscripciones
    RAISE NOTICE '';
    RAISE NOTICE '  Creando inscripciones...';
    
    FOR i IN 0..9 LOOP
        INSERT INTO registration (
            opportunity_id, agent_id, category, number,
            status, create_timestamp, sent_timestamp
        ) VALUES (
            v_opportunity_id,
            v_agent_ids[i + 1],
            CASE WHEN i % 2 = 0 THEN 'Individual' ELSE 'Colectivo' END,
            LPAD((i + 1)::TEXT, 4, '0'),
            CASE (i % 5)
                WHEN 0 THEN 1  -- Pendiente
                WHEN 1 THEN 2  -- Inválida
                WHEN 2 THEN 3  -- Enviada
                WHEN 3 THEN 8  -- Válida
                WHEN 4 THEN 10 -- Suplente
            END,
            NOW() - (i || ' hours')::INTERVAL,
            NOW() - (i || ' hours')::INTERVAL + INTERVAL '1 minute'
        ) RETURNING id INTO v_registration_id;
        
        -- Agregar metadatos de la inscripción
        INSERT INTO registration_meta (object_id, key, value)
        VALUES 
            (v_registration_id, 'field_1', '"Proyecto de prueba #' || (i + 1) || '"'),
            (v_registration_id, 'field_2', '"Descripción del proyecto cultural de prueba número ' || (i + 1) || '"');
        
        RAISE NOTICE '    ✓ Inscripción #% - Agente ID % (Estado: %)', 
            LPAD((i + 1)::TEXT, 4, '0'), 
            v_agent_ids[i + 1],
            CASE (i % 5)
                WHEN 0 THEN 'Pendiente'
                WHEN 1 THEN 'Inválida'
                WHEN 2 THEN 'Enviada'
                WHEN 3 THEN 'Válida'
                WHEN 4 THEN 'Suplente'
            END;
    END LOOP;
    
    RAISE NOTICE '';
    RAISE NOTICE '========================================';
    RAISE NOTICE '✓ Datos de prueba creados exitosamente';
    RAISE NOTICE '========================================';
    RAISE NOTICE '';
    RAISE NOTICE 'Resumen:';
    RAISE NOTICE '  - Agentes: 15';
    RAISE NOTICE '  - Llamados: 1 (con 10 inscripciones)';
    RAISE NOTICE '';
    RAISE NOTICE 'Acceda al sistema para ver los datos creados:';
    RAISE NOTICE '  - Llamado: http://localhost/oportunidade/%', v_opportunity_id;
    RAISE NOTICE '  - Inscripciones: http://localhost/oportunidade/%/inscricoes', v_opportunity_id;
    RAISE NOTICE '';
END $$;

COMMIT;

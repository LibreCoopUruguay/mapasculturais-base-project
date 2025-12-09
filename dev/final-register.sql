-- Script FINAL para crear 6 agentes e inscribirlos al llamado ID 6
-- Sin metadatos complejos, solo lo esencial
-- Ejecutar: docker exec -i dev-db-1 psql -U mapas -d mapas < final-register.sql

BEGIN;

DO $$
DECLARE
    v_user_id INTEGER := 1;
    v_opportunity_id INTEGER := 6;
    v_agent_id INTEGER;
    v_registration_id INTEGER;
    i INTEGER;
BEGIN
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Creando 6 agentes e inscribiéndolos';
    RAISE NOTICE '========================================';
    RAISE NOTICE '';
    
    FOR i IN 1..6 LOOP
        -- Crear agente
        INSERT INTO agent (
            name, type, short_description, long_description,
            status, create_timestamp, update_timestamp, user_id
        ) VALUES (
            'Agente Prueba ' || i,
            1,
            'Agente de prueba #' || i,
            'Agente de prueba para inscripciones.',
            1,
            NOW(),
            NOW(),
            v_user_id
        ) RETURNING id INTO v_agent_id;
        
        RAISE NOTICE '  ✓ Agente Prueba % creado (ID: %)', i, v_agent_id;
        
        -- Inscribir agente inmediatamente
        INSERT INTO registration (
            opportunity_id, agent_id, category, number,
            status, create_timestamp, sent_timestamp
        ) VALUES (
            v_opportunity_id,
            v_agent_id,
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
        
        RAISE NOTICE '    → Inscrito con número % (Estado: %)', 
            LPAD(i::TEXT, 4, '0'),
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

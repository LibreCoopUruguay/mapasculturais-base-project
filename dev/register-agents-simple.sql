-- Script simplificado para inscribir los 6 agentes ya creados al llamado ID 6
-- Ejecutar: docker exec -i dev-db-1 psql -U mapas -d mapas < register-agents-simple.sql

BEGIN;

DO $$
DECLARE
    v_opportunity_id INTEGER := 6;
    v_agent_ids INTEGER[] := ARRAY[151, 152, 153, 154, 155, 156];
    v_registration_id INTEGER;
    i INTEGER;
BEGIN
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Inscribiendo 6 agentes al llamado ID %', v_opportunity_id;
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
        
        RAISE NOTICE '  ✓ Inscripción #% - Agente Prueba % (ID: %, Estado: %)', 
            LPAD(i::TEXT, 4, '0'), 
            i,
            v_registration_id,
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

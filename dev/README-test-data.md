# Script de Datos de Prueba - Mapas Culturais

Este script SQL crea datos de prueba para el sistema Mapas Culturais.

## ✅ Lo que el script crea automáticamente

- **15 Agentes Culturales** con nombres "Agente Cultural 1" a "Agente Cultural 15"

## 📝 Uso del Script

### Ejecutar el script

```bash
cd dev
docker exec -i dev-db-1 psql -U mapas -d mapas < create-test-data.sql
```

### Resultado esperado

```
NOTICE:  ========================================
NOTICE:  Creando datos de prueba
NOTICE:  ========================================
NOTICE:  
NOTICE:  1. Creando 15 agentes de prueba...
NOTICE:    ✓ Creado: Agente Cultural 1 (ID: X)
NOTICE:    ✓ Creado: Agente Cultural 2 (ID: X)
...
NOTICE:    ✓ Creado: Agente Cultural 15 (ID: X)
COMMIT
```

## 🎯 Crear Llamado con Inscripciones (Manual)

Después de ejecutar el script, sigue estos pasos para crear un llamado con inscripciones:

### 1. Crear el Llamado

1. Accede a http://localhost
2. Inicia sesión como administrador
3. Ve a "Oportunidades" → "Crear nueva oportunidad"
4. Completa el formulario:
   - **Nombre:** "Llamado de Prueba - Apoyo a Proyectos Culturales 2025"
   - **Tipo:** Selecciona el tipo apropiado
   - **Descripción corta:** "Llamado de prueba para testing"
   - **Fecha de inicio:** Hace 5 días
   - **Fecha de fin:** En 25 días
   - **Categorías:** Individual, Colectivo
5. Guarda el llamado

### 2. Inscribir Agentes al Llamado

1. Accede al llamado creado
2. Para cada uno de los 10 primeros agentes:
   - Cambia al perfil del agente (o usa la opción de inscripción)
   - Haz clic en "Inscribirse"
   - Completa el formulario básico
   - Envía la inscripción

### 3. Asignar Estados a las Inscripciones

Como administrador, asigna diferentes estados a las inscripciones:
- Inscripción 1-2: Pendiente
- Inscripción 3-4: Inválida
- Inscripción 5-6: Enviada
- Inscripción 7-8: Válida
- Inscripción 9-10: Suplente

## 🔄 Limpiar Datos de Prueba

Para eliminar todos los agentes de prueba creados:

```sql
-- Ejecutar en psql
DELETE FROM agent WHERE name LIKE 'Agente Cultural %';
```

## 📌 Notas

- El script crea solo agentes porque la estructura de `opportunity` y `registration` en la base de datos requiere muchos campos internos del framework
- La creación manual del llamado garantiza que todos los campos requeridos se completen correctamente
- Este enfoque es más mantenible y permite verificar que el sistema funciona correctamente

## 🐛 Troubleshooting

### Error: "relation does not exist"
- Verifica que estás conectado a la base de datos correcta: `mapas`
- Asegúrate de que el contenedor de base de datos está corriendo

### Los agentes no aparecen en la interfaz
- Limpia el cache: `docker exec dev-mapas-1 rm -rf /var/www/var/cache/*`
- Reinicia el contenedor: `docker compose restart mapas`

# Scripts de Gestión del Tema

Este directorio contiene scripts para facilitar el trabajo con el submódulo `themeCulturaenlinea`.

## 📋 Scripts Disponibles

### 1. `theme-status.sh` - Ver Estado
Muestra el estado completo del tema y del proyecto base.

```bash
./theme-status.sh
```

**Muestra:**
- Estado del proyecto base
- Estado del tema (rama, commits, cambios)
- Información del submódulo

---

### 2. `theme-diff.sh` - Ver Diferencias
Muestra los cambios realizados en el tema que aún no han sido commiteados.

```bash
./theme-diff.sh
```

**Útil para:**
- Revisar qué archivos modificaste
- Ver las diferencias antes de hacer commit

---

### 3. `theme-commit.sh` - Hacer Commit
Automatiza el proceso completo de commit del tema y actualización del proyecto base.

```bash
./theme-commit.sh "Mensaje del commit"
```

**Qué hace:**
1. ✅ Detecta cambios en el tema
2. 📝 Hace commit en el repositorio del tema
3. 🚀 Pregunta si hacer push del tema
4. 🔄 Actualiza la referencia del submódulo en el proyecto base
5. 🚀 Pregunta si hacer push del proyecto base

**Ejemplo:**
```bash
./theme-commit.sh "Agregar nuevo componente de galería"
```

---

### 4. `theme-update.sh` - Actualizar Tema
Descarga los últimos cambios del tema desde el repositorio remoto y actualiza la referencia.

```bash
./theme-update.sh
```

**Qué hace:**
1. 📥 Hace pull del repositorio del tema
2. 🔄 Actualiza la referencia del submódulo en el proyecto base
3. 🚀 Pregunta si hacer push del proyecto base

**Útil cuando:**
- Otro desarrollador hizo cambios en el tema
- Trabajas en múltiples máquinas
- Quieres sincronizar con la última versión

---

## 🔄 Workflow Típico

### Desarrollo Normal

```bash
# 1. Ver el estado actual
./theme-status.sh

# 2. Hacer cambios en themes/themeCulturaenlinea/...
# (editar archivos)

# 3. Ver qué cambiaste
./theme-diff.sh

# 4. Hacer commit y push
./theme-commit.sh "Descripción de los cambios"
```

### Sincronizar con Cambios Remotos

```bash
# Actualizar el tema desde el repositorio remoto
./theme-update.sh
```

---

## 🎯 Ventajas de Usar Estos Scripts

✅ **Automatización**: No necesitas recordar todos los comandos git  
✅ **Seguridad**: Confirmaciones interactivas antes de hacer push  
✅ **Consistencia**: Siempre se actualiza correctamente la referencia del submódulo  
✅ **Claridad**: Mensajes informativos en cada paso  
✅ **Prevención de errores**: Verifica cambios antes de proceder  

---

## 📝 Notas Importantes

- Los scripts deben ejecutarse desde la **raíz del proyecto** (`mapasculturais-base-project/`)
- Siempre revisa los cambios con `theme-diff.sh` antes de hacer commit
- Los commits del tema y del proyecto base son **independientes**
- El proyecto base solo guarda la **referencia** (hash) del commit del tema

---

## 🆘 Solución de Problemas

### "No hay cambios para commitear"
- Verifica que realmente modificaste archivos en `themes/themeCulturaenlinea/`
- Usa `./theme-status.sh` para ver el estado

### "Hay cambios sin commitear"
- Usa `./theme-diff.sh` para ver qué cambió
- Decide si hacer commit o descartar los cambios

### Error de permisos
- Asegúrate de que los scripts tengan permisos de ejecución:
  ```bash
  chmod +x theme-*.sh
  ```

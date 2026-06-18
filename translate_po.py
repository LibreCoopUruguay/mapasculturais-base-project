import polib
from deep_translator import GoogleTranslator
import time
import re

po_file_path = 'themes/themeCulturaenlinea/translations/es_ES.po'
print(f"Cargando archivo: {po_file_path}")
po = polib.pofile(po_file_path)

untranslated = po.untranslated_entries()
total = len(untranslated)
print(f"Total de entradas sin traducir: {total}")

translator = GoogleTranslator(source='pt', target='es')

def preserve_variables(original, translated):
    # Encontrar todas las variables {{...}} en el original
    original_vars = re.findall(r'\{\{[^}]+\}\}', original)
    if not original_vars:
        return translated
    
    # Encontrar las variables en la traducción (pueden estar deformadas por Google)
    translated_vars = re.findall(r'\{\{[^}]+\}\}', translated)
    
    # Si coinciden en cantidad, reemplazamos las deformadas por las originales
    if len(original_vars) == len(translated_vars):
        for o_var, t_var in zip(original_vars, translated_vars):
            translated = translated.replace(t_var, o_var)
    else:
        print(f"Advertencia: Desajuste de variables en: {original}")
        # Retornar el original para no romper el código
        return original
    
    return translated

count = 0
for entry in untranslated:
    count += 1
    original_text = entry.msgid
    
    if not original_text.strip():
        entry.msgstr = ""
        continue
        
    try:
        translated_text = translator.translate(original_text)
        if translated_text is None:
            translated_text = original_text
            
        translated_text = preserve_variables(original_text, translated_text)
        entry.msgstr = translated_text
        
    except Exception as e:
        print(f"❌ Error al traducir '{original_text}': {e}")
        entry.msgstr = original_text
        continue

    if count % 50 == 0:
        print(f"Progreso: {count}/{total} traducidos...")
        
    time.sleep(0.1)

print(f"Guardando el archivo traducido...")
po.save(po_file_path)
print("¡Listo! Traducción masiva finalizada exitosamente.")

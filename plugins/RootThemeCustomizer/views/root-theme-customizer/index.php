<?php
use MapasCulturais\i;
?>
<div class="rtc-page">

    <!-- HEADER -->
    <div class="rtc-header">
        <div class="rtc-header__title">
            <h1><?php i::_e('Personalizar Página de Inicio') ?></h1>
            <p class="rtc-header__subtitle"><?php i::_e('Defina los textos principales, la imagen de fondo y qué secciones de contenido estarán visibles en la portada.') ?></p>
        </div>
        <div class="rtc-header__actions">
            <button type="button" class="rtc-btn rtc-btn--help" onclick="document.getElementById('rtc-help-modal').showModal()">
                <span class="rtc-icon">?</span> <?php i::_e('Ayuda') ?>
            </button>
        </div>
    </div>

    <!-- MODAL DE AYUDA -->
    <dialog id="rtc-help-modal" class="rtc-modal">
        <div class="rtc-modal__content">
            <div class="rtc-modal__header">
                <h2><?php i::_e('Guía de Personalización') ?></h2>
                <button class="rtc-modal__close" onclick="document.getElementById('rtc-help-modal').close()" aria-label="Cerrar">✕</button>
            </div>
            <div class="rtc-modal__body">
                <div class="rtc-help-section">
                    <h3>🖼️ Textos e Imágenes</h3>
                    <p>Configure el mensaje de bienvenida y la identidad visual de la portada.
                    <ul>
                        <li><strong>Título Principal:</strong> El texto grande que aparece sobre la imagen principal.</li>
                        <li><strong>Subtítulo:</strong> Un texto breve descriptivo debajo del título.</li>
                        <li><strong>Imagen Hero:</strong> URL de la imagen de fondo. Se recomienda alta resolución (1920x600px).</li>
                    </ul>
                    </p>
                </div>

                <div class="rtc-help-section">
                    <h3>👁️ Secciones Visibles</h3>
                    <p>Decida qué módulos de contenido aparecen en la página de inicio. Puede ocultar secciones que no estén en uso (ej. si no hay "Oportunidades" abiertas).</p>
                </div>

                <div class="rtc-help-section rtc-help-section--tip">
                    <h3>💡 Tip</h3>
                    <p>Los cambios son reversibles. Si deja los campos de texto vacíos, se mostrarán los valores predeterminados del tema.</p>
                </div>
            </div>
            <div class="rtc-modal__footer">
                <button class="rtc-btn rtc-btn--primary" onclick="document.getElementById('rtc-help-modal').close()"><?php i::_e('Entendido') ?></button>
            </div>
        </div>
    </dialog>

    <!-- FORMULARIO PRINCIPAL -->
    <form action="<?php echo $app->createUrl('root-theme-customizer', 'save'); ?>" method="POST" id="rtc-form">

        <div class="rtc-content">
            
            <!-- GRUPO 1: INFO GENERAL -->
            <div class="rtc-group">
                <div class="rtc-group__header">
                    <span class="rtc-group__icon">🖼️</span>
                    <h3 class="rtc-group__title"><?php i::_e('Textos e Imágenes') ?></h3>
                </div>
                <div class="rtc-group__body">
                    <div class="rtc-form-row">
                        <label class="rtc-label"><?php i::_e('Título Principal') ?></label>
                        <input type="text" name="config[title]" value="<?php echo htmlspecialchars($config['title']); ?>" class="rtc-input" placeholder="<?php i::_e('Título predeterminado') ?>">
                    </div>
                    <div class="rtc-form-row">
                        <label class="rtc-label"><?php i::_e('Subtítulo (Bajada)') ?></label>
                        <textarea name="config[subtitle]" class="rtc-input" rows="3" placeholder="<?php i::_e('Texto descriptivo...') ?>"><?php echo htmlspecialchars($config['subtitle']); ?></textarea>
                    </div>
                    <div class="rtc-form-row">
                        <label class="rtc-label"><?php i::_e('URL Imagen Hero (Fondo)') ?></label>
                        <div class="rtc-input-group">
                            <input type="text" name="config[hero_image]" value="<?php echo htmlspecialchars($config['hero_image']); ?>" class="rtc-input" placeholder="https://ejemplo.com/imagen.jpg">
                            <small class="rtc-hint">Recomendado: 1920x600px</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GRUPO 2: VISIBILIDAD -->
            <div class="rtc-group">
                <div class="rtc-group__header">
                    <span class="rtc-group__icon">👁️</span>
                    <h3 class="rtc-group__title"><?php i::_e('Secciones Visibles') ?></h3>
                    <span class="rtc-group__count"><?php echo count($config['sections']); ?> secciones</span>
                </div>
                
                <table class="rtc-table">
                    <thead>
                        <tr>
                            <th class="rtc-th--label">Sección</th>
                            <th class="rtc-th--toggle">Mostrar en Home</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sectionLabels = [
                            'events' => 'Eventos',
                            'agents' => 'Agentes',
                            'spaces' => 'Espacios',
                            'projects' => 'Proyectos',
                            'opportunities' => 'Oportunidades',
                            'developers' => 'Desarrolladores'
                        ];
                        // Ensure all sections exist in the labels array or use key
                        foreach($config['sections'] as $key => $val): 
                            $label = $sectionLabels[$key] ?? ucfirst($key);
                            $checked = $val ? 'checked' : '';
                        ?>
                        <tr class="rtc-row <?php echo !$val ? 'rtc-row--hidden' : ''; ?>">
                            <td class="rtc-cell--label">
                                <span class="rtc-section-label"><?php echo $label; ?></span>
                                <code class="rtc-section-key"><?php echo $key; ?></code>
                            </td>
                            <td class="rtc-cell--toggle">
                                <input type="hidden" name="config[sections][<?php echo $key; ?>]" value="0">
                                <label class="rtc-toggle">
                                    <input type="checkbox" name="config[sections][<?php echo $key; ?>]" value="1" <?php echo $checked; ?> class="rtc-toggle-input">
                                    <span class="rtc-toggle__slider"></span>
                                    <span class="rtc-toggle__label-text"><?php echo $val ? 'Visible' : 'Oculto'; ?></span>
                                </label>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- FOOTER -->
        <div class="rtc-footer">
            <div class="rtc-footer__info">
                <span>⚡ Cambios visibles inmediatamente en la portada.</span>
            </div>
            <button type="submit" class="rtc-btn rtc-btn--primary rtc-btn--large">
                💾 <?php i::_e('Guardar Configuración') ?>
            </button>
        </div>
    </form>
</div>

<style>
/* ── RTC Layout ── */
.rtc-page { padding: 24px 32px; max-width: 900px; font-family: 'Open Sans', sans-serif; }

/* ── Header ── */
.rtc-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 28px; }
.rtc-header h1 { margin: 0 0 6px; font-size: 1.6rem; font-weight: 700; color: #1a1a2e; }
.rtc-header__subtitle { margin: 0; color: #6b7280; font-size: 0.93rem; }

/* ── Groups ── */
.rtc-group { margin-bottom: 28px; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; background: #fff; }
.rtc-group__header { display: flex; align-items: center; gap: 10px; padding: 12px 18px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; }
.rtc-group__icon { font-size: 1.2rem; }
.rtc-group__title { margin: 0; font-size: 0.95rem; font-weight: 600; color: #374151; }
.rtc-group__count { margin-left: auto; font-size: 0.78rem; color: #9ca3af; background: #e5e7eb; padding: 2px 8px; border-radius: 999px; }
.rtc-group__body { padding: 20px; }

/* ── Forms ── */
.rtc-form-row { margin-bottom: 16px; }
.rtc-form-row:last-child { margin-bottom: 0; }
.rtc-label { display: block; font-weight: 600; color: #374151; margin-bottom: 6px; font-size: 0.9rem; }
.rtc-input { width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.95rem; color: #1f2937; transition: border-color .15s; }
.rtc-input:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,.15); }
.rtc-hint { display: block; margin-top: 4px; color: #6b7280; font-size: 0.8rem; }
textarea.rtc-input { resize: vertical; min-height: 80px; }

/* ── Table ── */
.rtc-table { width: 100%; border-collapse: collapse; }
.rtc-table th { background: #f3f4f6; padding: 10px 16px; text-align: left; font-size: 0.78rem; font-weight: 600; color: #6b7280; text-transform: uppercase; }
.rtc-th--toggle { text-align: right; width: 150px; }
.rtc-row { border-bottom: 1px solid #f3f4f6; transition: background .15s; }
.rtc-row:last-child { border-bottom: none; }
.rtc-row:hover { background: #f9fafb; }
.rtc-row--hidden .rtc-section-label { opacity: 0.5; text-decoration: line-through; }
.rtc-cell--label { padding: 12px 16px; }
.rtc-cell--toggle { padding: 12px 16px; text-align: right; }
.rtc-section-label { display: block; font-weight: 500; color: #1f2937; }
.rtc-section-key { display: inline-block; font-size: 0.75rem; color: #9ca3af; background: #f3f4f6; padding: 1px 5px; border-radius: 4px; margin-top: 2px; }

/* ── Buttons ── */
.rtc-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; transition: all .2s; }
.rtc-btn--help { background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; }
.rtc-btn--help:hover { background: #dbeafe; }
.rtc-btn--primary { background: #4361ee; color: #fff; }
.rtc-btn--primary:hover { background: #3451d1; }
.rtc-btn--large { padding: 12px 28px; font-size: 1rem; }
.rtc-icon { width: 18px; height: 18px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; }

/* ── Toggle ── */
.rtc-toggle { position: relative; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; }
.rtc-toggle input { opacity: 0; width: 0; height: 0; position: absolute; }
.rtc-toggle__slider { position: relative; width: 44px; height: 24px; background: #d1d5db; border-radius: 24px; transition: .25s; }
.rtc-toggle__slider::before { content: ''; position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: .25s; box-shadow: 0 1px 3px rgba(0,0,0,.2); }
.rtc-toggle input:checked ~ .rtc-toggle__slider { background: #10b981; }
.rtc-toggle input:checked ~ .rtc-toggle__slider::before { transform: translateX(20px); }
.rtc-toggle__label-text { font-size: 0.85rem; font-weight: 500; color: #4b5563; min-width: 45px; }

/* ── Modal ── */
.rtc-modal { border: none; border-radius: 16px; padding: 0; max-width: 500px; width: 95%; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
.rtc-modal::backdrop { background: rgba(0,0,0,.5); backdrop-filter: blur(4px); }
.rtc-modal__header { display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; border-bottom: 1px solid #e5e7eb; }
.rtc-modal__header h2 { margin: 0; font-size: 1.1rem; }
.rtc-modal__close { background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #6b7280; }
.rtc-modal__body { padding: 20px 24px; }
.rtc-help-section { margin-bottom: 20px; }
.rtc-help-section h3 { margin: 0 0 6px; font-size: 0.95rem; color: #111; }
.rtc-help-section p, .rtc-help-section ul { margin: 0; color: #666; font-size: 0.9rem; line-height: 1.5; }
.rtc-help-section ul { padding-left: 20px; }
.rtc-help-section--tip { background: #f0fdf4; padding: 12px; border-radius: 8px; border: 1px solid #dcfce7; }
.rtc-help-section--tip h3 { color: #166534; }
.rtc-help-section--tip p { color: #15803d; }
.rtc-modal__footer { padding: 16px 24px; border-top: 1px solid #e5e7eb; text-align: right; }

/* ── Footer ── */
.rtc-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb; }
.rtc-footer__info { color: #6b7280; font-size: 0.9rem; }
</style>

<script>
// Toggle label update
document.querySelectorAll('.rtc-toggle-input').forEach(function(input) {
    input.addEventListener('change', function() {
        var label = this.parentElement.querySelector('.rtc-toggle__label-text');
        if(label) label.textContent = this.checked ? 'Visible' : 'Oculto';
        
        // Highlight row
        var row = this.closest('.rtc-row');
        if(row) {
            if(!this.checked) row.classList.add('rtc-row--hidden');
            else row.classList.remove('rtc-row--hidden');
        }
    });
});

// Forzar separación visual label/key por si el CSS del panel interfiere
document.querySelectorAll('.rtc-section-label').forEach(function(el) {
    el.style.cssText = 'display:block !important; font-weight:600; color:#1f2937; margin-bottom: 2px;';
});
document.querySelectorAll('.rtc-section-key').forEach(function(el) {
    el.style.cssText = 'display:inline-block !important; font-size:0.75rem; color:#9ca3af; background:#f3f4f6; padding:1px 5px; border-radius:4px;';
});
</script>

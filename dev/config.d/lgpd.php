<?php
/**
 * Configuración de Términos y Condiciones (LGPD)
 * 
 * Esta configuración define los términos que los usuarios deben aceptar
 * al crear una cuenta en el sistema.
 */

return [
    'LGPD' => [
        'terms' => [
            'title' => 'Términos y Condiciones de Uso',
            'text' => '
                <h3>Términos y Condiciones de Uso</h3>
                <p>Al utilizar esta plataforma, usted acepta los siguientes términos y condiciones:</p>
                <ul>
                    <li>Los datos proporcionados serán utilizados exclusivamente para los fines de la plataforma.</li>
                    <li>Usted es responsable de mantener la confidencialidad de su contraseña.</li>
                    <li>No está permitido el uso indebido de la plataforma.</li>
                    <li>Nos reservamos el derecho de suspender cuentas que violen estos términos.</li>
                </ul>
                <p>Para más información, consulte nuestra <a href="/politica-de-privacidad">Política de Privacidad</a>.</p>
            ',
            'buttonText' => 'Acepto los términos y condiciones',
        ],
        'privacy' => [
            'title' => 'Política de Privacidad',
            'text' => '
                <h3>Política de Privacidad</h3>
                <p>Esta política describe cómo recopilamos, usamos y protegemos su información personal:</p>
                <ul>
                    <li><strong>Datos recopilados:</strong> nombre, email, documento de identidad.</li>
                    <li><strong>Uso de datos:</strong> gestión de cuenta, comunicaciones de la plataforma.</li>
                    <li><strong>Protección:</strong> utilizamos medidas de seguridad para proteger sus datos.</li>
                    <li><strong>Derechos:</strong> puede solicitar acceso, corrección o eliminación de sus datos.</li>
                </ul>
            ',
            'buttonText' => 'Acepto la política de privacidad',
        ],
    ],
];

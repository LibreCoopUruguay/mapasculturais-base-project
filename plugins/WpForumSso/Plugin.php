<?php
namespace WpForumSso;

use MapasCulturais\i;
use MapasCulturais\App;

class Plugin extends \MapasCulturais\Plugin {
    public function _init() {
        // Initialization handled in register()
    }

    public function register() {
        $app = App::i();
        
        // Register the controller that will handle the auth redirects and API checks
        $app->registerController('wp-sso', 'WpForumSso\Controllers\Sso');
        
        // Register metadata to store the temporary token
        $this->registerUserMetadata('wp_sso_temp_token', [
            'label' => i::__('WP SSO Temp Token'),
            'type' => 'text',
            'private' => true
        ]);
        
        $this->registerUserMetadata('wp_sso_token_expires', [
            'label' => i::__('WP SSO Token Expires'),
            'type' => 'text',
            'private' => true
        ]);
    }
}

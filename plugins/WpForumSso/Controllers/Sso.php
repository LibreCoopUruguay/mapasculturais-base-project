<?php
namespace WpForumSso\Controllers;

use MapasCulturais\App;
use MapasCulturais\Controller;

class Sso extends Controller {

    public function __construct() {
        // No layout needed, API responses or redirects
        $this->layout = '';
    }

    /**
     * WP redirects here to start the flow.
     * URL: /wp-sso/login?redirect_to=https://foro.com&state=1234
     */
    public function GET_login() {
        $app = App::i();
        
        $requestRedirectTo = $app->request->get('redirect_to') ?: '';
        $requestState = $app->request->get('state') ?: '';
        
        if (!empty($requestRedirectTo)) {
            $_SESSION['wp_sso_redirect_to'] = $requestRedirectTo;
            $_SESSION['wp_sso_state'] = $requestState;
        }

        // 1. Force the user to be authenticated in Mapas Culturais
        $this->requireAuthentication();
        
        $user = $app->user;
        $redirectTo = $requestRedirectTo ?: ($_SESSION['wp_sso_redirect_to'] ?? '');
        $state = $requestState ?: ($_SESSION['wp_sso_state'] ?? '');
        
        if (empty($redirectTo)) {
            $app->halt(400, "Missing redirect_to parameter");
        }
        
        // Limpiamos la sesión una vez rescatados los valores
        unset($_SESSION['wp_sso_redirect_to']);
        unset($_SESSION['wp_sso_state']);
        
        // 2. Generate short-lived secure token (Valid for 60 seconds)
        $token = bin2hex(random_bytes(32));
        $user->setMetadata('wp_sso_temp_token', $token);
        $user->setMetadata('wp_sso_token_expires', time() + 60);
        $user->save(true);
        
        // 3. Redirect back to WordPress with the token and state
        $separator = (parse_url($redirectTo, PHP_URL_QUERY) == NULL) ? '?' : '&';
        $finalUrl = $redirectTo . $separator . "mc_token=" . $token . "&state=" . urlencode($state);
        
        $app->redirect($finalUrl);
    }

    /**
     * Backend-to-Backend API Check.
     * WordPress calls this securely to exchange the token for User Data.
     * URL: /wp-sso/verify
     * Method: POST
     * Body: token=XXX&secret=YOUR_SHARED_SECRET
     */
    public function POST_verify() {
        $app = App::i();
        
        // Needs to respond strictly in JSON
        header('Content-Type: application/json');
        
        $token = $this->postData['token'] ?? null;
        $secret = $this->postData['secret'] ?? null;
        
        // SECURITY: Hardcoded secret for WP Validation (Should ideally be in config.php)
        $expectedSecret = $app->config['wp_sso.secret'] ?? 'CHANGE_ME_IN_CONFIG';
        
        if ($secret !== $expectedSecret) {
            echo json_encode(['error' => true, 'message' => 'Invalid Shared Secret']);
            $app->halt(403);
        }
        
        if (!$token) {
            echo json_encode(['error' => true, 'message' => 'Missing token']);
            $app->halt(400);
        }
        
        // Find User by Token
        $app->disableAccessControl();
        $userMeta = $app->repo('UserMeta')->findOneBy(['key' => 'wp_sso_temp_token', 'value' => $token]);
        
        if (!$userMeta || !$userMeta->owner) {
            echo json_encode(['error' => true, 'message' => 'Invalid or expired token']);
            $app->enableAccessControl();
            $app->halt(401);
        }
        
        $user = $userMeta->owner;
        $expires = $user->getMetadata('wp_sso_token_expires');
        
        // Burn the token immediately (Single-Use)
        $user->setMetadata('wp_sso_temp_token', null);
        $user->setMetadata('wp_sso_token_expires', null);
        $user->save(true);
        $app->enableAccessControl();
        
        if (!$expires || $expires < time()) {
            echo json_encode(['error' => true, 'message' => 'Token has expired']);
            $app->halt(401);
        }
        
        // Success: Token is valid. Fetch Agent Data and Seals.
        $agent = $user->profile;
        if (!$agent) {
            echo json_encode(['error' => true, 'message' => 'User has no default Agent profile']);
            $app->halt(400);
        }
        
        // Extract Seal IDs
        $sealIds = [];
        $relations = $agent->getSealRelations();
        foreach ($relations as $rel) {
            $sealIds[] = $rel->seal->id;
        }
        
        // Standardize output
        $payload = [
            'error' => false,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'authProvider' => $user->authProvider
            ],
            'agent' => [
                'id' => $agent->id,
                'name' => $agent->name,
                'shortDescription' => $agent->shortDescription,
                'seals' => $sealIds // Very important array for WordPress to check
            ]
        ];
        
        echo json_encode($payload);
        $app->halt(200);
    }
}

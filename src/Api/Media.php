<?php

namespace PrBiggerUploads\Api;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once ABSPATH . 'wp-admin/includes/image.php';

class Media extends \WP_REST_Controller
{

    public static function register(): Media
    {
        return new self;
    }

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function isUserValid(\WP_REST_Request $request){
        $authCookie = $_COOKIE['wordpress_logged_in_' . COOKIEHASH];
        $validCookie = wp_validate_auth_cookie($authCookie, 'logged_in'); // Is auth cookie valid.
        if(!$validCookie){
            return new \WP_Error( 'rest_cookie_invalid', __( 'Invalid authentication cookie.' ), array( 'status' => 403 ) );
        }
        $user = wp_parse_auth_cookie($authCookie, 'logged_in');
        $userData = get_user_by('login', $user['username']);
        if($userData->ID != 0) {
            wp_set_current_user($userData->ID);
        }
        $body = json_decode($request->get_body(), 1);
        $result = isset($body['_wpnonce']) ? wp_verify_nonce($body['_wpnonce'], 'media-form') : false;
        if ( ! $result ) {
            return new \WP_Error( 'rest_cookie_invalid_nonce', __( 'Cookie nonce is invalid' ), array( 'status' => 403 ) );
        }
        return true;
    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() // phpcs:ignore
    {
        $namespace = 'uploads/v1';
        $base = 'media';

        register_rest_route($namespace, '/' . $base, [
            [
                'methods' => \WP_REST_Server::CREATABLE,
                'callback' => [$this, 'post'],
                'permission_callback' => [$this, 'isUserValid']
            ]
        ]);

    }

    public function post(\WP_REST_Request $request): \WP_REST_Response
    {
        $user = wp_get_current_user();
        $body = json_decode($request->get_body(), 1);
        header('Access-Control-Allow-Origin: *');
        header('content-type: text/plain; charset=UTF-8');
        $fileUrl          = $body['url'];
        $fileName         = basename( $fileUrl );
        $fileType         = wp_check_filetype( $fileName, null );
        $attachmentTitle = sanitize_file_name( pathinfo( $fileName, PATHINFO_FILENAME ) );

        $postInfo = array(
            'guid'           => $body['url'],
            'post_mime_type' => $fileType['type'],
            'post_title'     => $attachmentTitle,
            'post_author'    => $user->ID,
            'post_content'   => '',
            'post_status'    => 'inherit',
            'post_parent'    => (int) $body['post_id'],
            'meta_input' => [
                'isExternalAttachment' => true
            ]
        );

        // Create the attachment.
        $attachID = wp_insert_attachment( $postInfo, $fileUrl );

        // Assign metadata to attachment.
        wp_update_attachment_metadata( $attachID, [
            // 'width' => 3840,
            // 'height' => 2160,
            'file' => $fileName
        ] );

        if(isset($body['action']) && $body['action'] == 'upload-attachment'){
            $data = wp_prepare_attachment_for_js($attachID);
            return new \WP_REST_Response([
                "success" => true,
                "data" => $data
            ], 200);
        } else {
            echo $attachID;
            exit;
        }
    }
}

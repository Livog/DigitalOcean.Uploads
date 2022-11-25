<?php

namespace PrBiggerUploads\Wp;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once ABSPATH . 'wp-admin/includes/image.php';

class Filters
{

    public static function register(): Filters
    {
        return new self;
    }

    public function __construct()
    {
        $this->filterGetAttachmentUrlIfIsExternalAttachment();
    }

    private function createBaseWpUploadsUrl()
    {
        $homeUrl = trailingslashit(get_home_url());
        $uploads = wp_get_upload_dir(); // $uploads['path'] = "/app/web/wp-content/uploads/"
        $wpRoot = get_home_path(); // $wpRoot = "/app/web/"
        $uploadsBaseDir = trailingslashit(explode($wpRoot, $uploads['basedir'])[1]);
        return $homeUrl . $uploadsBaseDir;
    }

    public function filterGetAttachmentUrlIfIsExternalAttachment()
    {
        add_filter('wp_get_attachment_url', function($url, $postID){
            preg_match_all('/http/', $url, $httpMatchesInUrl);
            // First time the URL hits this filter it has 2 http's, this ensures we only filter it once.
            if(!get_post_meta($postID, 'isExternalAttachment', true) || count($httpMatchesInUrl[0]) == 1){
                return $url;
            }
            $this->createBaseWpUploadsUrl();
            return explode($this->createBaseWpUploadsUrl(), $url)[1];
        }, 999, 2);
    }
}

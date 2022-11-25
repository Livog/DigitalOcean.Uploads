<?php

namespace PrBiggerUploads\Wp;

// If this file is called directly, abort.
use Aws\S3\S3Client;
use PrBiggerUploads\Acf\SettingsPage;

if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once ABSPATH . 'wp-admin/includes/image.php';

class Uploader
{
    private $s3Client;

    public static function register(): Uploader
    {
        return new self;
    }

    public function __construct(){
        add_action('acf/init', function(){
            $location = get_field(SettingsPage::FIELD_DIGITAL_OCEAN_SPACES_LOCATION, 'option');
            $key = get_field(SettingsPage::FIELD_DIGITAL_OCEAN_SPACES_KEY, 'option');
            $secret = get_field(SettingsPage::FIELD_DIGITAL_OCEAN_SPACES_SECRET, 'option');
            $this->s3Client = new S3Client([
                'version' => 'latest',
                'endpoint' => 'https://' . $location . '.digitaloceanspaces.com',
                'region' => $location,
                'credentials' => [
                    'key' => $key,
                    'secret' => $secret
                ]
            ]);
        });
        add_action('delete_attachment', [$this, 'onDeleteAttachment'], 10, 2);
        add_action('add_attachment', [$this, 'onUploadAttachment'], 10, 1);
    }

    private function getAttachmentFilename($attachmentID){
        $filePath = \get_attached_file($attachmentID);
        return  basename($filePath);
    }

    public function onUploadAttachment($attachmentID){
        $filePath = \get_attached_file($attachmentID);
        $fileSize = filesize($filePath);
        $mimeType = wp_check_filetype($filePath)['type'];
        $filename = basename($filePath);
        $putParams = [
            'ContentLength'	    => $fileSize,
            'ContentType'		=> $mimeType,
            'Bucket'      		=> 'lsr',
            'Key'        		=> $filename, // this is the save as file in the space
            'Body'        		=> fopen($filePath,'rb'), // and this is the file name on this server
            'ACL'        		=> 'public-read'
        ];
        $result = $this->s3Client->putObject($putParams);
        $url = $result->get('ObjectURL');

        update_post_meta( $attachmentID, '_wp_attached_file', $url );
        update_post_meta( $attachmentID, 'isExternalAttachment', true );

        var_dump($result->toArray());
        die;
    }

    public function onDeleteAttachment($postID, $post)
    {
        if(get_post_meta($postID, 'isExternalAttachment', true) == false) return; // Exit Early
        $container = @get_field(SettingsPage::FIELD_DIGITAL_OCEAN_SPACES_CONTAINER, 'option');
        $key = $this->getAttachmentFilename($postID);
        $result = $this->s3Client->deleteObject([
            'Bucket' => $container,
            'Key'    => $key
        ]);
    }
}

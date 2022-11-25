<?php

namespace PrBiggerUploads\Services;

use Aws\S3\S3Client;

class AWSClient {
    private $s3Client;
    public function __construct()
    {
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'endpoint' => 'https://ams3.digitaloceanspaces.com',
            'region' => 'ams3',
            'credentials' => [
                'accessKeyId' => 'DO007RARLKFGVUCBVBVN',
                'secretAccessKey' => 'VWXl99W2QkgzR6KHQksXPzAtTueJJwtgs66UiTHBh8Y'
            ]
        ]);
    }
    public  function getAWSClient()
    {
        return $this->s3Client;
    }
}
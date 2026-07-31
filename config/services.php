<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Cloud Storage — chuẩn đa dự án
    |--------------------------------------------------------------------------
    | Hằng số .env (bắt buộc khi MEDIA_DISK=gcs):
    |   GCS_PROJECT_ID, GCS_BUCKET, GCS_KEY_FILE, GCS_PUBLIC_URL
    | Key file: đường dẫn relative base_path hoặc absolute.
    | Fallback GOOGLE_CLOUD_* chỉ migrate dự án cũ.
    */
    'gcs' => [
        'project_id' => env('GCS_PROJECT_ID', env('GOOGLE_CLOUD_PROJECT_ID')),
        'bucket' => env('GCS_BUCKET', env('GOOGLE_CLOUD_STORAGE_BUCKET')),
        'key_file' => ($path = env('GCS_KEY_FILE', env('GOOGLE_CLOUD_KEY_FILE')))
            ? (str_starts_with($path, '/') || preg_match('#^[A-Za-z]:[\\\\/]#', $path) ? $path : base_path($path))
            : null,
        'public_url' => env('GCS_PUBLIC_URL', env('GCS_PUBLIC_BASE_URL', env('GOOGLE_CLOUD_URL'))),
        'path_prefix' => env('GCS_PATH_PREFIX', env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX', '')),
    ],

];

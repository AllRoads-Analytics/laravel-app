<?php return [
    'project' => env('BIGQUERY_PROJECT'),
    'dataset' => 'pixel_events',
    'table' => 'events',

    'key' => [
        "type" => "service_account",
        "project_id" => env('BIGQUERY_PROJECT'),
        "private_key_id" => env('BIGQUERY_PK_ID'),
        "private_key" => env('BIGQUERY_PK'),
        "client_email" => "laravel-app@" . env('BIGQUERY_PROJECT') . ".iam.gserviceaccount.com",
        "client_id" => "118351035812842648707",
        "auth_uri" => "https://accounts.google.com/o/oauth2/auth",
        "token_uri" => "https://oauth2.googleapis.com/token",
        "auth_provider_x509_cert_url" => "https://www.googleapis.com/oauth2/v1/certs",
        "client_x509_cert_url" => "https://www.googleapis.com/robot/v1/metadata/x509/laravel-app%40" . env('BIGQUERY_PROJECT') . ".iam.gserviceaccount.com"
    ]
];

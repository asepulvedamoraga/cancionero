<?php

return [
    'name' => env('APP_NAME', 'Cancionero'),
    'admin' => [
        'name' => env('ADMIN_NAME', 'Administrador'),
        'email' => env('ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('ADMIN_PASSWORD'),
    ],
    'upload_max_mb' => (int) env('SONG_UPLOAD_MAX_MB', 20),
    'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'],
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp', 'pdf'],
    'image_quality' => (int) env('SONG_IMAGE_QUALITY', 85),
    'pdf_conversion_enabled' => (bool) env('SONG_PDF_CONVERSION_ENABLED', false),
    'pdf_resolution' => (int) env('SONG_PDF_RESOLUTION', 150),
    'generate_thumbnails' => (bool) env('SONG_GENERATE_THUMBNAILS', true),
    'export' => [
        'include_cover' => (bool) env('REPERTOIRE_EXPORT_COVER', true),
        'include_index' => (bool) env('REPERTOIRE_EXPORT_INDEX', false),
        'temporary_file_hours' => (int) env('REPERTOIRE_EXPORT_TTL_HOURS', 24),
    ],
];

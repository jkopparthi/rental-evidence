<?php
// Copy this file to config/config.php and fill in your own values.
// config.php is ignored by git so your password never gets committed.

return [
    'db' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'name'     => 'rental_evidence',
        'user'     => 'root',
        'password' => '',
    ],
    'app' => [
        // Private upload folder, kept outside /public so photos can't be hot-linked.
        'upload_dir'    => __DIR__ . '/../storage/uploads',
        'max_upload_mb' => 8,
    ],
];

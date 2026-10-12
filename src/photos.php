<?php
declare(strict_types=1);

const ALLOWED_MIME = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

function upload_dir(): string
{
    global $config;
    $dir = rtrim($config['app']['upload_dir'], '/');
    if (!is_dir($dir) && !mkdir($dir, 0750, true)) {
        throw new RuntimeException('Upload folder could not be created.');
    }
    return $dir;
}

/**
 * Re-shapes $_FILES['photos'] (multiple upload) into a simple list.
 */
function normalize_files(array $files): array
{
    $list = [];
    foreach ((array)$files['name'] as $i => $name) {
        $list[] = [
            'name'     => $name,
            'tmp_name' => $files['tmp_name'][$i],
            'error'    => $files['error'][$i],
            'size'     => $files['size'][$i],
        ];
    }
    return $list;
}

/**
 * Validates and stores one uploaded photo. Returns an error message or null on success.
 */
function store_photo(array $file, int $propertyId, string $room, string $stage, string $description): ?string
{
    global $config;
    $maxBytes = $config['app']['max_upload_mb'] * 1024 * 1024;
    $label = $file['name'] !== '' ? $file['name'] : 'A file';

    if ($file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE || $file['size'] > $maxBytes) {
        return "$label is larger than {$config['app']['max_upload_mb']} MB.";
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return "$label didn't upload. Try again.";
    }

    // Check the real file contents, not the extension the browser claims.
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset(ALLOWED_MIME[$mime]) || @getimagesize($file['tmp_name']) === false) {
        return "$label isn't a JPG, PNG or WebP image.";
    }

    $storedName = bin2hex(random_bytes(16)) . '.' . ALLOWED_MIME[$mime];
    $target = upload_dir() . '/' . $storedName;
    $hash = hash_file('sha256', $file['tmp_name']);
    $takenAt = exif_taken_at($file['tmp_name'], $mime);

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return "$label couldn't be saved on the server.";
    }

    $stmt = db()->prepare(
        'INSERT INTO photos (property_id, room, stage, description, stored_name, original_name,
                             mime_type, size_bytes, sha256, taken_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $propertyId, $room, $stage, nullable($description), $storedName,
        mb_substr(basename($file['name']), 0, 255), $mime, $file['size'], $hash, $takenAt,
    ]);
    return null;
}

/** Reads the original capture date from a JPEG's EXIF data, if the extension is installed. */
function exif_taken_at(string $path, string $mime): ?string
{
    if ($mime !== 'image/jpeg' || !function_exists('exif_read_data')) return null;
    $exif = @exif_read_data($path);
    $raw = $exif['DateTimeOriginal'] ?? null;
    if (!$raw) return null;
    $d = DateTime::createFromFormat('Y:m:d H:i:s', $raw);
    return $d ? $d->format('Y-m-d H:i:s') : null;
}

function delete_photo_file(array $photo): void
{
    $path = upload_dir() . '/' . basename($photo['stored_name']);
    if (is_file($path)) unlink($path);
}

/** Photos for one property grouped as [room => ['before' => [...], 'after' => [...]]]. */
function photos_by_room(int $propertyId): array
{
    $stmt = db()->prepare(
        'SELECT * FROM photos WHERE property_id = ? ORDER BY room, stage, uploaded_at'
    );
    $stmt->execute([$propertyId]);

    $grouped = [];
    foreach ($stmt->fetchAll() as $photo) {
        $grouped[$photo['room']] ??= ['before' => [], 'after' => []];
        $grouped[$photo['room']][$photo['stage']][] = $photo;
    }
    // Keep rooms in the natural walk-through order defined in ROOMS.
    uksort($grouped, function ($a, $b) {
        $ia = array_search($a, ROOMS, true);
        $ib = array_search($b, ROOMS, true);
        return ($ia === false ? 999 : $ia) <=> ($ib === false ? 999 : $ib);
    });
    return $grouped;
}

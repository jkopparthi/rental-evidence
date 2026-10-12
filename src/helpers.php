<?php
declare(strict_types=1);

/** The app's display name. Lives in code so a stale config.php can never override it. */
const APP_NAME = 'Rental Evidence';

/** Escape output for HTML. Use on every piece of user data you print. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

/** One-time messages shown on the next page load. */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function post(string $key): string
{
    return trim((string)($_POST[$key] ?? ''));
}

function int_param(string $key): int
{
    return (int)($_GET[$key] ?? $_POST[$key] ?? 0);
}

/** Converts '' to null so optional date fields store NULL. */
function nullable(string $value): ?string
{
    return $value === '' ? null : $value;
}

function valid_date(string $value): bool
{
    if ($value === '') return true;
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return $d !== false && $d->format('Y-m-d') === $value;
}

function format_date(?string $value, string $format = 'M j, Y'): string
{
    return $value ? date($format, strtotime($value)) : '—';
}

function format_bytes(int $bytes): string
{
    return $bytes >= 1048576
        ? round($bytes / 1048576, 1) . ' MB'
        : round($bytes / 1024) . ' KB';
}

const ROOMS = [
    'Entrance', 'Living room', 'Kitchen', 'Dining area', 'Bedroom 1', 'Bedroom 2',
    'Bedroom 3', 'Bathroom', 'Second bathroom', 'Laundry', 'Hallway', 'Balcony',
    'Closets', 'Appliances', 'Windows & blinds', 'Exterior', 'Parking', 'Other',
];

<?php
// core/helpers.php - دوال مساعدة
declare(strict_types=1);

// التحويل وإعادة التوجيه
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

// تنظيف النص لإخراجه في HTML
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// تنظيف المدخلات
function sanitize(mixed $value): string
{
    return trim(strip_tags((string)$value));
}

// رسائل Flash
function flashSuccess(string $message): void
{
    $_SESSION['flash'] = ['type' => 'success', 'message' => $message];
}

function flashError(string $message): void
{
    $_SESSION['flash'] = ['type' => 'danger', 'message' => $message];
}

// تنسيق التاريخ
function formatDate(string $date): string
{
    if (empty($date)) return '';
    return date('d M Y', strtotime($date));
}

function formatTime(string $time): string
{
    if (empty($time)) return '';
    return date('h:i A', strtotime($time));
}
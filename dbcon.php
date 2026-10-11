<?php
declare(strict_types=1);

session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(
        getenv('DB_HOST') ?: 'localhost',
        getenv('DB_USER') ?: 'root',
        getenv('DB_PASSWORD') ?: '',
        getenv('DB_NAME') ?: 'php_db',
        (int) (getenv('DB_PORT') ?: 3306)
    );
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    error_log($exception->getMessage());
    http_response_code(503);
    exit('Database connection unavailable. Start MySQL in XAMPP and import database.sql into phpMyAdmin, then reload this page.');
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}                                                                                                                                   

function csrf_token(): string
{
    return $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
}

function valid_token(mixed $token): bool
{
    return is_string($token) && hash_equals(csrf_token(), $token);
}

function validate_user(string $name, string $email): array
{
    $errors = [];
    if ($name === '' || mb_strlen($name) > 100) {
        $errors[] = 'Enter a full name between 1 and 100 characters.';
    }
    if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address (up to 254 characters).';
    }
    return $errors;
}

function posted_text(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

function redirect_to(string $message): never
{
    header('Location: index.php?msg=' . urlencode($message));
    exit;
}

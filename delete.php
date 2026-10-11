<?php
require_once __DIR__ . '/dbcon.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if (!$id) {
    header("Location: index.php?msg=invalid");
    exit();
}

try {
    $statement = $conn->prepare("DELETE FROM users WHERE id = ?");
    $statement->bind_param("i", $id);
    $statement->execute();

    if ($statement->affected_rows > 0) {
        $conn->query("ALTER TABLE users AUTO_INCREMENT = 1");
        header("Location: index.php?msg=deleted");
    } else {
        header("Location: index.php?msg=missing");
    }
    exit();
} catch (mysqli_sql_exception $exception) {
    error_log($exception->getMessage());
    header("Location: index.php?msg=error");
    exit();
}
?>
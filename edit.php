<?php
require_once __DIR__ . '/dbcon.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if (!$id) {
    header("Location: index.php?msg=invalid");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!empty($name) && !empty($email)) {
        try {
            $statement = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $statement->bind_param("ssi", $name, $email, $id);
            if ($statement->execute()) {
                header("Location: index.php?msg=updated");
            } else {
                header("Location: index.php?msg=error");
            }
            exit();
        } catch (mysqli_sql_exception $exception) {
            error_log($exception->getMessage());
            header("Location: index.php?msg=error");
            exit();
        }
    }
}

header("Location: index.php?edit_id=" . $id);
exit();
?>
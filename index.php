<?php
require_once __DIR__ . '/dbcon.php';

$name = '';
$email = '';
$error_msg = '';
$edit_mode = false;
$edit_id = 0;

if (isset($_GET['edit_id'])) {
    $edit_id = filter_input(INPUT_GET, 'edit_id', FILTER_VALIDATE_INT);
    if ($edit_id) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $edit_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $user_data = $res->fetch_assoc();
            $name = $user_data['name'];
            $email = $user_data['email'];
            $edit_mode = true;
        }
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$edit_mode) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!empty($name) && !empty($email)) {
        $stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $email);
        if ($stmt->execute()) {
            header("Location: index.php?msg=added");
            exit();
        } else {
            $error_msg = "Error adding user.";
        }
        $stmt->close();
    } else {
        $error_msg = "Please fill in all fields.";
    }
}

$users = $conn->query("SELECT * FROM users ORDER BY id ASC");

$messages = [
    'added'   => ['success', 'User created successfully!'],
    'updated' => ['success', 'User edited successfully!'],
    'deleted' => ['danger',  'User deleted successfully!'],
];

$msgKey = $_GET['msg'] ?? '';
$notification = $messages[$msgKey] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-4">
    <h3 class="text-center mb-4">User Management System</h3>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3"><?= $edit_mode ? 'Edit User' : 'Add New User' ?></h5>

                    <?php if ($notification): ?>
                        <div class="alert alert-<?= $notification[0] ?> alert-dismissible fade show" role="alert">
                            <?= $notification[1] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <script>
                            alert("<?= $notification[1] ?>");
                        </script>
                    <?php endif; ?>

                    <?php if ($error_msg): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($error_msg) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= $edit_mode ? 'edit.php?id=' . $edit_id : 'index.php' ?>">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Full Name</label>
                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="Enter Full Name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Email Address</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Enter Email Address" required>
                        </div>

                        <?php if ($edit_mode): ?>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">Submit</button>
                                <a href="index.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        <?php else: ?>
                            <button type="submit" class="btn btn-primary w-100">Submit</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3">Registered Users</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$users || $users->num_rows === 0): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No data found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    $i = 1;
                                    while ($user = $users->fetch_assoc()): 
                                    ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= htmlspecialchars($user['name']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td><?= htmlspecialchars($user['created_at']) ?></td>
                                            <td>
                                                <a href="index.php?edit_id=<?= (int) $user['id'] ?>" class="btn btn-sm btn-success">Edit</a>
                                                <a href="delete.php?id=<?= (int) $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
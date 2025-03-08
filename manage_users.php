<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $usertype = $_POST['usertype'];
    $password = password_hash('defaultpassword', PASSWORD_DEFAULT); // Set a default password (change as needed)

    $stmt = $pdo->prepare("INSERT INTO users (username, email, usertype, password) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $email, $usertype, $password]);
    header("Location: manage_users.php");
    exit();
}

// Handle Delete User
if (isset($_GET['delete']) && is_numeric($_GET['delete']) && $_GET['delete'] != $user_id) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: manage_users.php");
    exit();
}

// Handle Edit User (Form Submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $usertype = $_POST['usertype'];

    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, usertype = ? WHERE id = ?");
    $stmt->execute([$username, $email, $usertype, $id]);
    header("Location: manage_users.php");
    exit();
}

// Fetch Users
$stmt = $pdo->query("SELECT id, username, email, usertype FROM users");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Smart Health Tracker</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f5f7fa; padding: 20px; }
        .table { background: white; border-radius: 10px; overflow: hidden; }
        .table thead { background: #007bff; color: white; }
        .form-container { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage Users</h1>

        <!-- Add User Form -->
        <div class="form-container">
            <h3>Add New User</h3>
            <form method="POST">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <input type="text" name="username" class="form-control" placeholder="Username" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <select name="usertype" class="form-control" required>
                            <option value="patient">Patient</option>
                            <option value="doctor">Doctor</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="add_user" class="btn btn-success">Add User</button>
            </form>
        </div>

        <!-- User Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['usertype']); ?></td>
                            <td>
                                <!-- Edit Button (Triggers Modal) -->
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $user['id']; ?>">Edit</button>
                                <!-- Delete Button -->
                                <?php if ($user['id'] != $user_id): ?>
                                    <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $user['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel<?php echo $user['id']; ?>">Edit User</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST">
                                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                            <div class="mb-3">
                                                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <select name="usertype" class="form-control" required>
                                                    <option value="patient" <?php echo $user['usertype'] == 'patient' ? 'selected' : ''; ?>>Patient</option>
                                                    <option value="doctor" <?php echo $user['usertype'] == 'doctor' ? 'selected' : ''; ?>>Doctor</option>
                                                    <option value="admin" <?php echo $user['usertype'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                </select>
                                            </div>
                                            <button type="submit" name="edit_user" class="btn btn-primary">Save Changes</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <a href="admin_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
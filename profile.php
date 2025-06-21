<?php
session_start();
if (isset($_SESSION["is"]) && !$_SESSION["is"]) {
    header("Location: login.php");
}
include 'db.php';
// Get user role
$user = $_SESSION["username"];
$stmt = $pdo->prepare("SELECT CAST(role AS UNSIGNED) AS role FROM user WHERE username = ?");
$stmt->execute([$user]);
$roleRow = $stmt->fetch();
$role = $roleRow['role'];
// Add new user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'], $_POST['password'], $_POST['role'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = (int) $_POST['role'];

    try {
        $stmt = $pdo->prepare("INSERT INTO user (username, password, role) VALUES (:username, :password, :role)");
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
        $stmt->bindValue(':role', (int) $role, PDO::PARAM_INT);
        $stmt->execute();
        // Redirect back
        header("Location: profile.php");
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "A user with that username already exists!";
        } else {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background-color: #9ACBD0;">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light shadow-sm" style="background-color: #F2EFE7">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="home.php" style="color: #006A71; font-size: 2rem;">TeachAssistant</a>
            <button class="navbar-toggler bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="text-dark nav-link" href="home.php">Home</a></li>
                    <li class="nav-item"><a class="text-dark nav-link" href="classes.php">Classes</a></li>
                    <li class="nav-item"><a class="text-dark nav-link disabled" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="text-dark nav-link" href="login.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <div class="card mx-auto shadow" style="max-width: 500px;">
            <div class="card-body text-center">
                <img src="https://upload.wikimedia.org/wikipedia/commons/7/7c/Profile_avatar_placeholder_large.png?20150327203541" class="rounded-circle mb-3 w-50" alt="Avatar">
                <h2 class="card-title"><?= htmlspecialchars($user) ?></h2>
                <p class="text-muted">Role: <?= $role == 0 ? 'Teacher' : 'Others' ?></p>
                <div class="text-center mt-4">
                    <a href="login.php" class="btn btn-outline-secondary mt-3">Logout</a>
                    <?php if ($_SESSION["role"] == 0) { ?>
                        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#newUserModal">New User</button>
                    <?php } ?>
                </div>

                <?php if ($_SESSION["role"] == 0) { ?>
                    <!-- Add user modal -->
                    <div class="modal fade text-start" id="newUserModal" tabindex="-1" aria-labelledby="newUserModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form method="POST" action="profile.php" class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="newUserModalLabel">Add New User</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="username" maxlength="20" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" name="password" class="form-control" required minlength="6">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <select name="role" class="form-select" required>
                                            <option value=0>Teacher</option>
                                            <option value=1>Others</option>
                                        </select>
                                    </div>
                                    <?php if (!empty($error)): ?>
                                        <div class="alert alert-danger mt-3 text-start"><?= htmlspecialchars($error) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success">Add User</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
<?php
session_start();
if (!isset($_SESSION["is"]) || !$_SESSION["is"]) {
    header("Location: login.php");
}
include 'db.php';
// Add new class
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['name']) && isset($_POST['code']) && isset($_POST['semester'])) {
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);
    $semester = trim($_POST['semester']);

    try {
        $stmt = $pdo->prepare("INSERT INTO class (name, code, semester) VALUES (?, ?, ?)");
        $stmt->execute([$name, $code, $semester]);

        // Redirect to avoid resubmission
        header("Location: classes.php");
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "A class with that code already exists!";
        } else {
            $error = "Error: " . $e->getMessage();
        }
    }
}
// Delete classes
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_ids'])) {
    $deleteIds = $_POST['delete_ids'];
    $placeholders = implode(',', array_fill(0, count($deleteIds), '?'));
    $stmt = $pdo->prepare("DELETE FROM class WHERE id IN ($placeholders)");
    $stmt->execute($deleteIds);
    // Redirect back
    header("Location: classes.php");
    exit;
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classes</title>
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
                    <li class="nav-item"><a class="text-dark nav-link disabled" href="classes.php">Classes</a></li>
                    <li class="nav-item"><a class="text-dark nav-link" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="text-dark nav-link" href="login.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="container mt-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <h2>Your Classes</h2>
            <?php if ($_SESSION["role"] == 0) { ?>
                <div class="d-flex align-items-center">
                    <!-- Add new class -->
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newClassModal">+ New Class</button>
                    <!-- Form -->
                    <div class="modal fade" id="newClassModal" tabindex="-1" aria-labelledby="newClassModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="classes.php" method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="newClassModalLabel">Add New Class</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Name -->
                                        <div class="mb-3">
                                            <label for="className" class="form-label">Class Name</label>
                                            <input type="text" class="form-control" name="name" id="className" maxlength="100" required>
                                        </div>
                                        <!-- Code -->
                                        <div class="mb-3">
                                            <label for="classCode" class="form-label">Class Code</label>
                                            <input type="text" class="form-control" name="code" id="classCode" maxlength="20" required>
                                        </div>
                                        <!-- Semester -->
                                        <div class="mb-3">
                                            <label for="semester" class="form-label">Semester</label>
                                            <input type="text" class="form-control" name="semester" id="semester" maxlength="20" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success">Create</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Delete buttons -->
                    <form method="POST" action="classes.php" id="deleteForm" class="d-flex align-items-center">
                        <button id="toggleDeleteBtn" type="button" class="btn btn-danger ms-3">- Delete</button>
                        <div id="deleteControls" class="d-none ms-3">
                            <button type="submit" class="btn btn-danger">Confirm Deletion</button>
                            <button type="button" class="btn btn-secondary ms-2" id="cancelDelete">Cancel</button>
                        </div>
                    </form>
                </div>
            <?php } ?>
            <!-- Condition to open add class form -->
            <?php if (isset($_GET['showModal']) && $_GET['showModal'] == '1'): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const newClassModal = new bootstrap.Modal(document.getElementById('newClassModal'));
                    newClassModal.show();
                });
            </script>
            <?php endif; ?>
        </div>
        <!-- Display error if add class unsuccessfully -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <!-- Classes -->
        <form method="POST" action="classes.php" id="deleteFormHidden">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
                <?php
                    $result = $pdo->query("SELECT * FROM class ORDER BY semester");
                ?>
                <?php foreach ($result as $row): ?>
                    <div class='col mb-2'>
                        <div class='card shadow-sm position-relative'>
                            <div class='card-body'>
                                <!-- Choose classes to delete -->
                                <div class="form-check delete-checkbox d-none">
                                    <input class="form-check-input" type="checkbox" name="delete_ids[]" value="<?= $row['id'] ?>" form="deleteForm">
                                </div>
                                <h5 class='card-title'><?= $row['name'] ?></h5>
                                <p class='card-text text-muted'>Code: <?= $row['code'] ?> | Semester: <?= $row['semester'] ?></p>
                                <a href='lectures.php?classId=<?= $row['id'] ?>' class='btn btn-outline-primary btn-sm'>Lectures</a>
                                <a href='materials.php?classId=<?= $row['id'] ?>' class='btn btn-outline-primary btn-sm'>Materials</a>
                                <a href='assignments.php?classId=<?= $row['id'] ?>' class='btn btn-outline-primary btn-sm'>Assignments</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </form>
        <!-- Script to display checkboxes -->
        <script>
            const toggleBtn = document.getElementById("toggleDeleteBtn");
            const checkboxes = document.querySelectorAll(".delete-checkbox");
            const deleteControls = document.getElementById("deleteControls");
            const cancelBtn = document.getElementById("cancelDelete");

            toggleBtn.addEventListener("click", () => {
                checkboxes.forEach(cb => cb.classList.remove("d-none"));
                deleteControls.classList.remove("d-none");
                toggleBtn.classList.add("d-none");
            });

            cancelBtn.addEventListener("click", () => {
                checkboxes.forEach(cb => {
                    cb.classList.add("d-none");
                    cb.querySelector("input").checked = false;
                });
                deleteControls.classList.add("d-none");
                toggleBtn.classList.remove("d-none");
            });
        </script>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
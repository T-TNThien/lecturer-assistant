<?php
session_start();
if (!isset($_SESSION["is"]) || !$_SESSION["is"]) {
    header("Location: login.php");
}
include 'db.php';
// Get class ID
$classId = isset($_GET['classId']) ? (int)$_GET['classId'] : 0;

// Fetch class information from the database
$stmt = $pdo->prepare("SELECT * FROM class WHERE id = ?");
$stmt->execute([$classId]);
$class = $stmt->fetch();

// If the class does not exist
if (!$class) {
    die('Class not found');
}

// Fetch lectures for the class
$lectures = $pdo->prepare("SELECT * FROM lecture WHERE classId = ? ORDER BY date");
$lectures->execute([$classId]);
$lecturesList = $lectures->fetchAll();

// Add new lecture
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['date'])) {
    $title = $_POST['title'];
    $description = $_POST['description'] ?? null;
    $date = $_POST['date'];

    $insert = $pdo->prepare("INSERT INTO lecture (classId, title, description, date) VALUES (?, ?, ?, ?)");
    $insert->execute([$classId, $title, $description, $date]);

    header("Location: lectures.php?classId=" . $classId);
    exit();
}

// Delete lectures
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_lecture_ids'])) {
    $deleteIds = $_POST['delete_lecture_ids'];
    $placeholders = implode(',', array_fill(0, count($deleteIds), '?'));
    $stmt = $pdo->prepare("DELETE FROM lecture WHERE id IN ($placeholders)");
    $stmt->execute($deleteIds);
    // Redirect back
    $classId = isset($_GET['classId']) ? intval($_GET['classId']) : '';
    header("Location: lectures.php" . ($classId ? "?classId=$classId" : ""));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lectures for <?= $class['name'] ?></title>
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
                    <li class="nav-item"><a class="text-dark nav-link" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="text-dark nav-link" href="login.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <h2>Lectures For <?= $class['name'] ?></h2>
            <?php if ($_SESSION["role"] == 0) { ?>
                <div class="d-flex align-items-center">
                    <!-- Add new lecture -->
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newLectureModal">+ New Lecture</button>
                    <!-- Form -->
                    <div class="modal fade" id="newLectureModal" tabindex="-1">
                        <div class="modal-dialog">
                            <form class="modal-content" action="lectures.php?classId=<?= $classId ?>" method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="newLectureModalLabel">Add New Lecture</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Lecture Title -->
                                    <div class="mb-3">
                                        <label for="lectureTitle" class="form-label">Lecture Title</label>
                                        <input type="text" class="form-control" name="title" id="lectureTitle" maxlength="100" required>
                                    </div>
                                    <!-- Lecture Description -->
                                    <div class="mb-3">
                                        <label for="lectureDescription" class="form-label">Description</label>
                                        <textarea class="form-control" name="description" id="lectureDescription" rows="3" maxlength="1000"></textarea>
                                    </div>
                                    <!-- Lecture Date -->
                                    <div class="mb-3">
                                        <label for="lectureDate" class="form-label">Date</label>
                                        <input type="datetime-local" class="form-control" name="date" id="lectureDate" required min="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success">Create</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- Delete buttons -->
                    <form method="POST" action="lectures.php?classId=<?= $classId ?>" id="deleteForm" class="d-flex align-items-center">
                        <button id="toggleDeleteBtn" type="button" class="btn btn-danger ms-3">- Delete</button>
                        <div id="deleteControls" class="d-none ms-3">
                            <button type="submit" class="btn btn-danger">Confirm Deletion</button>
                            <button type="button" class="btn btn-secondary ms-2" id="cancelDelete">Cancel</button>
                        </div>
                    </form>
                </div>
                <!-- Display error if add class unsuccessfully -->
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
            <?php } ?>
        </div>

            
        <!-- Lectures -->
        <a href="classes.php" class="btn btn-outline-secondary mb-4">← Back to Classes</a>
        <form id="deleteLecturesForm" method="POST" action="lectures.php?classId=<?= $classId ?>">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
                <?php if (count($lecturesList) > 0): ?>
                    <?php foreach ($lecturesList as $lecture): ?>
                        <div class="col mb-2">
                            <div class="card shadow-sm position-relative">
                                <div class="card-body">
                                    <!-- Checkbox -->
                                    <div class="form-check delete-checkbox d-none">
                                        <input class="form-check-input" type="checkbox" name="delete_lecture_ids[]" value="<?= $lecture['id'] ?>" form="deleteForm">
                                    </div>
                                    <!-- Content -->
                                    <h5 class="card-title"><?= $lecture['title'] ?></h5>
                                    <p class="card-text text-muted"><?= $lecture['date'] ?></p>
                                    <p class="card-text"><?= $lecture['description'] ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <h3>No lectures available for this class.</h3>
                <?php endif; ?>
            </div>
        </form>
        <script>
            // Script to display checkboxes
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
            // Set minimum date and time
            window.addEventListener('DOMContentLoaded', () => {
                const dateInput = document.getElementById("lectureDate");
                const now = new Date();
                now.setMinutes(now.getMinutes() - now.getTimezoneOffset()); // convert to local time
                const rounded = new Date(Math.ceil(now.getTime() / (5 * 60 * 1000)) * (5 * 60 * 1000)); // round to next 5 minutes
                dateInput.min = rounded.toISOString().slice(0, 16);
            });

        </script>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
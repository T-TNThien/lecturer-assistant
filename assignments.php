<?php
session_start();
if (isset($_SESSION["is"]) && !$_SESSION["is"]) {
    header("Location: login.php");
}
include 'db.php';
// Get class ID
$classId = isset($_GET['classId']) ? (int)$_GET['classId'] : 0;

// Fetch class information from the database
$stmt = $pdo->prepare("SELECT * FROM class WHERE id = ?");
$stmt->execute([$classId]);
$class = $stmt->fetch();
if (!$class) die('Class not found');

// Fetch assignments
$assignments = $pdo->prepare("SELECT * FROM assignment WHERE classId = ? ORDER BY dueTime");
$assignments->execute([$classId]);
$assignmentList = $assignments->fetchAll();

// Add assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['dueTime'])) {
    $title = $_POST['title'];
    $description = $_POST['description'] ?? '';
    $dueTime = $_POST['dueTime'];
    $filePath = null;

    if (!empty($_FILES['file']['name'])) {
        $uploadDir = 'uploads/';
        $filePath = $uploadDir . basename($_FILES['file']['name']);
        move_uploaded_file($_FILES['file']['tmp_name'], $filePath);
    }

    $insert = $pdo->prepare("INSERT INTO assignment (classId, title, description, dueTime, filePath) VALUES (?, ?, ?, ?, ?)");
    $insert->execute([$classId, $title, $description, $dueTime, $filePath]);

    header("Location: assignments.php?classId=" . $classId);
    exit();
}

// Delete assignments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_assignment_ids'])) {
    $ids = $_POST['delete_assignment_ids'];
    foreach ($ids as $id) {
        $delete = $pdo->prepare("DELETE FROM assignment WHERE id = ?");
        $delete->execute([$id]);
    }
    header("Location: assignments.php?classId=" . $classId);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Assignments For <?= $class['name'] ?></h2>
            <?php if ($_SESSION["role"] == 0) { ?>
                <div class="d-flex align-items-center">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newAssignmentModal">+ New Assignment</button>
                    <!-- Add Assignment Modal -->
                    <div class="modal fade" id="newAssignmentModal" tabindex="-1">
                        <div class="modal-dialog">
                            <form class="modal-content" method="POST" action="assignments.php?classId=<?= $classId ?>" enctype="multipart/form-data">
                                <div class="modal-header">
                                    <h5 class="modal-title">Add New Assignment</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Title</label>
                                        <input type="text" class="form-control" name="title" maxlength="100" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="description" rows="3" maxlength="1000"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Due Time</label>
                                        <input type="datetime-local" class="form-control" name="dueTime" id="lectureDate" required min="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Upload File (optional)</label>
                                        <input type="file" class="form-control" name="file">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success">Add Assignment</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <form id="deleteForm" method="POST" action="assignments.php?classId=<?= $classId ?>">
                        <button type="button" class="btn btn-danger ms-2" id="toggleDeleteBtn">- Delete</button>
                        <div id="deleteControls" class="d-none ms-3">
                            <button type="submit" class="btn btn-danger">Confirm Deletion</button>
                            <button type="button" class="btn btn-secondary ms-2" id="cancelDelete">Cancel</button>
                        </div>
                </div>
            <?php } ?>
        </div>

        <!-- Assignment Cards -->
        <a href="classes.php" class="btn btn-outline-secondary mb-4">← Back to Classes</a>
        <div class="row row-cols-1 g-4">
            <?php if (count($assignmentList) > 0): ?>
                <?php foreach ($assignmentList as $assignment): ?>
                    <div class="col">
                        <div class="form-check delete-checkbox d-none">
                            <input class="form-check-input" type="checkbox" name="delete_assignment_ids[]" value="<?= $assignment['id'] ?>">
                        </div>
                        <div class="card shadow-sm" data-bs-toggle="modal" data-bs-target="#assignmentModal<?= $assignment['id'] ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($assignment['title']) ?></h5>
                                <p class="card-text text-muted">Due: <?= htmlspecialchars($assignment['dueTime']) ?></p>
                                <p class="card-text text-truncate"><?= htmlspecialchars($assignment['description']) ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Modal -->
                    <div class="modal fade" id="assignmentModal<?= $assignment['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><?= htmlspecialchars($assignment['title']) ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($assignment['description'])) ?></p>
                                    <p><strong>Due Date:</strong> <?= htmlspecialchars($assignment['dueTime']) ?></p>
                                    <?php if (!empty($assignment['filePath'])): ?>
                                        <p><strong>File:</strong> <a href="<?= $assignment['filePath'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">View / Download</a></p>
                                    <?php endif; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <h3>No materials available for this class.</h3>
            <?php endif; ?>
        </div>
    </div>

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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
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

// Fetch materials for the class
$materials = $pdo->prepare("SELECT * FROM material WHERE classId = ? ORDER BY uploadedTime DESC");
$materials->execute([$classId]);
$materialsList = $materials->fetchAll();

// Add new material
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_FILES['file'])) {
    $title = $_POST['title'];
    $description = $_POST['description'] ?? null;
    $file = $_FILES['file'];

    // Handle file upload
    $uploadDir = 'uploads/';
    $uploadFile = $uploadDir . basename($file['name']);

    if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
        $insert = $pdo->prepare("INSERT INTO material (classId, title, description, filePath, uploadedTime) VALUES (?, ?, ?, ?, ?)");
        $insert->execute([$classId, $title, $description, $uploadFile, date('Y-m-d H:i:s')]);

        header("Location: materials.php?classId=" . $classId);
        exit();
    } else {
        $error = "File upload failed.";
    }
}

// Delete material
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_material_ids'])) {
    $deleteIds = $_POST['delete_material_ids'];
    $placeholders = implode(',', array_fill(0, count($deleteIds), '?'));
    $stmt = $pdo->prepare("DELETE FROM material WHERE id IN ($placeholders)");
    $stmt->execute($deleteIds);
    // Redirect back
    header("Location: materials.php?classId=" . $classId);
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .text-truncate-multiline {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            /* Number of lines you want to show */
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
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
            <h2>Materials For <?= $class['name'] ?></h2>
            <?php if ($_SESSION["role"] == 0) { ?>
                <div class="d-flex align-items-center">
                    <!-- Add new material -->
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newMaterialModal">+ New Material</button>
                    <!-- Modal -->
                    <div class="modal fade" id="newMaterialModal" tabindex="-1" aria-labelledby="newMaterialModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="materials.php?classId=<?= $classId ?>" method="POST" enctype="multipart/form-data">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="newMaterialModalLabel">Add New Material</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Material Title -->
                                        <div class="mb-3">
                                            <label for="materialTitle" class="form-label">Title</label>
                                            <input type="text" class="form-control" name="title" id="materialTitle" maxlength="100" required>
                                        </div>
                                        <!-- Material Description -->
                                        <div class="mb-3">
                                            <label for="materialDescription" class="form-label">Description</label>
                                            <textarea class="form-control" name="description" id="materialDescription" rows="3" maxlength="1000"></textarea>
                                        </div>
                                        <!-- Material File -->
                                        <div class="mb-3">
                                            <label for="materialFile" class="form-label">Upload File</label>
                                            <input type="file" class="form-control" name="file" id="materialFile" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success">Upload</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Delete buttons -->
                    <form method="POST" action="materials.php?classId=<?= $classId ?>" id="deleteForm" class="d-flex align-items-center">
                        <button id="toggleDeleteBtn" type="button" class="btn btn-danger ms-3">- Delete</button>
                        <div id="deleteControls" class="d-none ms-3">
                            <button type="submit" class="btn btn-danger">Confirm Deletion</button>
                            <button type="button" class="btn btn-secondary ms-2" id="cancelDelete">Cancel</button>
                        </div>
                    </form>
                </div>
                <!-- Display error if add material unsuccessfully -->
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
            <?php } ?>
        </div>
        
        <!-- Materials -->
        <a href="classes.php" class="btn btn-outline-secondary mb-4">← Back to Classes</a>
        <form id="deleteMaterialsForm" method="POST" action="materials.php?classId=<?= $classId ?>">
            <div class="row row-cols-1 g-4">
                <?php if (count($materialsList) > 0): ?>
                    <?php foreach ($materialsList as $material): ?>
                        <div class="col mb-2">
                            <!-- Checkbox -->
                            <div class="form-check delete-checkbox d-none">
                                <input class="form-check-input" type="checkbox" name="delete_material_ids[]" value="<?= $material['id'] ?>" form="deleteForm">
                            </div>
                            <div class="card shadow-sm position-relative material-card" data-bs-toggle="modal" data-bs-target="#materialModal<?= $material['id'] ?>">
                                <div class="card-body">
                                    <!-- Content -->
                                    <!-- Determine file type for icon -->
                                    <?php
                                        $extension = strtolower(pathinfo($material['filePath'], PATHINFO_EXTENSION));
                                        $icons = [
                                            'pdf' => 'fa-file-pdf',
                                            'doc' => 'fa-file-word',
                                            'docx' => 'fa-file-word',
                                            'ppt' => 'fa-file-powerpoint',
                                            'pptx' => 'fa-file-powerpoint',
                                            'xls' => 'fa-file-excel',
                                            'xlsx' => 'fa-file-excel',
                                            'jpg' => 'fa-file-image',
                                            'jpeg' => 'fa-file-image',
                                            'png' => 'fa-file-image',
                                            'gif' => 'fa-file-image',
                                            'zip' => 'fa-file-zipper',
                                            'rar' => 'fa-file-zipper',
                                            'txt' => 'fa-file-lines',
                                            'mp4' => 'fa-file-video',
                                            'mp3' => 'fa-file-audio'
                                        ];
                                        $icon = isset($icons[$extension]) ? $icons[$extension] : 'fa-file';
                                    ?>
                                    <div class="d-flex flex-wrap justify-content-between">
                                        
                                        <h5 class="card-title">
                                            <i class="fa-solid <?= $icon ?> me-2 text-primary"></i>
                                            <?= rtrim($material['title']) . (!empty($material['filePath']) ? '.' . $extension : '') ?>
                                        </h5>
                                        <p class="card-text text-muted"><?= htmlspecialchars($material['uploadedTime']) ?></p>
                                    </div>
                                    <p class="card-text text-truncate-multiline"><?= htmlspecialchars($material['description']) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Modal -->
                        <div class="modal fade" id="materialModal<?= $material['id'] ?>" tabindex="-1" aria-labelledby="materialModalLabel<?= $material['id'] ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="card-title">
                                            <i class="fa-solid <?= $icon ?> me-2 text-primary"></i>
                                            <?= rtrim($material['title']) . (!empty($material['filePath']) ? '.' . $extension : '') ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Description:</strong></p>
                                        <p><?= nl2br(htmlspecialchars($material['description'])) ?></p>

                                        <p><strong>Uploaded:</strong> <?= htmlspecialchars($material['uploadedTime']) ?></p>

                                        <?php if (!empty($material['filePath'])): ?>
                                            <p><strong>File:</strong>
                                                <a href="<?= htmlspecialchars($material['filePath']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">View / Download</a>
                                            </p>
                                        <?php else: ?>
                                            <p class="fst-italic">No file attached.</p>
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
        </form>
        <script>
            // Script to display checkboxes
            const toggleBtn = document.getElementById("toggleDeleteBtn");
            const checkboxes = document.querySelectorAll(".delete-checkbox");
            const deleteControls = document.getElementById("deleteControls");
            const cancelBtn = document.getElementById("cancelDelete");

            toggleBtn.addEventListener("click", () => {
                event.preventDefault(); 
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
<?php
session_start();
if (!isset($_SESSION["is"]) || !$_SESSION["is"]) {
    header("Location: login.php");
}
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
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
                    <li class="nav-item"><a class="text-dark nav-link disabled" href="home.php">Home</a></li>
                    <li class="nav-item"><a class="text-dark nav-link" href="classes.php">Classes</a></li>
                    <li class="nav-item"><a class="text-dark nav-link" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="text-dark nav-link" href="login.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <h1 class="mb-4">Welcome, <?= $_SESSION["username"] ?>!</h1>
        <div class="mb-5 d-flex flex-column flex-sm-row align-items-center align-items-sm-start">
            <?php if ($_SESSION["role"] == 0) { ?>
                <a href="classes.php?showModal=1" class="btn btn-primary mt-3">+ New Class</a>
            <?php } ?>
        </div>

        <!-- Dashboard Cards -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-5">
            <div class="col">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-muted">My Classes</h5>
                        <a class="display-6 text-primary" href="classes.php" style="text-decoration:none">
                            <?php
                                $result = $pdo->query("SELECT COUNT(*) FROM class");
                                $count = $result->fetchColumn();
                                echo $count;
                            ?>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Materials</h5>
                        <p class="display-6 text-warning m-0">
                            <?php
                                $result = $pdo->query("SELECT COUNT(*) FROM material");
                                $count = $result->fetchColumn();
                                echo $count;
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Upcoming Lectures</h5>
                        <?php $result = $pdo->query("SELECT * FROM lecture ORDER BY date ASC LIMIT 3"); ?>
                        <?php foreach ($result as $row): ?>
                            <a class='display-6 text-success' href='lectures.php?classId=<?= $row['classId'] ?>' style='text-decoration:none'>
                                <?= $row['title'] ?>
                            </a><br>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Upcoming Assignments</h5>
                        <?php $result = $pdo->query("SELECT * FROM assignment ORDER BY dueTime ASC LIMIT 3"); ?>
                        <?php foreach ($result as $row): ?>
                            <a class='display-6 text-danger' href='assignments.php?classId=<?= $row['classId'] ?>' style='text-decoration:none'>
                                <?= $row['title'] ?>
                            </a><br>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
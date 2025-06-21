<?php
  session_start();
  unset($_SESSION["is"]);
  require_once 'db.php';
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM user WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
      $_SESSION["username"] = $user['username'];
      $_SESSION["role"] = $user['role'];
      $_SESSION["is"] = true;
      header("Location: home.php");
    } else {
      $_SESSION["is"] = false;
    }
  }
?>
<!DOCTYPE html>
<html lang="en" class="w-100 h-100">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
      rel="stylesheet"
      href="https://use.fontawesome.com/releases/v5.3.1/css/all.css"
      integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU"
      crossorigin="anonymous"
    />
    <link
      rel="stylesheet"
      href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
    />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <title>Login</title>
  </head>
  <body class="w-100 h-100" style="background-color: #9ACBD0">
    <div class="d-flex align-items-center w-100 h-100">
      <div class="container bg-white shadow rounded">
        <div class="row justify-content-center">
          <div class="col-md-7 col-lg-5">
            <div class="login-wrap p-4 p-md-5">
              <div
                class="icon d-flex align-items-center justify-content-center"
              >
                <span class="fa fa-user-o"></span>
              </div>
              <h3 class="text-center mb-4">Sign In</h3>
              <form action="login.php" method="post" class="login-form">
                <div class="form-group">
                  <input
                    id="username"
                    name="username"
                    type="text"
                    class="form-control rounded-left"
                    placeholder="Username"
                    required=""
                  />
                </div>
                <div class="form-group d-flex">
                  <input
                    id="password"
                    name="password"
                    type="password"
                    class="form-control rounded-left"
                    placeholder="Password"
                    required=""
                  />
                </div>
                <div class="alert alert-danger" id="wrongInput" style="display: <?php echo isset($_SESSION["is"]) && !$_SESSION["is"] ? "block" : "none" ?>;">Username or password is incorrect.</div>
                <div class="form-group">
                  <button
                    type="submit"
                    class="form-control btn btn-primary rounded submit px-3"
                    style="background-color: #006A71"
                  >
                    Login
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>

<?php

//index.php

if (!file_exists('db_connect.php')) {
    header('Location: install.php');
    exit;
}

require_once 'db_connect.php';

require_once 'auth_function.php';

redirectIfLoggedIn();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_name = trim($_POST['user_name']);
    $user_password = trim($_POST['user_password']);

    if (empty($user_name)) {
        $errors[] = "Username is required.";
    }

    if (empty($user_password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM elms_admin WHERE admin_user_name = ?");
            $stmt->execute([$user_name]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if($user){
                if(password_verify($user_password, $user['admin_password'])){
                    $_SESSION['admin_id'] = $user['admin_id'];
                    $_SESSION['user_type'] = 'Admin';
                    $_SESSION['admin_logged_in'] = true;
                    header('Location: dashboard.php');
                } else {
                    $errors[] = "Wrong Password.";
                }
            } else {
                $errors[] = "Wrong Email";
            }
        } catch (PDOException $e) {
            $errors[] = "DB ERROR: " . $e->getMessage();
        }
    }
}


?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Employee Leave Management System Admin Login</title>
    <link href="asset/vendor/bootstrap/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>
<body>
    <main>
        <div class="container">
            <h1 class="mt-5 mb-5 text-center">PHP Employee Leave Management System</h1>
            <div class="row">
                <div class="col-md-4">&nbsp;</div>
                <div class="col-md-4">
                    <?php if (!empty($errors)) { ?>
                        <div class="alert alert-danger">
                            <ul class="list-unstyled">
                                <?php foreach ($errors as $error) { ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                    <div class="card">
                        <div class="card-header"><b>Admin Login</b></div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="user_name" class="form-label">Username:</label>
                                    <input type="text" id="user_name" name="user_name" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="user_password" class="form-label">Password:</label>
                                    <input type="password" id="user_password" name="user_password" class="form-control">
                                </div>
                                <input type="submit" value="Login" class="btn btn-primary">&nbsp;&nbsp;&nbsp;
                                <a href="employee_login.php">Employee Login</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
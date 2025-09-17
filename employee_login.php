<?php

//index.php

if (!file_exists('db_connect.php')) {
    header('Location: install.php');
    exit;
}

require_once 'db_connect.php';

require_once 'auth_function.php';

redirectIfEmpLoggedIn();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_email = trim($_POST['employee_email']);
    $employee_password = trim($_POST['employee_password']);

    if (empty($employee_email)) {
        $errors[] = "Email is required.";
    }

    if (empty($employee_password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM elms_employee WHERE employee_email = ?");
            $stmt->execute([$employee_email]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);
            if($employee){
                if(password_verify($employee_password, $employee['employee_password'])){
                    $_SESSION['employee_id'] = $employee['employee_id'];
                    $_SESSION['user_type'] = 'Employee';
                    header('Location: employee_dashboard.php');
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
                        <div class="card-header"><b>Employee Login</b></div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="employee_email" class="form-label">Email:</label>
                                    <input type="text" id="employee_email" name="employee_email" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="employee_password" class="form-label">Password:</label>
                                    <input type="password" id="employee_password" name="employee_password" class="form-control">
                                </div>
                                <input type="submit" value="Login" class="btn btn-primary">&nbsp;&nbsp;&nbsp;
                                <a href="index.php">Admin Login</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
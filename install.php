<?php

//install.php

session_start();

// Check if installation is already done
if (file_exists('config.php')) {
    header('Location: index.php');
    exit;
}

$errors = [];

$install_step = 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['db_config'])){
        $host = trim($_POST['db_host']);
        $db = trim($_POST['db_name']);
        $user = trim($_POST['db_user']);
        $pass = trim($_POST['db_pass']);

        if (empty($host)) {
            $errors[] = "Database host is required.";
        }
        if (empty($db)) {
            $errors[] = "Database name is required.";
        }
        if (empty($user)) {
            $errors[] = "Database user is required.";
        }
        if (empty($pass)) {
            $errors[] = "Database password is required.";
        }

        if (empty($errors)) {
            $_SESSION['install_data']['host'] = $host;
            $_SESSION['install_data']['user'] = $user;
            $_SESSION['install_data']['pass'] = $pass;
            $_SESSION['install_data']['db'] = $db;
            $_SESSION['install_data']['table'] = [
                "CREATE TABLE `elms_admin` (
                  `admin_id` int NOT NULL AUTO_INCREMENT,
                  `admin_user_name` varchar(100) NOT NULL,
                  `admin_password` varchar(255) NOT NULL,
                  PRIMARY KEY (`admin_id`)
                )",
                "CREATE TABLE `elms_department` (
                  `department_id` int NOT NULL AUTO_INCREMENT,
                  `department_name` varchar(100) NOT NULL,
                  `department_status` enum('Active','Inactive') DEFAULT NULL,
                  `added_on` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_on` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`department_id`)
                )",
                "CREATE TABLE `elms_leave_type` (
                  `leave_type_id` int NOT NULL AUTO_INCREMENT,
                  `leave_type_name` varchar(100) NOT NULL,
                  `added_on` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_on` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  `leave_type_status` enum('Active','Inactive') DEFAULT NULL,
                  `days_allowed` int DEFAULT NULL,
                  PRIMARY KEY (`leave_type_id`)
                )",
                "CREATE TABLE `elms_employee` (
                  `employee_id` int NOT NULL AUTO_INCREMENT,
                  `employee_unique_code` varchar(50) NOT NULL,
                  `employee_first_name` varchar(100) NOT NULL,
                  `employee_last_name` varchar(100) NOT NULL,
                  `employee_email` varchar(100) NOT NULL,
                  `employee_password` varchar(255) NOT NULL,
                  `employee_gender` enum('Male','Female','Other') NOT NULL,
                  `employee_birthdate` date NOT NULL,
                  `employee_department` int DEFAULT NULL,
                  `employee_address` text,
                  `employee_city` varchar(100) DEFAULT NULL,
                  `employee_country` varchar(100) DEFAULT NULL,
                  `employee_mobile_number` varchar(15) DEFAULT NULL,
                  `employee_status` enum('Active','Inactive') DEFAULT 'Active',
                  PRIMARY KEY (`employee_id`),
                  UNIQUE KEY `employee_unique_code` (`employee_unique_code`),
                  UNIQUE KEY `employee_email` (`employee_email`),
                  KEY `employee_department` (`employee_department`),
                  CONSTRAINT `elms_employee_ibfk_1` FOREIGN KEY (`employee_department`) REFERENCES `elms_department` (`department_id`)
                )",
                "CREATE TABLE `elms_leave` (
                  `leave_id` int NOT NULL AUTO_INCREMENT,
                  `employee_id` int DEFAULT NULL,
                  `leave_type` int DEFAULT NULL,
                  `leave_start_date` date NOT NULL,
                  `leave_end_date` date NOT NULL,
                  `leave_description` text,
                  `leave_admin_remark` text,
                  `leave_status` enum('Pending','Admin Read','Approve','Reject') DEFAULT 'Pending',
                  `leave_apply_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                  `leave_admin_remark_date` timestamp NULL DEFAULT NULL,
                  PRIMARY KEY (`leave_id`),
                  KEY `employee_id` (`employee_id`),
                  KEY `leave_type` (`leave_type`),
                  CONSTRAINT `elms_leave_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `elms_employee` (`employee_id`),
                  CONSTRAINT `elms_leave_ibfk_2` FOREIGN KEY (`leave_type`) REFERENCES `elms_leave_type` (`leave_type_id`)
                )",
                "CREATE TABLE `elms_leave_balance` (
                  `leave_balance_id` int NOT NULL AUTO_INCREMENT,
                  `employee_id` int NOT NULL,
                  `leave_type_id` int NOT NULL,
                  `leave_balance` int NOT NULL DEFAULT '0',
                  PRIMARY KEY (`leave_balance_id`),
                  KEY `employee_id` (`employee_id`),
                  KEY `leave_type_id` (`leave_type_id`),
                  CONSTRAINT `elms_leave_balance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `elms_employee` (`employee_id`),
                  CONSTRAINT `elms_leave_balance_ibfk_2` FOREIGN KEY (`leave_type_id`) REFERENCES `elms_leave_type` (`leave_type_id`)
                )",                
                "CREATE TABLE `elms_notifications` (
                  `notification_id` int NOT NULL AUTO_INCREMENT,
                  `recipient_id` int NOT NULL,
                  `recipient_role` enum('Admin','Employee') NOT NULL,
                  `notification_message` text NOT NULL,
                  `notification_status` enum('Unread','Read') DEFAULT 'Unread',
                  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                  `leave_id` int DEFAULT NULL,
                  PRIMARY KEY (`notification_id`)
                )"
            ];
            $_SESSION['step'] = 2;
        }
    }
    if(isset($_POST['admin_account'])){
        $user_name = trim($_POST['user_name']);
        $user_password = trim($_POST['user_password']);

        if (empty($user_name)) {
            $errors[] = "Admin Name is required.";
        }
    
        if (empty($user_password)) {
            $errors[] = "Admin password is required.";
        } elseif (strlen($user_password) < 6) {
            $errors[] = "Password must be at least 6 characters.";
        }

        if (empty($errors)) {
            $hashed_password = password_hash($user_password, PASSWORD_BCRYPT);

            try {
                $pdo = new PDO("mysql:host=".$_SESSION['install_data']['host']."", $_SESSION['install_data']['user'], $_SESSION['install_data']['pass']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Create database if it doesn't exist
                $pdo->exec("CREATE DATABASE IF NOT EXISTS ".$_SESSION['install_data']['db']."");
                $pdo->exec("USE " . $_SESSION['install_data']['db'] . "");

                foreach ($_SESSION['install_data']['table'] as $table) {
                    $pdo->exec($table);
                }

                //Set Up Admin Account
                $stmt = $pdo->prepare("INSERT INTO elms_admin (admin_user_name, admin_password) VALUES (?, ?)");
                $stmt->execute([$user_name, $hashed_password]);

                // Create a config.php file to signal the installation completion
                $config_content = "<?php\n";
                $config_content .= "define('DB_HOST', '".$_SESSION['install_data']['host']."');\n";
                $config_content .= "define('DB_NAME', '".$_SESSION['install_data']['db']."');\n";
                $config_content .= "define('DB_USER', '".$_SESSION['install_data']['user']."');\n";
                $config_content .= "define('DB_PASS', '".$_SESSION['install_data']['pass']."');\n";
                file_put_contents('config.php', $config_content);

                //Create Database connection file
                $db_connect_content = "<?php
                require_once 'config.php';
                try {
                    \$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
                    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (PDOException \$e) {
                    die('DB ERROR: ' . \$e->getMessage());
                }
                ?>
                ";
                file_put_contents('db_connect.php', $db_connect_content);                

                unset($_SESSION['step']);
                unset($_SESSION['install_data']);

                header('Location: index.php');
                exit;

            } catch (PDOException $e) {
                $errors[] = "Error: " . $e->getMessage();
            }

        }
    }
}

if (!isset($_SESSION['step'])) {
    $_SESSION['step'] = 1;
    $_SESSION['install_data'] = array();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>PHP Employee Leave Management System Installation Page</title>
    <link href="asset/vendor/bootstrap/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>
<body>
    <main>
        <div class="container">
            <h1 class="mt-5 mb-5 text-center">Employee Leave Management System</h1>
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
                        <?php if ($_SESSION['step'] == 1) { ?>
                        <div class="card-header"><b>Step 1: Database Configuration</b></div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="db_host" class="form-label">Database Host:</label>
                                    <input type="text" id="db_host" name="db_host" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="db_name" class="form-label">Database Name:</label>
                                    <input type="text" id="db_name" name="db_name" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="db_user" class="form-label">Database User:</label>
                                    <input type="text" id="db_user" name="db_user" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="db_pass" class="form-label">Database Password:</label>
                                    <input type="password" id="db_pass" name="db_pass" class="form-control">
                                </div>
                                <input type="submit" name="db_config" value="Next" class="btn btn-primary">
                            </form>
                        </div>
                        <?php } elseif ($_SESSION['step'] == 2) { ?>
                        <div class="card-header"><b>Step 2: Admin Account Creation</b></div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="user_name" class="form-label">Username Name:</label>
                                    <input type="text" id="user_name" name="user_name" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="user_password" class="form-label">Password:</label>
                                    <input type="password" id="user_password" name="user_password" class="form-control">
                                </div>
                                <input type="submit" name="admin_account" value="Finish" class="btn btn-primary">
                            </form>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
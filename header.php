<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>PHP Employee Leave Management System | Webslesson.info</title>
        <link href="asset/css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <link rel="stylesheet" type="text/css" href="asset/vendor/datatables/dataTables.bootstrap5.min.css"/>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>        
    </head>
    <body>
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <!-- Navbar Brand-->
            <a class="navbar-brand ps-3" href="index.html">Leave Management</a>
            <!-- Sidebar Toggle-->
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <!-- Navbar Search-->
            <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                
            </form>
            <!-- Navbar-->
            <?php
            if(isset($_SESSION['user_type'])){
                if($_SESSION['user_type'] === 'Admin'){
                    $fetch_notifications = $pdo->prepare("
                        SELECT * 
                        FROM elms_notifications 
                        WHERE recipient_id = :admin_id AND recipient_role = 'Admin' AND notification_status = 'Unread' 
                        ORDER BY created_at DESC
                    ");
                    $fetch_notifications->execute([':admin_id' => $_SESSION['admin_id']]);
                    $notifications = $fetch_notifications->fetchAll(PDO::FETCH_ASSOC);
                }
                if($_SESSION['user_type'] === 'Employee'){
                    $fetch_notifications = $pdo->prepare("
                        SELECT * 
                        FROM elms_notifications 
                        WHERE recipient_id = :employee_id AND recipient_role = 'Employee' AND notification_status = 'Unread' 
                        ORDER BY created_at DESC
                    ");
                    $fetch_notifications->execute([':employee_id' => $_SESSION['employee_id']]);
                    $notifications = $fetch_notifications->fetchAll(PDO::FETCH_ASSOC);
                }
                if(isset($notifications)){
            ?>
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="notificationDropdown1" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Notifications <span class="badge bg-danger"><?= count($notifications) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <?php if (!empty($notifications)): ?>
                            <?php foreach ($notifications as $notification): ?>
                        <li>
                            <a class="dropdown-item" href="view_leave_details.php?id=<?= $notification['leave_id'] ?>&notification_id=<?= $notification['notification_id'] ?>">
                                <?= htmlspecialchars($notification['notification_message']) ?><br />
                                <small class="text-muted"><?= $notification['created_at'] ?></small>
                            </a>
                        </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <li><a class="dropdown-item">No Notifications</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
            <?php
                }
            }
            ?>
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <?php
                        if(isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Employee'){
                        ?>
                        <li><a class="dropdown-item" href="employee_profile.php">Profile</a></li>
                        <?php
                        }
                        ?>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">                            
                            
                            <?php 
                            if(isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Admin'){
                            ?>
                            <a class="nav-link" href="dashboard.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Dashboard
                            </a>
                            <a class="nav-link" href="department.php">
                                <div class="sb-nav-link-icon"><i class="far fa-building"></i></div>
                                Department
                            </a>
                            <a class="nav-link" href="employee.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-user-md"></i></div>
                                Employee
                            </a>
                            <a class="nav-link" href="leave_type.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-notes-medical"></i></div>
                                Leave Type
                            </a>
                            <?php
                            }
                            if(isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Employee'){
                            ?>
                            <a class="nav-link" href="employee_dashboard.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Dashboard
                            </a>
                            <a class="nav-link" href="employee_profile.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-user-md"></i></div>
                                Profile
                            </a>
                            <a class="nav-link" href="employee_change_password.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-key"></i></div>
                                Change Password
                            </a>
                            <?php
                            }
                            ?>
                            <a class="nav-link" href="leave_list.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tasks"></i></div>
                                Leave
                            </a>
                            <a class="nav-link" href="logout.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt"></i></div>
                                Logout
                            </a>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        Start Bootstrap
                    </div>
                </nav>
            </div>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4 mb-4">
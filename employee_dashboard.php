
<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkEmployeeLogin();

$leaveSql = "SELECT COUNT(*) FROM elms_leave WHERE employee_id = '".$_SESSION['employee_id']."'";
$approveLeaveSql = "SELECT COUNT(*) FROM elms_leave WHERE employee_id = '".$_SESSION['employee_id']."' AND leave_status = 'Approve'";
$pendingLeaveSql = "SELECT COUNT(*) FROM elms_leave WHERE employee_id = '".$_SESSION['employee_id']."' AND leave_status = 'Pending'";

$stmt = $pdo->prepare($leaveSql);
$stmt->execute();
$total_leaves = $stmt->fetchColumn();

$stmt = $pdo->prepare($approveLeaveSql);
$stmt->execute();
$total_approve_leaves = $stmt->fetchColumn();

$stmt = $pdo->prepare($pendingLeaveSql);
$stmt->execute();
$total_pending_leaves = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT lt.leave_type_name, lb.leave_balance FROM elms_leave_balance lb INNER JOIN elms_leave_type lt ON lb.leave_type_id = lt.leave_type_id WHERE lb.employee_id = :employee_id");
$stmt->execute([':employee_id' => $_SESSION['employee_id']]);
$balances = $stmt->fetchAll(PDO::FETCH_ASSOC);


include('header.php');
?>
<style>
    .circle {
        display: inline-block;
        width: 100px;
        height: 100px;
        line-height: 100px;
        border-radius: 50%;
        background-color: white;
        color: black;
        text-align: center;
        font-size: 36px;
        font-weight: bold;
        margin-bottom:16px;
    }
</style>
<h1 class="mt-4">Dashboard</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Dashboard</li>
</ol>
<div class="row mb-4">
    <div class="col-xl-4 col-md-6">
        <div class="card bg-info mb-4">
            <div class="card-body text-center">
                <div class="circle"><?php echo $total_leaves; ?></div><br />
                <b>Total Leaves</b>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card bg-danger mb-4">
            <div class="card-body text-white text-center">
                <div class="circle"><?php echo $total_approve_leaves; ?></div><br />
                <b>Approved Leaves</b>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card bg-secondary text-white mb-4">
            <div class="card-body text-center">
                <div class="circle"><?php echo $total_pending_leaves; ?></div><br />
                <b>Pending Leaves Application</b>
            </div>
        </div>
    </div>
    <?php 
    foreach ($balances as $balance) {
    ?>

    <div class="col-xl-4 col-md-6">
        <div class="card bg-light mb-4">
            <div class="card-body text-center">
                <div class="circle bg-dark text-white"><?php echo $balance['leave_balance']; ?></div><br />
                <b><?php echo $balance['leave_type_name']; ?></b>
            </div>
        </div>
    </div>
    <?php
    }
    ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php
include('footer.php');
?>


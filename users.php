<?php
$host     = "localhost";
$db_name  = "expedia_flight_booking";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("<div style='color:red;padding:20px;'>Database Connection Failure: " . $e->getMessage() . "</div>");
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_user'])) {
    $usrname    = trim($_POST['username']);
    $fname      = trim($_POST['first_name']);
    $lname      = trim($_POST['last_name']);
    $plain_pass = trim($_POST['password']);
    $confirm_p  = trim($_POST['confirm_password']);
    $mobile     = trim($_POST['mobile']);
    $email      = trim($_POST['email']);
    $is_admin   = isset($_POST['system_admin']) ? 1 : 0;

    if (!empty($usrname) && !empty($fname) && !empty($lname) && !empty($plain_pass)) {
        if ($plain_pass !== $confirm_p) {
            $message = "<div class='alert alert-danger'>Password verification mismatch! Ensure entries match.</div>";
        } else {
            try {
                // Verify username availability
                $check = $conn->prepare("SELECT 1 FROM users WHERE username = :uname");
                $check->execute([':uname' => $usrname]);

                if ($check->rowCount() > 0) {
                    $message = "<div class='alert alert-warning'>Username already taken! Choose a unique handle.</div>";
                } else {
                    // Generate a cryptographically secure random Salt
                    $salt = bin2hex(random_bytes(10)); // Generates a strong 20-character salt string
                    
                    // Secure Hashing: Combine password with salt using the SHA-256 standard
                    $secure_hash = hash('sha256', $plain_pass . $salt);

                    $stmt = $conn->prepare("CALL sp_save_user(0, :uname, :fname, :lname, :pass, :salt, :mob, :email, :admin, 1)");
                    $stmt->execute([
                        ':uname' => $usrname, ':fname' => $fname, ':lname' => $lname,
                        ':pass'  => $secure_hash, ':salt' => $salt, ':mob' => $mobile,
                        ':email' => $email, ':admin' => $is_admin
                    ]);
                    $message = "<div class='alert alert-success'>Success! User profile created and password securely encrypted.</div>";
                }
            } catch(Exception $ex) {
                $message = "<div class='alert alert-danger'>Operation Failure: " . $ex->getMessage() . "</div>";
            }
        }
    } else {
        $message = "<div class='alert alert-info'>Please populate all required system fields.</div>";
    }
}

// Fetch active system operators for our presentation layout grid table
$users_list = $conn->query("SELECT user_id, username, first_name, last_name, email, mobile, system_admin, status FROM users ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>xpedia.com | User Security Dashboard</title>
    <style>
        /* Charcoal & Amber/Yellow Secure Theme Framework Style Sheets */
        html{font-family:sans-serif;line-height:1.15;-webkit-text-size-adjust:100%}
        body{margin:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;font-size:0.95rem;background-color:#1e1e24;color:#f8f9fa;padding:30px}
        .container-fluid{width:100%;margin-right:auto;margin-left:auto;max-width:1350px}
        .row{display:flex;flex-wrap:wrap;margin-right:-15px;margin-left:-15px}
        .col-md-4{flex:0 0 33.333333%;max-width:33.333333%;padding:15px;box-sizing:border-box}
        .col-md-8{flex:0 0 66.666667%;max-width:66.666667%;padding:15px;box-sizing:border-box}
        .form-group{margin-bottom:1.1rem}
        .form-control{display:block;width:100%;height:38px;padding:.37rem .75rem;font-size:.875rem;color:#f8f9fa;background-color:#2d2d35;border:1px solid #ffc107;border-radius:.25rem;box-sizing:border-box}
        .btn{display:inline-block;font-weight:600;text-align:center;vertical-align:middle;cursor:pointer;user-select:none;background-color:transparent;border:1px solid transparent;padding:.5rem 1.5rem;font-size:.875rem;line-height:1.5;border-radius:.25rem;text-transform:uppercase;width:100%}
        .btn-warning{color:#1e1e24;background-color:#ffc107;border-color:#ffc107}
        .btn-warning:hover{background-color:#ffdb4d;border-color:#ffdb4d}
        .card{background-color:#25252d;border:1px solid #3e3e4a;border-radius:.35rem;margin-bottom:2rem;box-shadow:0 4px 6px rgba(0,0,0,0.15);overflow:hidden}
        .card-header{padding:.75rem 1.25rem;background-color:#ffc107;color:#1e1e24;font-weight:700;font-size:0.95rem;text-transform:uppercase;letter-spacing:0.5px}
        .card-body{padding:1.5rem}
        .table{width:100%;margin-bottom:0;color:#f8f9fa;border-collapse:collapse}
        .table th,.table td{padding:.75rem;vertical-align:middle;border-top:1px solid #3e3e4a}
        .thead-dark th{color:#ffc107;background-color:#1e1e24;text-align:left;border-bottom:2px solid #ffc107;text-transform:uppercase;font-size:0.8rem}
        .table-striped tbody tr:nth-of-type(odd){background-color:rgba(255,255,255,.02)}
        .alert{padding:.75rem 1.25rem;margin-bottom:1.25rem;border-radius:.25rem;font-size:0.85rem;font-weight:600}
        .alert-success{color:#155724;background-color:#d4edda;border-color:#c3e6cb}
        .alert-warning{color:#856404;background-color:#fff3cd;border-color:#ffeeba}
        .alert-danger{color:#721c24;background-color:#f8d7da;border-color:#f5c6cb}
        .badge-amber{background-color:rgba(255,193,7,0.1);color:#ffc107;border:1px solid #ffc107;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700}
        .badge-admin{background-color:rgba(220,53,69,0.1);color:#dc3545;border:1px solid #dc3545;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700}
        label{color:#ffc107;font-weight:600;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:4px}
    </style>
</head>
<body>

    <div class="container-fluid">
        <!-- Control Header Deck -->
        <div style="border-bottom:2px solid #ffc107;padding-bottom:12px;margin-bottom:25px;">
            <h2 style="margin:0;color:#ffc107;font-weight:700;text-transform:uppercase;letter-spacing:1px;">System Security Operator Profiles</h2>
        </div>

        <!-- Feedback Alert Component -->
        <?php if(!empty($message)) echo $message; ?>

        <div class="row">
            <!-- Left Side Grid: Form Entry Deck -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Register User Profile</div>
                    <div class="card-body">
                        <form action="users.php" method="POST">
                            <div class="form-group">
                                <label for="username">Username Handle</label>
                                <input type="text" id="username" name="username" class="form-control" placeholder="e.g. jdoe_admin" required autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" required autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" required autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label for="mobile">Mobile Number</label>
                                <input type="text" id="mobile" name="mobile" class="form-control" placeholder="e.g. 0712345678" required autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" placeholder="e.g. name@xpedia.com" required autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            </div>
                            <div class="form-group" style="display:flex; align-items:center; margin-top:15px; margin-bottom:15px;">
                                <input type="checkbox" id="system_admin" name="system_admin" value="1" style="width:18px; height:18px; margin-right:10px; cursor:pointer;">
                                <label for="system_admin" style="margin-bottom:0; cursor:pointer;">Grant System Administrator Rights</label>
                            </div>
                            <button type="submit" name="submit_user" class="btn btn-warning">Save User Node</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Side Grid: Registered Operators Tracking Table Panel -->
            <div class="col-md-8">
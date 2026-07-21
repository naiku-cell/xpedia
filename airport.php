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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_airport'])) {
    $city_id      = intval($_POST['airport_city']);
    $airport_name = trim($_POST['airport_name']);

    if (!empty($city_id) && !empty($airport_name)) {
        try {
            // Fetch the parent country ID automatically from the chosen city mapping node
            $city_stmt = $conn->prepare("SELECT country_id FROM cities WHERE city_id = :city_id");
            $city_stmt->execute([':city_id' => $city_id]);
            $city_row = $city_stmt->fetch(PDO::FETCH_ASSOC);
            $country_id = $city_row['country_id'];

            // Duplicate safeguarding check
            $check_stmt = $conn->prepare("SELECT 1 FROM airports WHERE airport_name = :aname AND city_id = :city_id");
            $check_stmt->execute([':aname' => $airport_name, ':city_id' => $city_id]);
            
            if ($check_stmt->rowCount() > 0) {
                $message = "<div class='alert alert-warning'>That airport hub is already mapped under this city entity!</div>";
            } else {
                $ins_stmt = $conn->prepare("INSERT INTO airports (airport_name, city_id, country_id) VALUES (:aname, :city_id, :country_id)");
                $ins_stmt->execute([':aname' => $airport_name, ':city_id' => $city_id, ':country_id' => $country_id]);
                $message = "<div class='alert alert-success'>Success! Airport registered into the system layer.</div>";
            }
        } catch(Exception $ex) {
            $message = "<div class='alert alert-danger'>Error: " . $ex->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert alert-info'>Please fill out all fields.</div>";
    }
}

// Extraction loops for dropdown selectors and grid table
$cities_list = $conn->query("SELECT c.city_id, c.city_name, co.country_name FROM cities c INNER JOIN countries co ON c.country_id = co.country_id ORDER BY co.country_name ASC, c.city_name ASC")->fetchAll(PDO::FETCH_ASSOC);

$airports_list = $conn->query("
    SELECT a.airport_id, a.airport_name, c.city_name, co.country_name 
    FROM airports a
    INNER JOIN cities c ON a.city_id = c.city_id
    INNER JOIN countries co ON a.country_id = co.country_id
    ORDER BY co.country_name ASC, c.city_name ASC, a.airport_name ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>xpedia.com | Airport Terminals Dashboard</title>
    <style>
        html{font-family:sans-serif;line-height:1.15;-webkit-text-size-adjust:100%}
        body{margin:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;font-size:0.95rem;background-color:#1e1e24;color:#f8f9fa;padding:30px}
        .container-fluid{width:100%;margin-right:auto;margin-left:auto;max-width:1300px}
        .row{display:flex;flex-wrap:wrap;margin-right:-15px;margin-left:-15px}
        .col-md-4{flex:0 0 33.333333%;max-width:33.333333%;padding:15px;box-sizing:border-box}
        .col-md-8{flex:0 0 66.666667%;max-width:66.666667%;padding:15px;box-sizing:border-box}
        .form-group{margin-bottom:1.25rem}
        .form-control{display:block;width:100%;height:38px;padding:.37rem .75rem;font-size:.875rem;color:#f8f9fa;background-color:#2d2d35;border:1px solid #ffc107;border-radius:.25rem;box-sizing:border-box}
        .btn{display:inline-block;font-weight:600;text-align:center;vertical-align:middle;cursor:pointer;user-select:none;background-color:transparent;border:1px solid transparent;padding:.5rem 1.5rem;font-size:.875rem;line-height:1.5;border-radius:.25rem;text-transform:uppercase;width:100%}
        .btn-warning{color:#1e1e24;background-color:#ffc107;border-color:#ffc107}
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
        .badge-amber{background-color:rgba(255,193,7,0.1);color:#ffc107;border:1px solid #ffc107;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700}
        label{color:#ffc107;font-weight:600;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:5px}
    </style>
</head>
<body>
    <div class="container-fluid">
        <div style="border-bottom:2px solid #ffc107;padding-bottom:12px;margin-bottom:25px;">
            <h2 style="margin:0;color:#ffc107;font-weight:700;text-transform:uppercase;letter-spacing:1px;">Airports</h2>
        </div>

        <?php if(!empty($message)) echo $message; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Register Airport Hub</div>
                    <div class="card-body">
                        <form action="airport.php" method="POST">
                            <div class="form-group">
                                <label for="airport_city">City</label>
                                <select id="airport_city" name="airport_city" class="form-control" required>
                                    <option value="">Choose City</option>
                                    <?php foreach ($cities_list as $row): ?>
                                        <option value="<?php echo $row['city_id']; ?>">
                                            <?php echo htmlspecialchars($row['country_name'] . " — " . $row['city_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="airport_name">Airport Name</label>
                                <input type="text" id="airport_name" name="airport_name" class="form-control" placeholder="e.g. Heathrow Airport" required autocomplete="off">
                            </div>
                            <button type="submit" name="submit_airport" class="btn btn-warning">Save Terminal</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Active Aviation Infrastructure Hubs</div>
                    <div style="padding:0;overflow-x:auto;">
                        <table class="table table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Country</th>
                                    <th>City Location</th>
                                    <th>International Airport Terminal Hub</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($airports_list) === 0): ?>
                                    <tr>
                                        <td colspan="3" class="text-center" style="color:#aaa;padding:30px;">No operational airports saved.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($airports_list as $air): ?>
                                        <tr>
                                            <td><span class="badge-amber"><?php echo htmlspecialchars($air['country_name']); ?></span></td>
                                            <td style="font-weight:600;"><?php echo htmlspecialchars($air['city_name']); ?></td>
                                            <td style="color:#ffc107;font-weight:700;font-size:1rem;"><?php echo htmlspecialchars($air['airport_name']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// Establish a direct local connection to your flight booking database
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

// Action Handler: Catch and write fresh forms directly to the MySQL engine
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_city'])) {
    $country_id = intval($_POST['city_country']);
    $city_name  = trim($_POST['city_name']);

    if (!empty($country_id) && !empty($city_name)) {
        try {
            // Check if the city name is already mapped under that country
            $check_stmt = $conn->prepare("SELECT 1 FROM cities WHERE city_name = :cname AND country_id = :cid");
            $check_stmt->execute([':cname' => $city_name, ':cid' => $country_id]);
            
            if ($check_stmt->rowCount() > 0) {
                $message = "<div class='alert alert-warning'>That city already exists in this country!</div>";
            } else {
                $ins_stmt = $conn->prepare("INSERT INTO cities (city_name, country_id) VALUES (:cname, :cid)");
                $ins_stmt->execute([':cname' => $city_name, ':cid' => $country_id]);
                $message = "<div class='alert alert-success'>Success! City added to the system dashboard.</div>";
            }
        } catch(Exception $ex) {
            $message = "<div class='alert alert-danger'>Error: " . $ex->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert alert-info'>Please fill out all fields.</div>";
    }
}

// Extract active collections for presentation layout loops
$countries_list = $conn->query("SELECT * FROM countries ORDER BY country_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Pull cities alongside their mapped matching Airport and Airline strings from your schema tables
$query_string = "
    SELECT 
        c.city_id, 
        c.city_name, 
        co.country_name,
        COALESCE(a.airport_name, 'No Airport Linked') AS airport,
        COALESCE(al.airline_name, 'No Airline Linked') AS airline
    FROM cities c
    INNER JOIN countries co ON c.country_id = co.country_id
    LEFT JOIN airports a ON c.city_id = a.city_id
    LEFT JOIN airlines al ON co.country_id = al.airline_id
    ORDER BY co.country_name ASC, c.city_name ASC
";
$cities_list = $conn->query($query_string)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>xpedia.com | Flight Operations Dashboard</title>
    <style>
        /* Charcoal & Amber/Yellow Theme Layout */
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
        .btn-warning:hover{background-color:#ffdb4d;border-color:#ffdb4d}
        .card{background-color:#25252d;border:1px solid #3e3e4a;border-radius:.35rem;margin-bottom:2rem;box-shadow:0 4px 6px rgba(0,0,0,0.15);overflow:hidden}
        .card-header{padding:.75rem 1.25rem;background-color:#ffc107;color:#1e1e24;font-weight:700;font-size:0.95rem;text-transform:uppercase;letter-spacing:0.5px}
        .card-body{padding:1.5rem}
        .table{width:100%;margin-bottom:0;color:#f8f9fa;border-collapse:collapse}
        .table th,.table td{padding:.75rem;vertical-align:middle;border-top:1px solid #3e3e4a}
        .thead-dark th{color:#ffc107;background-color:#1e1e24;text-align:left;border-bottom:2px solid #ffc107;text-transform:uppercase;font-size:0.8rem}
        .table-striped tbody tr:nth-of-type(odd){background-color:rgba(255,255,255,.02)}
        .table-striped tbody tr:hover{background-color:rgba(255,193,7,.04)}
        .alert{padding:.75rem 1.25rem;margin-bottom:1.25rem;border-radius:.25rem;font-size:0.85rem;font-weight:600}
        .alert-success{color:#155724;background-color:#d4edda;border-color:#c3e6cb}
        .alert-warning{color:#856404;background-color:#fff3cd;border-color:#ffeeba}
        .alert-danger{color:#721c24;background-color:#f8d7da;border-color:#f5c6cb}
        .alert-info{color:#0c5460;background-color:#d1ecf1;border-color:#bee5eb}
        .badge-amber{background-color:rgba(255,193,7,0.1);color:#ffc107;border:1px solid #ffc107;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700}
        label{color:#ffc107;font-weight:600;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:5px}
    </style>
</head>
<body>

    <div class="container-fluid">
        <!-- Dashboard Header -->
        <div style="border-bottom:2px solid #ffc107;padding-bottom:12px;margin-bottom:25px;">
            <h2 style="margin:0;color:#ffc107;font-weight:700;text-transform:uppercase;letter-spacing:1px;">Cities</h2>
        </div>

        <!-- Render Feedback Banners -->
        <?php if(!empty($message)) echo $message; ?>

        <div class="row">
            <!-- Left Form Panel -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Register City Entity</div>
                    <div class="card-body">
                        <form action="city.php" method="POST">
                            <div class="form-group">
                                <label for="city_country">Country</label>
                                <select id="city_country" name="city_country" class="form-control" required>
                                    <option value="">Choose Country</option>
                                    <?php foreach ($countries_list as $row): ?>
                                        <option value="<?php echo $row['country_id']; ?>"><?php echo htmlspecialchars($row['country_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="city_name">City Name</label>
                                <input type="text" id="city_name" name="city_name" class="form-control" placeholder="e.g. Mombasa, Tokyo, New York" required autocomplete="off">
                            </div>
                            <button type="submit" name="submit_city" class="btn btn-warning">Save Entity</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Infrastructure Data Grid Table -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Active Regional System Infrastructure</div>
                    <div style="padding:0;overflow-x:auto;">
                        <table class="table table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Country</th>
                                    <th>City Name</th>
                                    <th>Airport</th>
                                    <th>Airlines</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($cities_list) === 0): ?>
                                    <tr>
                                        <td colspan="4" class="text-center" style="color:#aaa;padding:30px;">No records found. Add an entity on the left to begin.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($cities_list as $city): ?>
                                        <tr>
                                            <td><span class="badge-amber"><?php echo htmlspecialchars($city['country_name']); ?></span></td>
                                            <td style="color:#ffc107;font-weight:700;font-size:1.05rem;"><?php echo htmlspecialchars($city['city_name']); ?></td>
                                            <td style="font-size:0.9rem; color:#e0e0e0;"><?php echo htmlspecialchars($city['airport']); ?></td>
                                            <td style="font-weight:600; color:#ffc107; font-size:0.9rem;"><?php echo htmlspecialchars($city['airline']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
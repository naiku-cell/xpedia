<?php
require_once('../models/airports.php');
$airport = new Airport();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_airport'])) {
    echo json_encode($airport->saveAirport(intval($_POST['airport_id']), trim($_POST['airport_name']), intval($_POST['city_id']), intval($_POST['country_id'])));
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['get_airports'])) {
    echo $airport->getAirports();
}
?>
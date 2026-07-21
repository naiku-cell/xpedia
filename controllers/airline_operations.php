<?php
require_once('../models/airlines.php');
$airline = new Airline();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_airline'])) {
    echo json_encode($airline->saveAirline(intval($_POST['airline_id']), trim($_POST['airline_name']), trim($_POST['airline_code']), trim($_POST['logo'])));
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['get_airlines'])) {
    echo $airline->getAirlines();
}
?>
<?php
require_once('../models/flights.php');
$flight = new Flight();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_flight'])) {
    echo json_encode($flight->saveFlight(
        intval($_POST['flight_id']), intval($_POST['airline_id']), trim($_POST['flight_name']),
        intval($_POST['departure_airport_id']), intval($_POST['arrival_airport_id']), trim($_POST['departure_date_time']),
        trim($_POST['return_date_time']), trim($_POST['trip_type']), floatval($_POST['price']), trim($_POST['flight_no'])
    ));
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['get_flights'])) {
    echo $flight->getFlights();
}
?>
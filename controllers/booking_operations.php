<?php
require_once('../models/bookings.php');
$booking = new Booking();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_booking'])) {
        echo json_encode($booking->saveBooking(intval($_POST['booking_id']), trim($_POST['booking_ref']), intval($_POST['flight_id']), intval($_POST['payment_method_id']), intval($_POST['booking_type_id'])));
    }
    if (isset($_POST['save_passenger'])) {
        echo json_encode($booking->savePassenger(trim($_POST['document_no']), trim($_POST['passenger_name']), intval($_POST['age']), trim($_POST['gender']), intval($_POST['booking_id']), intval($_POST['booking_class_id']), intval($_POST['document_id'])));
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['get_bookings'])) {
    $id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
    echo $booking->getBookingDetails($id);
}
?>
<?php
require_once('db.php');
class Booking extends database {
    public function saveBooking($booking_id, $ref, $flight_id, $pay_id, $type_id) {
        $sql = "CALL sp_save_booking({$booking_id}, '{$ref}', {$flight_id}, {$pay_id}, {$type_id})";
        return $this->getData($sql);
    }
    public function savePassenger($doc_no, $name, $age, $gender, $booking_id, $class_id, $doc_id) {
        $sql = "CALL sp_save_passenger('{$doc_no}', '{$name}', {$age}, '{$gender}', {$booking_id}, {$class_id}, {$doc_id})";
        return $this->getData($sql);
    }
    public function getBookingDetails($booking_id = 0) {
        $sql = "CALL sp_get_booking_details({$booking_id})";
        return $this->getJSON($sql);
    }
}
?>
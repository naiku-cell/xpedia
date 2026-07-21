<?php
require_once('db.php');
class Flight extends database {
    public function saveFlight($flight_id, $airline_id, $flight_name, $dep_id, $arr_id, $dep_time, $ret_time, $type, $price, $number) {
        $ret_val = empty($ret_time) ? "NULL" : "'{$ret_time}'";
        $sql = "CALL sp_save_flight({$flight_id}, {$airline_id}, '{$flight_name}', {$dep_id}, {$arr_id}, '{$dep_time}', {$ret_val}, '{$type}', {$price}, '{$number}')";
        return $this->getData($sql);
    }
    public function getFlights() {
        $sql = "CALL sp_get_flights()";
        return $this->getJSON($sql);
    }
}
?>
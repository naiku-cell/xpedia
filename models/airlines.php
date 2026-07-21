<?php
require_once('db.php');
class Airline extends database {
    public function saveAirline($airline_id, $airline_name, $airline_code, $logo) {
        $sql = "CALL sp_save_airline({$airline_id}, '{$airline_name}', '{$airline_code}', '{$logo}')";
        return $this->getData($sql);
    }
    public function getAirlines() {
        $sql = "CALL sp_get_airlines()";
        return $this->getJSON($sql);
    }
}
?>
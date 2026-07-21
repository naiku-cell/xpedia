<?php
require_once('db.php');
class Airport extends database {
    public function saveAirport($airport_id, $airport_name, $city_id, $country_id) {
        $sql = "CALL sp_save_airport({$airport_id}, '{$airport_name}', {$city_id}, {$country_id})";
        return $this->getData($sql);
    }
    public function getAirports() {
        $sql = "CALL sp_get_airports()";
        return $this->getJSON($sql);
    }
}
?>
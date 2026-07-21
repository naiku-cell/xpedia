<?php
require_once('db.php');

class city extends database {

    public function checkCity($city_id, $city_name, $country_id) {
        // Query engine routine to safeguard matching table strings
        $sql = "SELECT 1 FROM cities WHERE city_name = '{$city_name}' AND country_id = {$country_id} AND city_id != {$city_id}";
        $result = $this->getData($sql);
        if (is_array($result)) {
            return false;
        }
        return $result->rowCount() > 0;
    }

    public function saveCity($city_id, $city_name, $country_id) {
        if ($this->checkCity($city_id, $city_name, $country_id)) {
            return array("status" => "exists", "message" => "City name already exists in this country.");
        }

        $sql = "CALL sp_save_city({$city_id}, '{$city_name}', {$country_id})";
        $this->getData($sql);
        return array("status" => "success", "message" => "City transaction executed successfully.");
    }

    public function filterCities($country_id) {
        $sql = "CALL sp_filter_cities({$country_id})";
        return $this->getJSON($sql);
    }

    public function getCityDetails($city_id) {
        $sql = "CALL sp_get_city_details({$city_id})";
        return $this->getJSON($sql);
    }

    public function deleteCity($city_id) {
        $sql = "CALL sp_delete_city({$city_id})";
        $this->getData($sql);
        return array("status" => "success", "message" => "City record dropped successfully.");
    }
}
?>
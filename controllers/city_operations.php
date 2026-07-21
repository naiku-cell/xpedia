<?php
require_once('../models/cities.php');
$city_obj = new city();

// --- HTTP POST API MAPPINGS (Create / Update / Delete) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Action 1: Upsert Processing Loop
    if (isset($_POST['save_city'])) {
        $city_id = intval($_POST['city_id']);
        $city_name = trim($_POST['city_name']);
        $country_id = intval($_POST['country_id']);

        if (empty($city_name) || $country_id === 0) {
            echo json_encode(array("status" => "error", "message" => "Missing required parameter data fields."));
            exit();
        }

        $response = $city_obj->saveCity($city_id, $city_name, $country_id);
        echo json_encode($response);
    }

    // Action 2: Deletion Routine Block
    if (isset($_POST['delete_city'])) {
        $city_id = intval($_POST['city_id']);
        $response = $city_obj->deleteCity($city_id);
        echo json_encode($response);
    }
}

// --- HTTP GET API MAPPINGS (Read / Search Lookups) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Action 3: Pull Entire Collection or Filter by Country Map
    if (isset($_GET['get_cities'])) {
        $country_id = isset($_GET['country_id']) ? intval($_GET['country_id']) : 0;
        echo $city_obj->filterCities($country_id);
    }

    // Action 4: Extract Single Target Profile Detail Row
    if (isset($_GET['get_city_details'])) {
        $city_id = intval($_GET['city_id']);
        echo $city_obj->getCityDetails($city_id);
    }
}
?>
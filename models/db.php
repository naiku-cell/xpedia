<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$SQL = "";

class database {
    private $server_name;
    private $database_name;
    private $username;
    private $password;
    private $character_set;

    protected function connect() {
        // Match settings to local server configuration parameters
        $this->server_name   = "localhost";
        $this->database_name = "expedia_flight_booking";
        $this->username      = "root";
        $this->password      = "";
        $this->character_set = "utf8mb4";

        try {
            $DSN = "mysql:host=" . $this->server_name . ";dbname=" . $this->database_name . ";charset=" . $this->character_set;
            $PDO_obj = new PDO($DSN, $this->username, $this->password);
            $PDO_obj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $PDO_obj;
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            die();
        }
    }

    // Execute state changing modifications (INSERT, UPDATE, DELETE)
    public function getData($sql_query) {
        try {
            $connection = $this->connect();
            $statement = $connection->prepare($sql_query);
            $statement->execute();
            return $statement;
        } catch (PDOException $e) {
            return array("status" => "error", "message" => $e->getMessage());
        }
    }

    // Process table extraction lists into clean JSON objects
    public function getJSON($sql_query) {
        try {
            $connection = $this->connect();
            $statement = $connection->prepare($sql_query);
            $statement->execute();
            $results = $statement->fetchAll(PDO::FETCH_ASSOC);
            return json_encode($results);
        } catch (PDOException $e) {
            return json_encode(array("status" => "error", "message" => $e->getMessage()));
        }
    }
}
?>
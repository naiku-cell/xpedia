-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 21, 2026 at 08:22 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `expedia_flight_booking`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_delete_city` (IN `p_city_id` INT)   BEGIN
    DELETE FROM cities WHERE city_id = p_city_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_filter_cities` (IN `p_country_id` INT)   BEGIN
    -- If country_id is 0, return all cities. Otherwise, filter by country.
    SELECT c.city_id, c.city_name, co.country_name 
    FROM cities c
    INNER JOIN countries co ON c.country_id = co.country_id
    WHERE p_country_id = 0 OR c.country_id = p_country_id
    ORDER BY c.city_name ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_airlines` ()   BEGIN
    SELECT * FROM airlines ORDER BY airline_name ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_airports` ()   BEGIN
    SELECT a.airport_id, a.airport_name, c.city_name, co.country_name 
    FROM airports a
    INNER JOIN cities c ON a.city_id = c.city_id
    INNER JOIN countries co ON a.country_id = co.country_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_booking_details` (IN `p_booking_id` INT)   BEGIN
    SELECT b.booking_id, b.booking_ref, b.booking_date, f.flight_no, f.flight_name, p.passenger_name, p.document_no
    FROM booking b
    INNER JOIN flights f ON b.flight_id = f.flight_id
    LEFT JOIN passenger p ON b.booking_id = p.booking_id
    WHERE b.booking_id = p_booking_id OR p_booking_id = 0;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_city_details` (IN `p_city_id` INT)   BEGIN
    SELECT c.city_id, c.city_name, c.country_id, co.country_name 
    FROM cities c
    INNER JOIN countries co ON c.country_id = co.country_id
    WHERE c.city_id = p_city_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_flights` ()   BEGIN
    SELECT f.*, al.airline_name, al.airline_code, dep.airport_name AS departure_airport, arr.airport_name AS arrival_airport
    FROM flights f
    INNER JOIN airlines al ON f.airline_id = al.airline_id
    INNER JOIN airports dep ON f.departure_airport_id = dep.airport_id
    INNER JOIN airports arr ON f.arrival_airport_id = arr.airport_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_save_airline` (IN `p_airline_id` INT, IN `p_airline_name` VARCHAR(100), IN `p_airline_code` VARCHAR(10), IN `p_logo` VARCHAR(255))   BEGIN
    IF p_airline_id = 0 THEN
        IF NOT EXISTS (SELECT 1 FROM airlines WHERE airline_code = p_airline_code) THEN
            INSERT INTO airlines (airline_name, airline_code, logo) VALUES (p_airline_name, p_airline_code, p_logo);
        END IF;
    ELSE
        UPDATE airlines SET airline_name = p_airline_name, airline_code = p_airline_code, logo = p_logo WHERE airline_id = p_airline_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_save_airport` (IN `p_airport_id` INT, IN `p_airport_name` VARCHAR(150), IN `p_city_id` INT, IN `p_country_id` INT)   BEGIN
    IF p_airport_id = 0 THEN
        IF NOT EXISTS (SELECT 1 FROM airports WHERE airport_name = p_airport_name AND city_id = p_city_id) THEN
            INSERT INTO airports (airport_name, city_id, country_id) VALUES (p_airport_name, p_city_id, p_country_id);
        END IF;
    ELSE
        UPDATE airports SET airport_name = p_airport_name, city_id = p_city_id, country_id = p_country_id WHERE airport_id = p_airport_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_save_booking` (IN `p_booking_id` INT, IN `p_booking_ref` VARCHAR(50), IN `p_flight_id` INT, IN `p_payment_method_id` INT, IN `p_booking_type_id` INT)   BEGIN
    IF p_booking_id = 0 THEN
        INSERT INTO booking (booking_ref, flight_id, booking_date, payment_method_id, booking_type_id) 
        VALUES (p_booking_ref, p_flight_id, NOW(), p_payment_method_id, p_booking_type_id);
    ELSE
        UPDATE booking SET booking_ref = p_booking_ref, flight_id = p_flight_id, 
            payment_method_id = p_payment_method_id, booking_type_id = p_booking_type_id 
        WHERE booking_id = p_booking_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_save_city` (IN `p_city_id` INT, IN `p_city_name` VARCHAR(100), IN `p_country_id` INT)   BEGIN
    IF p_city_id = 0 THEN
        IF NOT EXISTS (SELECT 1 FROM cities WHERE city_name = p_city_name AND country_id = p_country_id) THEN
            INSERT INTO cities (city_name, country_id) VALUES (p_city_name, p_country_id);
        END IF;
    ELSE
        UPDATE cities SET city_name = p_city_name, country_id = p_country_id WHERE city_id = p_city_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_save_flight` (IN `p_flight_id` INT, IN `p_airline_id` INT, IN `p_flight_name` VARCHAR(100), IN `p_departure_airport_id` INT, IN `p_arrival_airport_id` INT, IN `p_departure_date_time` DATETIME, IN `p_return_date_time` DATETIME, IN `p_trip_type` VARCHAR(50), IN `p_price` DECIMAL(10,2), IN `p_flight_no` VARCHAR(20))   BEGIN
    IF p_flight_id = 0 THEN
        INSERT INTO flights (airline_id, flight_name, departure_airport_id, arrival_airport_id, departure_date_time, return_date_time, trip_type, price, flight_no) 
        VALUES (p_airline_id, p_flight_name, p_departure_airport_id, p_arrival_airport_id, p_departure_date_time, p_return_date_time, p_trip_type, p_price, p_flight_no);
    ELSE
        UPDATE flights SET airline_id = p_airline_id, flight_name = p_flight_name, departure_airport_id = p_departure_airport_id, 
            arrival_airport_id = p_arrival_airport_id, departure_date_time = p_departure_date_time, return_date_time = p_return_date_time, 
            trip_type = p_trip_type, price = p_price, flight_no = p_flight_no 
        WHERE flight_id = p_flight_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_save_passenger` (IN `p_document_no` VARCHAR(50), IN `p_passenger_name` VARCHAR(150), IN `p_age` INT, IN `p_gender` VARCHAR(20), IN `p_booking_id` INT, IN `p_booking_class_id` INT, IN `p_document_id` INT)   BEGIN
    IF EXISTS (SELECT 1 FROM passenger WHERE document_no = p_document_no) THEN
        UPDATE passenger SET passenger_name = p_passenger_name, age = p_age, gender = p_gender, 
            booking_id = p_booking_id, booking_class_id = p_booking_class_id, document_id = p_document_id 
        WHERE document_no = p_document_no;
    ELSE
        INSERT INTO passenger (passenger_name, age, gender, booking_id, booking_class_id, document_id, document_no) 
        VALUES (p_passenger_name, p_age, p_gender, p_booking_id, p_booking_class_id, p_document_id, p_document_no);
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `airlines`
--

CREATE TABLE `airlines` (
  `airline_id` int(11) NOT NULL,
  `airline_name` varchar(100) NOT NULL,
  `airline_code` varchar(10) NOT NULL,
  `logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `airlines`
--

INSERT INTO `airlines` (`airline_id`, `airline_name`, `airline_code`, `logo`) VALUES
(1, 'Kenya Airways', 'KQ', 'kq_logo.png');

-- --------------------------------------------------------

--
-- Table structure for table `airports`
--

CREATE TABLE `airports` (
  `airport_id` int(11) NOT NULL,
  `airport_name` varchar(150) NOT NULL,
  `city_id` int(11) NOT NULL,
  `country_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `airports`
--

INSERT INTO `airports` (`airport_id`, `airport_name`, `city_id`, `country_id`) VALUES
(1, 'Jomo Kenyatta International Airport', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `booking_id` int(11) NOT NULL,
  `booking_ref` varchar(50) NOT NULL,
  `flight_id` int(11) NOT NULL,
  `booking_date` datetime NOT NULL,
  `payment_method_id` int(11) NOT NULL,
  `booking_type_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`booking_id`, `booking_ref`, `flight_id`, `booking_date`, `payment_method_id`, `booking_type_id`) VALUES
(1, 'BK-KQ-98217', 1, '2026-07-21 14:20:00', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `booking_class`
--

CREATE TABLE `booking_class` (
  `booking_class_id` int(11) NOT NULL,
  `booking_class_name` varchar(50) NOT NULL,
  `no_of_seats` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_class`
--

INSERT INTO `booking_class` (`booking_class_id`, `booking_class_name`, `no_of_seats`, `price`) VALUES
(1, 'Economy Class', 150, 45000.00);

-- --------------------------------------------------------

--
-- Table structure for table `booking_type`
--

CREATE TABLE `booking_type` (
  `booking_type_id` int(11) NOT NULL,
  `booking_type_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_type`
--

INSERT INTO `booking_type` (`booking_type_id`, `booking_type_name`) VALUES
(1, 'One-Way');

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `city_id` int(11) NOT NULL,
  `city_name` varchar(100) NOT NULL,
  `country_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`city_id`, `city_name`, `country_id`) VALUES
(1, 'Nairobi', 1);

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `country_id` int(11) NOT NULL,
  `country_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`country_id`, `country_name`) VALUES
(1, 'Kenya');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `document_id` int(11) NOT NULL,
  `document_name` varchar(50) NOT NULL,
  `document_no` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`document_id`, `document_name`, `document_no`) VALUES
(1, 'Passport', 'A1234567B');

-- --------------------------------------------------------

--
-- Table structure for table `flights`
--

CREATE TABLE `flights` (
  `flight_id` int(11) NOT NULL,
  `airline_id` int(11) NOT NULL,
  `flight_name` varchar(100) NOT NULL,
  `departure_airport_id` int(11) NOT NULL,
  `arrival_airport_id` int(11) NOT NULL,
  `departure_date_time` datetime NOT NULL,
  `return_date_time` datetime DEFAULT NULL,
  `trip_type` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `flight_no` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `flights`
--

INSERT INTO `flights` (`flight_id`, `airline_id`, `flight_name`, `departure_airport_id`, `arrival_airport_id`, `departure_date_time`, `return_date_time`, `trip_type`, `price`, `flight_no`) VALUES
(1, 1, 'Pride of Africa Express', 1, 1, '2026-08-01 08:30:00', NULL, 'One-Way', 45000.00, 'KQ102');

-- --------------------------------------------------------

--
-- Table structure for table `passenger`
--

CREATE TABLE `passenger` (
  `passenger_name` varchar(150) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `booking_class_id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `document_no` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passenger`
--

INSERT INTO `passenger` (`passenger_name`, `age`, `gender`, `booking_id`, `booking_class_id`, `document_id`, `document_no`) VALUES
('John Doe', 29, 'Male', 1, 1, 1, 'A1234567B');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `payment_method_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`payment_method_id`, `booking_id`, `payment_method`, `amount`, `status`) VALUES
(1, 1, 'M-Pesa', 45000.00, 'Completed');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `airlines`
--
ALTER TABLE `airlines`
  ADD PRIMARY KEY (`airline_id`);

--
-- Indexes for table `airports`
--
ALTER TABLE `airports`
  ADD PRIMARY KEY (`airport_id`),
  ADD KEY `fk_airports_city` (`city_id`),
  ADD KEY `fk_airports_country` (`country_id`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `fk_booking_flight` (`flight_id`),
  ADD KEY `fk_booking_payment` (`payment_method_id`),
  ADD KEY `fk_booking_type` (`booking_type_id`);

--
-- Indexes for table `booking_class`
--
ALTER TABLE `booking_class`
  ADD PRIMARY KEY (`booking_class_id`);

--
-- Indexes for table `booking_type`
--
ALTER TABLE `booking_type`
  ADD PRIMARY KEY (`booking_type_id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`city_id`),
  ADD KEY `fk_cities_country` (`country_id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`country_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `fk_documents_passenger` (`document_no`);

--
-- Indexes for table `flights`
--
ALTER TABLE `flights`
  ADD PRIMARY KEY (`flight_id`),
  ADD KEY `fk_flights_airline` (`airline_id`),
  ADD KEY `fk_flights_dep_airport` (`departure_airport_id`),
  ADD KEY `fk_flights_arr_airport` (`arrival_airport_id`);

--
-- Indexes for table `passenger`
--
ALTER TABLE `passenger`
  ADD PRIMARY KEY (`document_no`),
  ADD KEY `fk_passenger_booking` (`booking_id`),
  ADD KEY `fk_passenger_class` (`booking_class_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`payment_method_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `airlines`
--
ALTER TABLE `airlines`
  MODIFY `airline_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `airports`
--
ALTER TABLE `airports`
  MODIFY `airport_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `booking_class`
--
ALTER TABLE `booking_class`
  MODIFY `booking_class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `booking_type`
--
ALTER TABLE `booking_type`
  MODIFY `booking_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `city_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `country_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `flights`
--
ALTER TABLE `flights`
  MODIFY `flight_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `payment_method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `airports`
--
ALTER TABLE `airports`
  ADD CONSTRAINT `fk_airports_city` FOREIGN KEY (`city_id`) REFERENCES `cities` (`city_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_airports_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`country_id`) ON DELETE CASCADE;

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `fk_booking_flight` FOREIGN KEY (`flight_id`) REFERENCES `flights` (`flight_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_booking_payment` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`payment_method_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_booking_type` FOREIGN KEY (`booking_type_id`) REFERENCES `booking_type` (`booking_type_id`) ON DELETE CASCADE;

--
-- Constraints for table `cities`
--
ALTER TABLE `cities`
  ADD CONSTRAINT `fk_cities_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`country_id`) ON DELETE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `fk_documents_passenger` FOREIGN KEY (`document_no`) REFERENCES `passenger` (`document_no`) ON DELETE CASCADE;

--
-- Constraints for table `flights`
--
ALTER TABLE `flights`
  ADD CONSTRAINT `fk_flights_airline` FOREIGN KEY (`airline_id`) REFERENCES `airlines` (`airline_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_flights_arr_airport` FOREIGN KEY (`arrival_airport_id`) REFERENCES `airports` (`airport_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_flights_dep_airport` FOREIGN KEY (`departure_airport_id`) REFERENCES `airports` (`airport_id`) ON DELETE CASCADE;

--
-- Constraints for table `passenger`
--
ALTER TABLE `passenger`
  ADD CONSTRAINT `fk_passenger_booking` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`booking_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_passenger_class` FOREIGN KEY (`booking_class_id`) REFERENCES `booking_class` (`booking_class_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

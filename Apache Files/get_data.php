<?php
// Connect to MySQL database
$conn = new mysqli("localhost", "username", "password", "temperature_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to retrieve data for the last 12 hours
$query = "SELECT sensor_name, sensor_value, UNIX_TIMESTAMP(timestamp) AS timestamp_unix 
          FROM temperature_data 
          WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 14 HOUR) 
          ORDER BY timestamp ASC";

$result = $conn->query($query);

$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Close connection
$conn->close();

// Return data in JSON format
header('Content-Type: application/json');
echo json_encode($data);
?>

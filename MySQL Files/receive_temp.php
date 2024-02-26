<?php
// Function to sanitize input data
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if all parameters are set
if(isset($_POST['device_name']) && isset($_POST['sensor_name']) && isset($_POST['sensor_value'])) {
    // Sanitize input data
    $device_name = sanitize($_POST['device_name']);
    $sensor_name = sanitize($_POST['sensor_name']);
    $sensor_value = sanitize($_POST['sensor_value']);

    // Database connection parameters
    $servername = "localhost";
    $username = "username"; // Change to your MySQL username
    $password = "password"; // Change to your MySQL password
    $dbname = "temperature_db";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and bind statement
    $stmt = $conn->prepare("INSERT INTO temperature_data (device_name, sensor_name, sensor_value, timestamp) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $device_name, $sensor_name, $sensor_value);

    // Execute statement
    if ($stmt->execute() === TRUE) {
        echo "Data received and stored successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo "Incomplete parameters.";
}
?>

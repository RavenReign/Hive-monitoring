<?php
// Initialize variables
$x = "";
$y = "";
$z = "";

// Connect to MySQL database
$servername = "localhost";
$username = "USERNAME";
$password = "PASSWORD";
$dbname = "DATABASENAME";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Calculate 12 hours ago timestamp
$twelve_hours_ago = date('Y-m-d H:i:s', strtotime('-12 hours'));

// Query to retrieve sensor names and latest temperature value from last 12 hours
$sql = "SELECT t.sensor_name, t.sensor_value, t.timestamp 
        FROM Temperature_Data t
        JOIN (SELECT sensor_name, MAX(timestamp) AS max_timestamp 
              FROM Temperature_Data 
              WHERE timestamp >= '$twelve_hours_ago' 
              GROUP BY sensor_name) m
        ON t.sensor_name = m.sensor_name AND t.timestamp = m.max_timestamp";

$result = $conn->query($sql);

$sensor_data = array();

if ($result->num_rows > 0) {
    // Fetch sensor data from the result set
    while ($row = $result->fetch_assoc()) {
        $sensor_name = $row["sensor_name"];
        $sensor_value = $row["sensor_value"];
        $timestamp = $row["timestamp"];

        // Retrieve x, y, z coordinates for sensor from sensor_xyz.txt file
        $sensor_xyz_file = 'sensor_xyz.txt';
        if (file_exists($sensor_xyz_file) && is_readable($sensor_xyz_file)) {
            // Read sensor_xyz.txt line by line
            $existing_sensors = file($sensor_xyz_file, FILE_IGNORE_NEW_LINES);

            $sensor_found = false;
            // Check if the sensor exists in sensor_xyz.txt
            foreach ($existing_sensors as $line) {
                list($existing_sensor_name, $existing_x, $existing_y, $existing_z) = explode(',', $line);
                if ($existing_sensor_name == $sensor_name) {
                    $x = $existing_x;
                    $y = $existing_y;
                    $z = $existing_z;
                    $sensor_found = true;
                    break;
                }
            }

            // If sensor not found, add it to sensor_xyz.txt with blank coordinates
            if (!$sensor_found) {
                // Append sensor data to sensor_xyz.txt with blank coordinates
                file_put_contents($sensor_xyz_file, "$sensor_name,,,\n", FILE_APPEND);
            }
        }

        // Add sensor data to array
        if (!empty($x) && !empty($y) && !empty($z)) {
            // Only add data if x, y, z coordinates are not empty
            $sensor_data[] = array(
                "x" => $x,
                "y" => $y,
                "z" => $z,
                "value" => $sensor_value,
                "sensor_name" => $sensor_name,
                "timestamp" => $timestamp
            );
        }
    }
}

// Close MySQL connection
$conn->close();

// Scatter plot code
?>
<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <style>
        /* Center the graph container */
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #plotly-graph {
            width: 1200px; /* Set the width as desired */
            height: 1000px; /* Set the height as desired */
        }
    </style>
</head>
<body>

<div id="plotly-graph"></div>

<script>
// Parse the JSON data obtained from PHP
var sensorData = <?php echo json_encode($sensor_data); ?>;

// Extract x, y, z, value, sensor_name, and timestamp arrays
var x = sensorData.map(obj => obj.x);
var y = sensorData.map(obj => obj.y);
var z = sensorData.map(obj => obj.z);
var value = sensorData.map(obj => obj.value);
var sensorName = sensorData.map(obj => obj.sensor_name);
var timestamp = sensorData.map(obj => obj.timestamp);

// Create hover text for each point
var hoverText = [];
for (var i = 0; i < sensorData.length; i++) {
    hoverText.push('Sensor: ' + sensorName[i] + '<br>Temperature: ' + value[i] + '<br>Timestamp: ' + timestamp[i]);
}

// Create a trace for the scatter plot
var trace = {
    x: x,
    y: y,
    z: z,
    mode: 'markers',
    marker: {
        size: 20,
        color: value,  // Color by temperature value
        colorscale: [
            [0, 'blue'],    // Lowest value (blue)
            [0.25, 'yellow'],  // Intermediate value (yellow)
            [0.5, 'orange'], // Intermediate value (orange)
            [1, 'red']      // Highest value (red)
        ],
        opacity: 0.8,
        colorbar: {
            title: 'Temperature',  // Title for the color bar legend
            ticksuffix: ' °C'  // Suffix for color bar ticks
        }
    },
    type: 'scatter3d',
    hoverinfo: 'text',
    text: hoverText
};

// Define layout options
var layout = {
    scene: {
        xaxis: { title: 'X Axis' },
        yaxis: { title: 'Y Axis' },
        zaxis: { title: 'Z Axis' }
    }
};

// Combine trace and layout
var data = [trace];

// Plot the graph
Plotly.newPlot('plotly-graph', data, layout);
</script>

</body>
</html>

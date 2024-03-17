<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Your PHP code to retrieve data from the database and display it here
$servername = "localhost";
$username = "root"; // Default username for XAMPP MySQL
$password = "";     // Default password is empty for XAMPP MySQL
$dbname = "temperature_db";

// Create connection with default username and password
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Default time range
$timeRange = isset($_GET['time_range']) ? $_GET['time_range'] : '12h';

// Default start time
$startTime = isset($_GET['start_time']) ? $_GET['start_time'] : calculateStartTime($timeRange);

// Calculate start time based on time range
function calculateStartTime($timeRange) {
    switch ($timeRange) {
        case '3h':
            return strtotime('-3 hours');
        case '6h':
            return strtotime('-6 hours');
        case '12h':
            return strtotime('-12 hours');
        case '24h':
            return strtotime('-24 hours');
        case '48h':
            return strtotime('-48 hours');
        case '3d':
            return strtotime('-3 days');
        case '1w':
            return strtotime('-1 week');
        case '2w':
            return strtotime('-2 weeks');
        case '1M':
            return strtotime('-1 month');
        case '3M':
            return strtotime('-3 months');
        case '6M':
            return strtotime('-6 months');
        case '1y':
            return strtotime('-1 year');
        default:
            return 0; // Default start time
    }
}

// Offset for previous and next buttons
$offset = getTimeRangeInSeconds($timeRange);

// Adjust start time for previous and next buttons
if (isset($_GET['offset'])) {
    $offset = $_GET['offset'];
    $startTime = $_GET['start_time'];
}

// Calculate end time based on selected time range
$endTime = $startTime + $offset;

// Function to get time range in seconds
function getTimeRangeInSeconds($timeRange) {
    switch ($timeRange) {
        case '3h':
            return 3 * 3600;
        case '6h':
            return 6 * 3600;
        case '12h':
            return 12 * 3600;
        case '24h':
            return 24 * 3600;
        case '48h':
            return 48 * 3600;
        case '3d':
            return 3 * 24 * 3600;
        case '1w':
            return 7 * 24 * 3600;
        case '2w':
            return 14 * 24 * 3600;
        case '1M':
            return 30 * 24 * 3600;
        case '3M':
            return 3 * 30 * 24 * 3600;
        case '6M':
            return 6 * 30 * 24 * 3600;
        case '1y':
            return 365 * 24 * 3600;
        default:
            return 0; // Default time range
    }
}

// SQL query to fetch sensor_name, timestamp, and sensor_value based on start time
if ($timeRange === '3h' ||$timeRange === '6h' || $timeRange === '12h' || $timeRange === '24h' ||$timeRange === '48h') {
    // No aggregation for 6 hours, 12 hours, and 48 hours
    $sql = "SELECT sensor_name, timestamp, sensor_value FROM temperature_data WHERE timestamp >= FROM_UNIXTIME($startTime) AND timestamp <= FROM_UNIXTIME($endTime) ORDER BY timestamp";
} elseif ($timeRange === '3d' || $timeRange === '1w' || $timeRange === '2w') {
    // 1 hour aggregation for 3 days, 1 week, and 2 weeks
    $sql = "SELECT sensor_name, FROM_UNIXTIME(UNIX_TIMESTAMP(timestamp) DIV (3600)*(3600)) as timestamp, AVG(sensor_value) as sensor_value FROM temperature_data WHERE timestamp >= FROM_UNIXTIME($startTime) AND timestamp <= FROM_UNIXTIME($endTime) GROUP BY sensor_name, FROM_UNIXTIME(UNIX_TIMESTAMP(timestamp) DIV (3600)*(3600)) ORDER BY timestamp";
} elseif ($timeRange === '1M' || $timeRange === '3M') {
    // 12 hour aggregation for 1 month and 3 months
    $sql = "SELECT sensor_name, FROM_UNIXTIME(UNIX_TIMESTAMP(timestamp) DIV (3600*12)*(3600*12)) as timestamp, AVG(sensor_value) as sensor_value FROM temperature_data WHERE timestamp >= FROM_UNIXTIME($startTime) AND timestamp <= FROM_UNIXTIME($endTime) GROUP BY sensor_name, FROM_UNIXTIME(UNIX_TIMESTAMP(timestamp) DIV (3600*12)*(3600*12)) ORDER BY timestamp";
} else {
    // Default 24 hour aggregation
    $sql = "SELECT sensor_name, FROM_UNIXTIME(UNIX_TIMESTAMP(timestamp) DIV (3600*24)*(3600*24)) as timestamp, AVG(sensor_value) as sensor_value FROM temperature_data WHERE timestamp >= FROM_UNIXTIME($startTime) AND timestamp <= FROM_UNIXTIME($endTime) GROUP BY sensor_name, FROM_UNIXTIME(UNIX_TIMESTAMP(timestamp) DIV (3600*24)*(3600*24)) ORDER BY timestamp";
}

$result = $conn->query($sql);

$data = [];

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        // Collect data into an array
        $data[] = $row;
    }
} else {
    echo "0 results";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temperature Data</title>
    <script src="http://localhost/localonly/javachartscripts/chart.js"></script>
    <script src="http://localhost/localonly/javachartscripts/moment.js"></script>
    <script src="http://localhost/localonly/javachartscripts/chartjs-adapter-moment.js"></script>
</head>
<body>
    <div>
        <label for="timeRangeSelect">Select Time Range:</label>
        <select id="timeRangeSelect" onchange="updateChart()">
            <option value="3h" <?php if ($timeRange === '3h') echo 'selected'; ?>>3 Hours</option>
            <option value="6h" <?php if ($timeRange === '6h') echo 'selected'; ?>>6 Hours</option>
            <option value="12h" <?php if ($timeRange === '12h') echo 'selected'; ?>>12 Hours</option>
            <option value="24h" <?php if ($timeRange === '24h') echo 'selected'; ?>>24 Hours</option>
            <option value="48h" <?php if ($timeRange === '48h') echo 'selected'; ?>>48 Hours</option>
            <option value="3d" <?php if ($timeRange === '3d') echo 'selected'; ?>>3 Days</option>
            <option value="1w" <?php if ($timeRange === '1w') echo 'selected'; ?>>1 Week</option>
            <option value="2w" <?php if ($timeRange === '2w') echo 'selected'; ?>>2 Weeks</option>
            <option value="1M" <?php if ($timeRange === '1M') echo 'selected'; ?>>1 Month</option>
            <option value="3M" <?php if ($timeRange === '3M') echo 'selected'; ?>>3 Months</option>
            <option value="6M" <?php if ($timeRange === '6M') echo 'selected'; ?>>6 Months</option>
            <option value="1y" <?php if ($timeRange === '1y') echo 'selected'; ?>>1 Year</option>
        </select>
        <button onclick="previousData()">Previous</button>
        <button onclick="nextData()">Next</button>
        <button onclick="toggleLines()">Toggle Lines</button>
    </div>

    <div id="loadingIndicator">Loading data...</div>
    <canvas id="temperatureChart" width="800" height="400"></canvas>
	
	

    <script>
        // Define getTimeRangeInSeconds as a JavaScript function
        function getTimeRangeInSeconds(timeRange) {
            switch (timeRange) {
                case '3h':
                    return 3 * 3600;
                case '6h':
                    return 6 * 3600;
                case '12h':
                    return 12 * 3600;
                case '24h':
                    return 24 * 3600;
                case '48h':
                    return 48 * 3600;
                case '3d':
                    return 3 * 24 * 3600;
                case '1w':
                    return 7 * 24 * 3600;
                case '2w':
                    return 14 * 24 * 3600;
                case '1M':
                    return 30 * 24 * 3600;
                case '3M':
                    return 3 * 30 * 24 * 3600;
                case '6M':
                    return 6 * 30 * 24 * 3600;
                case '1y':
                    return 365 * 24 * 3600;
                default:
                    return 0; // Default time range
            }
        }
		
        // Parse data fetched from PHP
        const data = <?php echo json_encode($data); ?>;

        // Function to process data and create chart
        function createChart(data) {
            const datasets = {};

            data.forEach(entry => {
                if (!datasets[entry.sensor_name]) {
                    datasets[entry.sensor_name] = {
                        label: entry.sensor_name,
                        data: [],
                        borderColor: getRandomColor(),
                        fill: false
                    };
                }
                datasets[entry.sensor_name].data.push({
                    x: entry.timestamp,
                    y: entry.sensor_value
                });
            });

            const ctx = document.getElementById('temperatureChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    datasets: Object.values(datasets)
                },
                options: {
                    scales: {
                        x: {
                            type: 'time',
                            time: {}
                        },
                        y: {
                            scaleLabel: {
                                display: true,
                                labelString: 'Temperature'
                            }
                        }
                    }
                }
            });

            document.getElementById('loadingIndicator').style.display = 'none'; // Hide loading indicator
        }

        // Function to generate random color
        function getRandomColor() {
            return '#' + Math.floor(Math.random() * 16777215).toString(16);
        }

        // Function to update chart based on selected time range
        function updateChart() {
            const selectedRange = document.getElementById('timeRangeSelect').value;
            window.location.href = `?time_range=${selectedRange}`;
        }

        // Function to navigate to previous data
        function previousData() {
            const selectedRange = document.getElementById('timeRangeSelect').value;
            const offset = getTimeRangeInSeconds(selectedRange);
            const startTime = <?php echo $startTime; ?> - offset;
            window.location.href = `?time_range=${selectedRange}&start_time=${startTime}`;
        }

        // Function to navigate to next data
        function nextData() {
            const selectedRange = document.getElementById('timeRangeSelect').value;
            const offset = getTimeRangeInSeconds(selectedRange);
            const startTime = <?php echo $startTime; ?> + offset;
            window.location.href = `?time_range=${selectedRange}&start_time=${startTime}`;
        }
        
        // Function to toggle visibility of all lines
        function toggleLines() {
            const chart = Chart.getChart('temperatureChart');
            chart.data.datasets.forEach(dataset => {
                dataset.hidden = !dataset.hidden;
            });
            chart.update();
        }

        // Call createChart function with fetched data
        createChart(data);
    </script>
</body>
</html>

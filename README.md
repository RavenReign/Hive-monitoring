ESP32 Dallas Temperature Sensor Data Logger for Honeybee Hives
Overview

This project is primarily built to track the temperatures inside honeybee hives and log them. The ESP32 microcontroller interfaces with Dallas Temperature Sensors placed inside the hives to collect temperature data at regular intervals. The collected data is then transmitted to a server for storage. The server provides a web interface where users can view the temperature data on a multi-line graph and possibly a 3D heat map, allowing for detailed monitoring and analysis of hive conditions over time. Additionally, the system is designed around being deployed offline without internet connectivity, ensuring continuous data collection and monitoring even in remote locations. (Be aware security is not a focus, please use with care.

Disclaimer!

This project is developed by an individual with limited coding experience who heavily relies on ChatGPT for guidance. With the occasional human help. The code provided here is tested in a specific environment, and all uploaded code should be assumed to be working unless stated otherwise. Your results may vary depending on your specific setup and configurations.

Usage

    Hardware Setup: Connect the Dallas Temperature Sensors to the ESP32 microcontroller following the provided instructions.

    Software Setup:
        Configure the ESP32 code with your WiFi credentials and server information.
        Upload the modified ESP32 code to your microcontroller.

    Server Setup: Set up a server to receive and store the temperature data transmitted by the ESP32. Ensure that the server is accessible over the network.

    Data Visualization: Access the web interface hosted on the server to view and analyze the collected temperature data using the provided graphs.

    Monitoring: Monitor the temperature data regularly through the web interface and take necessary actions based on the analysis.

Contributions

Contributions to this project are welcome! If you have any ideas for improvements, new features, or bug fixes, feel free to open an issue or submit a pull request.
License

This project is licensed under the MIT License.
Credits

    ESP32
    Dallas Temperature Sensors
    Arduino IDE

Support

For any questions or issues regarding this project, please open an issue on GitHub.

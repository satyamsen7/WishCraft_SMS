<?php
// DB connection
$host = "localhost";
$username = "root";
$password = "";
$database = "WISHCRAFT";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Today's date
$today = date("m-d");

// Function to send message
function sendBirthdayMessage($name, $number) {
    // Removed urlencode to preserve spaces in names
    $variables = $name;

    $postData = [
        "route" => "dlt",
        "sender_id" => "YOUR_DLT_SENDER_ID",
        "message" => "YOUR_DLT_MESSAGE_ID", // DLT Template ID
        "variables_values" => $variables,
        "flash" => 0,
        "numbers" => $number
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($postData),
        CURLOPT_HTTPHEADER => [
            "authorization: YOUR_FAST2SMS_API", // FAST2SMS API
            "Content-Type: application/json"
        ]
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        echo "cURL Error #: " . $err . "\n";
    } else {
        echo "Message sent to $name ($number): $response\n";
    }
}

// 🎓 Send to students
$student_sql = "SELECT name, number FROM students WHERE DATE_FORMAT(dob, '%m-%d') = ?";
$student_stmt = $conn->prepare($student_sql);
$student_stmt->bind_param("s", $today);
$student_stmt->execute();
$student_result = $student_stmt->get_result();

while ($row = $student_result->fetch_assoc()) {
    sendBirthdayMessage($row['name'], $row['number']);
}

// 👨‍🏫 Send to faculty
$faculty_sql = "SELECT name, number FROM faculty WHERE DATE_FORMAT(dob, '%m-%d') = ?";
$faculty_stmt = $conn->prepare($faculty_sql);
$faculty_stmt->bind_param("s", $today);
$faculty_stmt->execute();
$faculty_result = $faculty_stmt->get_result();

while ($row = $faculty_result->fetch_assoc()) {
    sendBirthdayMessage($row['name'], $row['number']);
}

$conn->close();
?>

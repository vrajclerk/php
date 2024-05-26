<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "myDB";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// prepare and bind
$stmt = $conn->prepare("INSERT INTO MyGuests (firstname, lastname, email) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $firstname, $lastname, $email);   //The "sss" argument lists the types of data that the parameters are.
                                                          // The s character tells mysql that the parameter is a string.

// set parameters and execute
$firstname = "vraj";
$lastname = "Clerk";
$email = "vraj04@example.com";
$stmt->execute();

$firstname = "MS";
$lastname = "MDhoni";
$email = "msd@example.com";
$stmt->execute();

$firstname = "Ratan";
$lastname = "Tata";
$email = "tata50@example.com";
$stmt->execute();

echo "New records created successfully";

$stmt->close();
$conn->close();
?>
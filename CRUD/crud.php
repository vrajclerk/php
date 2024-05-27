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

// Create table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS Member (
    id INT(10) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    mobilenum VARCHAR(10) NOT NULL
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating table: " . $conn->error);
}

$editId = '';
$editName = '';
$editCode = '';
$editMobilenum = '';
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $mobilenum = trim($_POST['mobilenum'] ?? '');

    if ($action == 'Insert' || $action == 'Update') {
        // Validate inputs
        if (empty($name) || empty($code) || empty($mobilenum)) {
            $error = 'All fields are required.';
        } elseif (!preg_match('/^[0-9]{10}$/', $mobilenum)) {
            $error = 'Mobile number must contain only digits and length must be 10.';
        } else {
            // Check for duplicate code
            $stmt = $conn->prepare("SELECT id FROM Member WHERE code = ? AND id != ?");
            $stmt->bind_param("si", $code, $id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "Error: Code '$code' is already in use. Please choose a different code.";
            } else {
                if ($action == 'Insert') {
                    $stmt = $conn->prepare("INSERT INTO Member (name, code, mobilenum) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $name, $code, $mobilenum);
                    if ($stmt->execute()) {
                        $success = 'New record created successfully';
                    } else {
                        $error = 'Error: ' . $stmt->error;
                    }
                } elseif ($action == 'Update' && $id > 0) {
                    $stmt = $conn->prepare("UPDATE Member SET name = ?, code = ?, mobilenum = ? WHERE id = ?");
                    $stmt->bind_param("sssi", $name, $code, $mobilenum, $id);
                    if ($stmt->execute()) {
                        $success = 'Record updated successfully';
                    } else {
                        $error = 'Error: ' . $stmt->error;
                    }
                }
            }
            $stmt->close();
        }
    } elseif ($action == 'Delete' && $id > 0) {
        $stmt = $conn->prepare("DELETE FROM Member WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = 'Record deleted successfully';
        } else {
            $error = 'Error: ' . $stmt->error;
        }
        $stmt->close();
    } elseif (isset($_POST['edit_id'])) {
        $editId = intval($_POST['edit_id']);
        $stmt = $conn->prepare("SELECT id, name, code, mobilenum FROM Member WHERE id = ?");
        $stmt->bind_param("i", $editId);
        $stmt->execute();
        $stmt->bind_result($editId, $editName, $editCode, $editMobilenum);
        $stmt->fetch();
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>CRUD Operations</title>
    <!-- <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .action-buttons {
            display: flex;
            justify-content: space-between;
        }
        .action-buttons button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .action-buttons button:hover {
            background-color: #d32f2f;
        }
    </style> -->

</head>
<body>

<h2>Member Form</h2>
<?php
if (!empty($error)) {
    echo "<p style='color:red;'>$error</p>";
}
if (!empty($success)) {
    echo "<p style='color:green;'>$success</p>";
}
?>
<form method="POST" action="">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($editId); ?>">
    <label for="name">Name:</label>
<input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : htmlspecialchars($editName); ?>" required>
<br>
<label for="code">Code:</label>
<input type="text" id="code" name="code" value="<?php echo isset($_POST['code']) ? htmlspecialchars($_POST['code']) : htmlspecialchars($editCode); ?>" required>
<br>
<label for="mobilenum">Mobile Number:</label>
<input type="text" id="mobilenum" name="mobilenum" value="<?php echo isset($_POST['mobilenum']) ? htmlspecialchars($_POST['mobilenum']) : htmlspecialchars($editMobilenum); ?>" required pattern="\d{10}" title="Please enter a 10-digit mobile number.">

    <br>
    <?php if (empty($editId)) { ?>
        <input type="submit" name="action" value="Insert">
    <?php } else { ?>
        <input type="submit" name="action" value="Update">
    <?php } ?>
</form>

<h2>Member List</h2>
<table border="2">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Code</th>
        <th>Mobile Number</th>
        <th>Action</th>
    </tr>
    <?php
    $result = $conn->query("SELECT * FROM Member");
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>"; //convert special characters to entities
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['code']) . "</td>";
        echo "<td>" . htmlspecialchars($row['mobilenum']) . "</td>";
        echo "<td>
                <form method='POST' action='' style='display:inline;'>
                    <input type='hidden' name='edit_id' value='" . htmlspecialchars($row['id']) . "'>
                    <input type='submit' value='Edit'>
                </form>
                <form method='POST' action='' style='display:inline;'>
                    <input type='hidden' name='id' value='" . htmlspecialchars($row['id']) . "'>
                    <input type='hidden' name='action' value='Delete'>
                    <input type='submit' value='Delete'>
                </form>
              </td>";
        echo "</tr>";
    }
    ?>
</table>

</body>
</html>

<?php
$conn->close();
?>

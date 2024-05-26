?php
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $code = isset($_POST['code']) ? trim($_POST['code']) : '';
        $mobilenum = isset($_POST['mobilenum']) ? trim($_POST['mobilenum']) : '';

        // Validate inputs
        if ($action == 'Insert') {
            if (empty($name) || empty($code) || empty($mobilenum)) {
                $error = "All fields are required.";
            } else {
                $stmt = $conn->prepare("INSERT INTO Member (name, code, mobilenum) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $name, $code, $mobilenum);
                if ($stmt->execute()) {
                    echo "New record created successfully";
                } else {
                    if ($stmt->errno == 1062) {
                        $error = "Error: Code must be unique.";
                    } else {
                        $error = "Error: " . $stmt->error;
                    }
                }
                $stmt->close();
            }
        } elseif ($action == 'Update' && $id > 0) {
            if (empty($name) || empty($code) || empty($mobilenum)) {
                $error = "All fields are required.";
            } else {
                $stmt = $conn->prepare("UPDATE Member SET name = ?, code = ?, mobilenum = ? WHERE id = ?");
                $stmt->bind_param("sssi", $name, $code, $mobilenum, $id);
                if ($stmt->execute()) {
                    echo "Record updated successfully";
                } else {
                    if ($stmt->errno == 1062) {
                        $error = "Error: Code must be unique.";
                    } else {
                        $error = "Error: " . $stmt->error;
                    }
                }
                $stmt->close();
            }
        } elseif ($action == 'Delete' && $id > 0) {
            $stmt = $conn->prepare("DELETE FROM Member WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "Record deleted successfully";
            } else {
                $error = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    } elseif (isset($_POST['edit_id'])) {
        $editId = intval($_POST['edit_id']);
        $stmt = $conn->prepare("SELECT * FROM Member WHERE id = ?");
        $stmt->bind_param("i", $editId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $editId = $row['id'];
            $editName = $row['name'];
            $editCode = $row['code'];
            $editMobilenum = $row['mobilenum'];
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>CRUD Operations</title>
</head>
<body>

<h2>Member Form</h2>
<?php
if (!empty($error)) {
    echo "<p style='color:red;'>$error</p>";
}
?>
<form method="POST" action="">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($editId); ?>">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($editName); ?>" required>
    <br>
    <label for="code">Code:</label>
    <input type="text" id="code" name="code" value="<?php echo htmlspecialchars($editCode); ?>" required>
    <br>
    <label for="mobilenum">Mobile Number:</label>
    <input type="text" id="mobilenum" name="mobilenum" value="<?php echo htmlspecialchars($editMobilenum); ?>" required>
    <br>
    <input type="submit" name="action" value="Insert">
    <?php if (!empty($editId)) { ?>
        <input type="submit" name="action" value="Update">
        <!-- <input type="submit" name="action" value="Delete"> -->
    <?php } ?>
</form>

<h2>Member List</h2>
<table border="2">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Code</th>
        <th>Mobile Number</th>
        <th >Action</th>
    </tr>
    <?php
    $result = $conn->query("SELECT * FROM Member");
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
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

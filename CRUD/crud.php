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

// Create Role table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS Role (
    id INT(10) AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating table: " . $conn->error);
}

// Insert some roles 
$result = $conn->query("SELECT COUNT(*) as count FROM Role");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $conn->query("INSERT INTO Role (role_name) VALUES ('Admin'), ('User'), ('Guest')");
}

// Create Member table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS Member (
    id INT(10) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    mobilenum VARCHAR(10) NOT NULL,
    role_id INT(10) NOT NULL,
    FOREIGN KEY (role_id) REFERENCES Role(id)
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating table: " . $conn->error);
}

$editId = '';
$editName = '';
$editCode = '';
$editMobilenum = '';
$editRoleId = '';
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $mobilenum = trim($_POST['mobilenum'] ?? '');
    $role_id = isset($_POST['role_id']) ? intval($_POST['role_id']) : 0;

    if ($action == 'Insert' || $action == 'Update') {
        // Validate inputs
        if (empty($name) || empty($code) || empty($mobilenum) || empty($role_id)) {
            $error = "<script>alert('All fields are required.');</script>";
        } elseif (!preg_match('/^\d{10}$/', $mobilenum)) {
            $error = "<script>alert('Mobile number must contain exactly 10 digits.');</script>";
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
                    $stmt = $conn->prepare("INSERT INTO Member (name, code, mobilenum, role_id) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("sssi", $name, $code, $mobilenum, $role_id);
                    if ($stmt->execute()) {
                        echo "<script>alert('New record created successfully');</script>";
                    } else {
                        $error = "<script>alert('Error: " . $stmt->error . "');</script>";
                    }
                } elseif ($action == 'Update' && $id > 0) {
                    $stmt = $conn->prepare("UPDATE Member SET name = ?, code = ?, mobilenum = ?, role_id = ? WHERE id = ?");
                    $stmt->bind_param("sssii", $name, $code, $mobilenum, $role_id, $id);
                    if ($stmt->execute()) {
                        echo "<script>alert('Record updated successfully');</script>";
                    } else {
                        $error = "<script>alert('Error: " . $stmt->error . "');</script>";
                    }
                }
            }
            $stmt->close();
        }

        // Preserve form values in case of error
        $editId = $id;
        $editName = $name;
        $editCode = $code;
        $editMobilenum = $mobilenum;
        $editRoleId = $role_id;
    } elseif ($action == 'Delete' && $id > 0) {
        // Debugging: check if delete action and id are received
        error_log("Delete action initiated for ID: " . $id);

        // Delete member
        $stmt = $conn->prepare("DELETE FROM Member WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "<script>alert('Record deleted successfully');</script>";
        } else {
            $error = "<script>alert('Error: " . $stmt->error . "');</script>";
        }
        $stmt->close();
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
            $editRoleId = $row['role_id'];
        }
        $stmt->close();
    }
}

// Fetch roles for the dropdown
$roles = [];
$result = $conn->query("SELECT id, role_name FROM Role");
while ($row = $result->fetch_assoc()) {
    $roles[] = $row;
}
?>

<!DOCTYPE html>

<html>
<head>
    <title>CRUD Operations</title>
</head>
<body>
<!-- <link href="member.css" rel="stylesheet" type="text/css" /> -->
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
    <input type="text" id="mobilenum" name="mobilenum" value="<?php echo htmlspecialchars($editMobilenum); ?>" required pattern="\d{10}" title="Please enter a 10-digit mobile number.">
    <br>
    <label for="role_id">Role:</label>
    <select id="role_id" name="role_id" required>
        <option value="">Select Role</option>
        <?php
        foreach ($roles as $role) {
            $selected = ($role['id'] == $editRoleId) ? "selected" : "";
            echo "<option value='" . htmlspecialchars($role['id']) . "' $selected>" . htmlspecialchars($role['role_name']) . "</option>";
        }
        ?>
    </select>
    <br>
    <?php if (empty($editId)) { ?>
        <input type="submit" name="action" value="Insert">
    <?php } else { ?>
        <input type="submit" name="action" value="Update">
    <?php } ?>
</form>

<h2>Member List</h2>
<form method="GET" action="">
    <label for="filter_role">Filter by Role:</label>
    <select id="filter_role" name="filter_role" onchange="this.form.submit()">
        <option value="">All Roles</option>
        <?php
        foreach ($roles as $role) {
            $selected = (isset($_GET['filter_role']) && $_GET['filter_role'] == $role['id']) ? "selected" : "";
            echo "<option value='" . htmlspecialchars($role['id']) . "' $selected>" . htmlspecialchars($role['role_name']) . "</option>";
        }
        ?>
    </select>
</form>
<table border="2">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Code</th>
        <th>Mobile Number</th>
        <th>Role</th>
        <th>Action</th>
    </tr>
    <?php
    $filter_role = isset($_GET['filter_role']) ? intval($_GET['filter_role']) : 0;
    $query = "SELECT Member.id, Member.name, Member.code, Member.mobilenum, Role.role_name 
              FROM Member 
              LEFT JOIN Role ON Member.role_id = Role.id";
    if ($filter_role > 0) {
        $query .= " WHERE Member.role_id = $filter_role";
    }
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['code']) . "</td>";
        echo "<td>" . htmlspecialchars($row['mobilenum']) . "</td>";
        echo "<td>" . htmlspecialchars($row['role_name']) . "</td>";
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
   
// </form>

    ?>
</table>

</body>
</html>

<?php
$conn->close();
?>

<?php
// Database connection setup
$dbServer = getenv('DB_SERVER'); // Environment variable for server
$dbName = getenv('DB_NAME'); // Environment variable for database name
$dbUser = getenv('DB_USER'); // Environment variable for username
$dbPassword = getenv('DB_PASSWORD'); // Environment variable for password

try {
    // Establish a connection to the Azure SQL Database
    $conn = new PDO("sqlsrv:server=$dbServer;Database=$dbName", $dbUser, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the form is submitted to add a new student
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
        $studentName = $_POST['name'];
        $studentEmail = $_POST['email'];

        // Insert new student into the database
        $sql = "INSERT INTO Students (name, email) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$studentName, $studentEmail]);

        echo "<p>Student added successfully!</p>";
    }

    // Retrieve all students from the database
    $sql = "SELECT * FROM Students";
    $stmt = $conn->query($sql);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>WIT Student Registration</title>
</head>
<body>
    <h1>WIT Student Registration</h1>

    <!-- Form to Add a New Student -->
    <form method="POST">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        <br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <br>
        <button type="submit" name="add_student">Add Student</button>
    </form>

    <h2>Registered Students</h2>
    <!-- Display the list of students -->
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
        </tr>
        <?php if (!empty($students)): ?>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['id']); ?></td>
                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3">No students found.</td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>

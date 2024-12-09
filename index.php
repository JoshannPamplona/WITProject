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

    // Fetch courses for the dropdown
    $courseQuery = "SELECT * FROM Courses";
    $courseStmt = $conn->query($courseQuery);
    $courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);

    // Handle form submission for adding a new student
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
        $studentName = $_POST['name'];
        $studentEmail = $_POST['email'];

        // Insert the new student into the database
        $studentSql = "INSERT INTO Students (name, email) VALUES (?, ?)";
        $studentStmt = $conn->prepare($studentSql);
        $studentStmt->execute([$studentName, $studentEmail]);

        echo "<p>Student added successfully!</p>";
    }

    // Handle form submission for registering a student for a course
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_student'])) {
        $studentId = $_POST['student_id'];
        $courseId = $_POST['course_id'];

        // Insert registration into the Registrations table
        $registrationSql = "INSERT INTO Registrations (student_id, course_id) VALUES (?, ?)";
        $registrationStmt = $conn->prepare($registrationSql);
        $registrationStmt->execute([$studentId, $courseId]);

        echo "<p>Student registered for the course successfully!</p>";
    }

    // Fetch all registrations
    $registrationQuery = "
        SELECT r.id, s.name AS student_name, c.course_name, r.registration_date
        FROM Registrations r
        JOIN Students s ON r.student_id = s.id
        JOIN Courses c ON r.course_id = c.id
    ";
    $registrationStmt = $conn->query($registrationQuery);
    $registrations = $registrationStmt->fetchAll(PDO::FETCH_ASSOC);

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
    <h2>Add a New Student</h2>
    <form method="POST">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        <br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <br>
        <button type="submit" name="add_student">Add Student</button>
    </form>

    <!-- Form to Register a Student for a Course -->
    <h2>Register a Student for a Course</h2>
    <form method="POST">
        <label for="student_id">Student ID:</label>
        <input type="number" id="student_id" name="student_id" required>
        <br>
        <label for="course_id">Course:</label>
        <select id="course_id" name="course_id" required>
            <?php foreach ($courses as $course): ?>
                <option value="<?php echo htmlspecialchars($course['id']); ?>">
                    <?php echo htmlspecialchars($course['course_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br>
        <button type="submit" name="register_student">Register Student</button>
    </form>

    <!-- Display the List of Course Registrations -->
    <h2>Course Registrations</h2>
    <table border="1">
        <tr>
            <th>Registration ID</th>
            <th>Student Name</th>
            <th>Course Name</th>
            <th>Registration Date</th>
        </tr>
        <?php if (!empty($registrations)): ?>
            <?php foreach ($registrations as $registration): ?>
                <tr>
                    <td><?php echo htmlspecialchars($registration['id']); ?></td>
                    <td><?php echo htmlspecialchars($registration['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($registration['course_name']); ?></td>
                    <td><?php echo htmlspecialchars($registration['registration_date']); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">No registrations found.</td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>

<?php
// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include("Connection.php");

    $studentId = $_POST['Student_ID'];
    $firstName = $_POST['First_Name'];
    $gender = $_POST['Gender'];
    $rollNo = $_POST['Roll_No'];
    $universityNo = $_POST['University_No'];
    $departmentId = $_POST['Department_ID'];
    $phoneNo = $_POST['PhoneNo'];
    $major = $_POST['Major'];
    $currentSemester = $_POST['Current_Semester'];
    $email = $_POST['Email'];
    $dateOfBirth = $_POST['Date_Of_Birth'];

    // Prepare and execute the update query
    $stmt = $conn->prepare("UPDATE students SET First_Name = ?, Gender = ?, Roll_No = ?, University_No = ?, Department_ID = ?, PhoneNo = ?, Major = ?, Current_Semester = ?, Email = ?, Date_Of_Birth = ? WHERE Student_ID = ?");
    $stmt->bind_param("ssissssssis", $firstName, $gender, $rollNo, $universityNo, $departmentId, $phoneNo, $major, $currentSemester, $email, $dateOfBirth, $studentId);

    if ($stmt->execute()) {
        echo "Student updated successfully.";
    } else {
        echo "Error updating student: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();

    // Redirect back to student access page or another page
    header("Location: StudentAccess.php");
    exit();
}
?>

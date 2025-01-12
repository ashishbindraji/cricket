<?php
// Database Connection with Exception Handling
try {
    $db = new PDO("mysql:host=localhost;dbname=student", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Insert Student
if (isset($_POST['name'], $_POST['email'])) {
    $stmt = $db->prepare("INSERT INTO `student` (`name`, `email`, `recyclebin`) VALUES (?, ?, 0)");
    $stmt->execute([ucwords($_POST['name']), $_POST['email']]);
}

// Fetch Active Students
$stmt2 = $db->prepare("SELECT * FROM student WHERE recyclebin = 0");
$stmt2->execute();
$data = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Fetch Recycled Students
$stmt3 = $db->prepare("SELECT * FROM student WHERE recyclebin = 1");
$stmt3->execute();
$data2 = $stmt3->fetchAll(PDO::FETCH_ASSOC);

// Edit Student
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM student WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Permanently Delete Student
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM student WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: http://localhost/php/");
    exit();
}

// Move to Recycle Bin
if (isset($_GET['recycle'])) {
    $stmt = $db->prepare("UPDATE student SET recyclebin = 1, recycle_time = NOW() WHERE id = ?");
    $stmt->execute([$_GET['recycle']]);
    header("Location: http://localhost/php/");
    exit();
}

// Restore Student
if (isset($_GET['restore'])) {
    $stmt = $db->prepare("UPDATE student SET recyclebin = 0, recycle_time = NULL WHERE id = ?");
    $stmt->execute([$_GET['restore']]);
    header("Location: http://localhost/php/");
    exit();
}

// Update Student
if (isset($_POST['update_name'], $_POST['update_email'], $_POST['update_id'])) {
    $stmt = $db->prepare("UPDATE student SET name = ?, email = ? WHERE id = ?");
    $stmt->execute([ucwords($_POST['update_name']), $_POST['update_email'], $_POST['update_id']]);
    header("Location: http://localhost/php/");
    exit();
}

// Auto-delete Recycled Students after 1 Minute
$stmt = $db->prepare("DELETE FROM student WHERE recyclebin = 1 AND recycle_time < NOW() - INTERVAL 30 MINUTE");
$stmt->execute();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
</head>

<body>
    <?php if (!isset($_GET['edit'])) { ?>
        <form action="" method="post">
            <h3>Add Student</h3>
            <input type="text" name="name" placeholder="Name" required><br><br>
            <input type="email" name="email" placeholder="Email" required><br><br>
            <button type="submit">Submit</button><br><br>
        </form>
    <?php } else { ?>
        <form action="" method="post">
            <h3>Update Student</h3>
            <input type="hidden" name="update_id" value="<?php echo $editData['id'] ?>">
            <input type="text" name="update_name" placeholder="Name" value="<?php echo $editData['name'] ?>" required><br><br>
            <input type="email" name="update_email" placeholder="Email" value="<?php echo $editData['email'] ?>" required><br><br>
            <button type="submit">Update</button><br><br>
        </form>
    <?php } ?>

    <button><a href="?back=0">Back</a></button>
    <button><a href="?rebin=1">RecycleBin</a></button>
    <?php if (!isset($_GET['rebin'])) { ?>
        <h2>My Students</h2>
        <table border="1" cellpadding="10" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Edit</th>
                <th>Recycle Bin</th>
            </tr>
            <?php $id = 1;
            foreach ($data as $student) { ?>
                <tr>
                    <td><?php echo $id++ ?></td>
                    <td><?php echo $student['name'] ?></td>
                    <td><?php echo $student['email'] ?></td>
                    <td><a href="?edit=<?php echo $student['id'] ?>">Edit</a></td>
                    <td><a href="?recycle=<?php echo $student['id'] ?>">Move-Bin</a></td>
                </tr>
            <?php } ?>
        </table><br>
    <?php } else { ?>
        <h2>Recycled Students</h2>
        <table border="1" cellpadding="10" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Restore</th>
                <th>Delete</th>
            </tr>
            <?php $id = 1;
            foreach ($data2 as $student) { ?>
                <tr>
                    <td><?php echo $id++ ?></td>
                    <td><?php echo $student['name'] ?></td>
                    <td><?php echo $student['email'] ?></td>
                    <td><a href="?restore=<?php echo $student['id'] ?>">Restore</a></td>
                    <td><a href="?delete=<?php echo $student['id'] ?>">Delete</a></td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>
</body>

</html>
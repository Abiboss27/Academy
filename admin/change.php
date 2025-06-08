<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/x-icon" href="assets/images/logo.png">  
    <title>Manage Students</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <link href="https://fonts.googleapis.com/css?family=Poppins:100,200,300,400,500,600,700,800,900" rel="stylesheet">

    <!-- Bootstrap core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="assets/css/fontawesome.css">
    <link rel="stylesheet" href="assets/css/templatemo-edu-meeting.css">
    <link rel="stylesheet" href="assets/css/owl.css">
    <link rel="stylesheet" href="assets/css/lightbox.css">
    <link rel="stylesheet" href="src/css/change.css">
   >
</head>
<body>
    <div class="students-container">
        <h1>Управление студентами</h1>
        
        <?php
        require_once __DIR__ . '/../database.php';
        $sql = "SELECT * FROM users WHERE id_role = 2";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            echo '<table class="students-table">';
            echo '<thead><tr>
                    <th>Фото</th>
                    <th>ФИО</th>
                    <th>Email</th>
                    <th>Дата рождения</th>
                    <th>Действия</th>
                  </tr></thead>';
            echo '<tbody>';
            
            while($student = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>';
                if (!empty($student['Picture_Link'])) {
                    echo '<img src="' . htmlspecialchars($student['Picture_Link']) . '" class="profile-pic" alt="Profile Picture">';
                } else {
                    echo '<img src="assets/images/default-profile.png" class="profile-pic" alt="Default Profile">';
                }
                echo '</td>';
                echo '<td>' . htmlspecialchars($student['FullName']) . '</td>';
                echo '<td>' . htmlspecialchars($student['email']) . '</td>';
                echo '<td>' . htmlspecialchars($student['BirthDate']) . '</td>';
                echo '<td>
                        <form method="post" action="update_role.php" style="display:inline;">
                            <input type="hidden" name="user_id" value="' . $student['id'] . '">
                            <button type="submit" class="action-btn" name="make_teacher">Сделать преподавателем</button>
                        </form>
                      </td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<div class="no-students">Нет студентов для отображения</div>';
        }
        
        // Close connection
        $conn->close();
        ?>
    </div>
</body>
</html>
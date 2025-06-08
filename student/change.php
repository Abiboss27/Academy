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
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --light-color: #f9f9f9;
            --dark-color: #333;
            --gray-color: #666;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background-color: var(--light-color);
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
        }
        
        .logo span {
            color: var(--secondary-color);
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav ul li {
            margin-left: 20px;
        }
        
        nav ul li a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        nav ul li a:hover {
            color: var(--secondary-color);
        }
        
        body {
            background-image: url("https://images.unsplash.com/photo-1637249805971-59d7b9319df3?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D");
            background-size: cover;
        }
        
        .students-container {
            margin-top: 20px;
            margin-bottom: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
            padding: 40px;
        }
        
        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
        }
        
        .students-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .students-table th, .students-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .students-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .students-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .profile-pic {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .action-btn {
            padding: 6px 20px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .action-btn:hover {
            background-color: #3a7bc8;
        }
        
        .no-students {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
    </style>
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
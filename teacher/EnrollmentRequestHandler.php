<?php
class EnrollmentRequestHandler
{
    private $conn;
    private $userId;

    public function __construct(mysqli $conn, int $userId)
    {
        $this->conn = $conn;
        $this->userId = $userId;
    }

    // Vérifie si l'utilisateur est authentifié
    public function isAuthenticated(): bool
    {
        return !empty($this->userId);
    }

    // Supprime une inscription si elle appartient à l'utilisateur
    public function deleteEnrolment(int $id_enrol): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT e.id FROM enrol e JOIN courses c ON e.id_course = c.id WHERE e.id = ? AND c.id_users = ?"
        );
        $stmt->bind_param("ii", $id_enrol, $this->userId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $delete_stmt = $this->conn->prepare("DELETE FROM enrol WHERE id = ?");
            $delete_stmt->bind_param("i", $id_enrol);
            $delete_stmt->execute();
            $delete_stmt->close();
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    }

 public function getEnrolments($search = '')
{
    $query = "SELECT e.id as id_enrol, u.FullName,e.id_user, c.id as id_course, c.title, rating, comments, e.date_added, CA.name as category
              FROM enrol e
              JOIN users u ON e.id_user = u.id 
              JOIN courses c ON e.id_course = c.id
              JOIN categories CA ON c.id_category = CA.id
              WHERE c.id_users = ?";
    
    $params = [$this->userId];
    $types = "i";
    
    if (!empty($search)) {
        $query .= " AND (u.FullName LIKE ? OR c.title LIKE ? OR CA.name LIKE ?)";
        $searchTerm = "%" . $search . "%";
        array_push($params, $searchTerm, $searchTerm, $searchTerm);
        $types .= "sss";
    }
    
    $query .= " ORDER BY FullName ASC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result();
}

    public function getLessonById(int $lessonId)
    {
        $query = "SELECT l.* FROM lessons l
                  JOIN courses c ON l.id_course = c.id
                  WHERE l.id = ? AND c.id_users = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $lessonId, $this->userId);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function deleteLesson(int $lessonId): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT l.id FROM lessons l JOIN courses c ON l.id_course = c.id WHERE l.id = ? AND c.id_users = ?"
        );
        $stmt->bind_param("ii", $lessonId, $this->userId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $delete_stmt = $this->conn->prepare("DELETE FROM lessons WHERE id = ?");
            $delete_stmt->bind_param("i", $lessonId);
            $delete_stmt->execute();
            $delete_stmt->close();
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    }

    public function getLessons()
    {
        $query = "SELECT l.id as id_lesson, c.title as course, s.title as section, l.title as lesson, l.attachment, l.video_url, 
                         l.date_added, l.last_modified 
                  FROM lessons l
                  JOIN courses c ON l.id_course = c.id 
                  JOIN section s ON l.id_section = s.id  
                  WHERE c.id_users = ?
                  ORDER BY course ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getTests(){
        $query = "
            SELECT t.id as test_id, t.title as test_title, se.title as section_name, t.id_section, c.title as course_title
            FROM tests t
            JOIN courses c ON t.id_course = c.id
            JOIN section se ON t.id_section = se.id
            WHERE c.id_users = ?
            ORDER BY c.title ASC, se.title ASC, t.title ASC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        return $testsResult = $stmt->get_result();
    }
    
    public function getAllUsersProgressByCourse(int $course_id)
    {
        $progressList = [];

        // Получаем всех пользователей, записанных на курс
        $enrolStmt = $this->conn->prepare("SELECT DISTINCT id_user FROM enrol WHERE id_course = ?");
        $enrolStmt->bind_param('i', $course_id);
        $enrolStmt->execute();
        $enrolResult = $enrolStmt->get_result();

        // Получаем общее количество разделов курса один раз
        $sectionStmt = $this->conn->prepare("SELECT COUNT(*) as total FROM section WHERE id_course = ?");
        $sectionStmt->bind_param('i', $course_id);
        $sectionStmt->execute();
        $sectionResult = $sectionStmt->get_result();
        $totalSections = (int)$sectionResult->fetch_assoc()['total'];
        $sectionStmt->close();

        if ($totalSections === 0) {
            // Если разделов нет, возвращаем пустой массив или нули
            while ($row = $enrolResult->fetch_assoc()) {
                $progressList[$row['id_user']] = 0;
            }
            $enrolStmt->close();
            return $progressList;
        }

        // Для каждого пользователя считаем прогресс
        while ($row = $enrolResult->fetch_assoc()) {
            $user_id = (int)$row['id_user'];

            $completedStmt = $this->conn->prepare("
                SELECT COUNT(DISTINCT section_id) as completed
                FROM scores
                WHERE id_users = ? AND course_id = ? AND score >= 75
            ");
            $completedStmt->bind_param('ii', $user_id, $course_id);
            $completedStmt->execute();
            $completedResult = $completedStmt->get_result();
            $completedSections = (int)$completedResult->fetch_assoc()['completed'];
            $completedStmt->close();

            $progress = round(($completedSections / $totalSections) * 100);
            $progressList[$user_id] = $progress;
        }

        $enrolStmt->close();

        return $progressList;
    }

    function getTestsDataByTeacher(int $userId)
    {
        $testsData = [];

        $testsQuery = "
            SELECT 
                u.FullName AS student,
                c.title AS course,
                se.title AS section,
                t.title AS test,
                s.score,
                s.attempt_count,
                s.attempt_date
            FROM scores s
            JOIN users u ON s.id_users = u.id
            JOIN tests t ON s.id_test = t.id
            JOIN courses c ON t.id_course = c.id
            JOIN section se ON t.id_section = se.id
            WHERE c.id_users = ?
            ORDER BY u.FullName ASC, c.title, se.title, t.title
        ";

        $stmt = $this->conn->prepare($testsQuery);
        if (!$stmt) {
            throw new Exception("Ошибка подготовки запроса: " . $this->conn->error);
        }

        $stmt->bind_param("i", $userId);

        if (!$stmt->execute()) {
            throw new Exception("Ошибка выполнения запроса: " . $stmt->error);
        }

        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception("Ошибка получения результата: " . $this->conn->error);
        }

        while ($row = $result->fetch_assoc()) {
            $testsData[] = $row;
        }

        $stmt->close();

        return $testsData;
    }


}

?>

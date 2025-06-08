<?php
class CourseManager
{
    private $conn;
    private $userId;

    public function __construct(mysqli $conn, int $userId)
    {
        $this->conn = $conn;
        $this->userId = $userId;
    }

    public function getEnrolledCourses(): array
    {
        $enrolledCourses = [];
        $stmt = $this->conn->prepare("SELECT id_course FROM enrol WHERE id_user = ?");
        $stmt->bind_param('i', $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $enrolledCourses[] = $row['id_course'];
        }
        $stmt->close();
        return $enrolledCourses;
    }

    public function getCourses(array $filters = []): mysqli_result
    {
        $where = [];
        $params = [];
        $types = '';

        if (!empty($filters['category'])) {
            $where[] = "c.id_category = ?";
            $params[] = (int)$filters['category'];
            $types .= 'i';
        }

        if (!empty($filters['price_type']) && $filters['price_type'] !== 'all') {
            if ($filters['price_type'] === 'free') {
                $where[] = "c.price = 0";
            } elseif ($filters['price_type'] === 'paid') {
                $where[] = "c.price > 0";
            }
        }

        if (!empty($filters['search'])) {
            $where[] = "(c.title LIKE ? OR c.short_description LIKE ?)";
            $search = "%" . $filters['search'] . "%";
            $params[] = $search;
            $params[] = $search;
            $types .= 'ss';
        }

        $where[] = "c.id_statut = 1";

        $sql = "SELECT c.*, cat.name AS category_name, IFNULL(AVG(e.rating), 0) AS avg_rating, lv.name AS lvl
                FROM courses c
                LEFT JOIN categories cat ON c.id_category = cat.id
                LEFT JOIN enrol e ON c.id = e.id_course
                LEFT JOIN levels lv ON c.id_level = lv.id";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " GROUP BY c.id";

        if (!empty($filters['rating'])) {
            $ratingVal = (int)$filters['rating'];
            $sql .= " HAVING avg_rating >= " . $ratingVal;
        }

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Ошибка подготовки запроса: ' . $this->conn->error);
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt->get_result();
    }

    // ДОБАВЬТЕ ЭТОТ МЕТОД:
    public function getCategories(): mysqli_result
    {
        return $this->conn->query("SELECT * FROM categories");
    }
}

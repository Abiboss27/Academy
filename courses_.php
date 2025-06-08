<?php
// Include database connection
include 'database.php';

// Search functionality
if (isset($_GET['search'])) {
    header("Location: search.php?query=" . urlencode($_GET['search']));
    exit();
}

// Filter parameters
$categoryFilter = isset($_GET['category']) ? intval($_GET['category']) : null;
$priceFilter = isset($_GET['price']) ? $_GET['price'] : null;
$ratingFilter = isset($_GET['rating']) ? intval($_GET['rating']) : null;

// Base query
$query = "SELECT c.id, c.title, c.language, c.price, c.id_statut, c.discount, 
          c.description, l.name AS level, c.Picture_Link, c.requirements, 
          CA.name AS category, CA.id AS category_id,
          AVG(e.rating) as average_rating
          FROM enrol e
          JOIN courses c ON e.id_course= c.id
          JOIN levels l ON c.id_level = l.id 
          JOIN categories CA ON c.id_category = CA.id
          WHERE c.id_statut = 1 ";

// Apply filters
if ($categoryFilter) {
    $query .= " AND CA.id = $categoryFilter";
}

if ($priceFilter === 'free') {
    $query .= " AND c.price = 0";
} elseif ($priceFilter === 'paid') {
    $query .= " AND c.price > 0";
}

$query .= " GROUP BY c.id";

if ($ratingFilter) {
    $query .= " HAVING average_rating >= $ratingFilter";
}

$query .= " ORDER BY c.id DESC";

// Execute query
$result = mysqli_query($conn, $query);

// Get all sub-categories for the filter dropdown
$categoriesQuery = "SELECT id, name FROM categories ORDER BY name";
$categoriesResult = mysqli_query($conn, $categoriesQuery);
$categories = [];
while ($row = mysqli_fetch_assoc($categoriesResult)) {
    $categories[$row['id']] = $row['name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/x-icon" href="assets/images/logo.png">  
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <link href="https://fonts.googleapis.com/css?family=Poppins:100,200,300,400,500,600,700,800,900" rel="stylesheet">

    <title> Courses </title>

    <!-- Bootstrap core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="assets/css/fontawesome.css">
    <link rel="stylesheet" href="assets/css/templatemo-edu-meeting.css">
    <link rel="stylesheet" href="assets/css/owl.css">
    <link rel="stylesheet" href="assets/css/lightbox.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<header>
        <div class="container header-container">
            <div class="logo">ABIBOSS <span>ACADEMY</span></div>
            <nav>
                <ul>
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="login.html">Вход</a></li>
                    <li ><a href="courses.php">Курсы</a></li> 
                    <li><a href="about_us.html">О нас</a></li>
                </ul>
            </nav>
        </div>
    </header>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог курсов</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .search-bar {
            display: flex;
            width: 50%;
        }
        
        .search-bar input {
            flex-grow: 1;
            padding: 10px;
            border: none;
            border-radius: 4px 0 0 4px;
            font-size: 16px;
        }
        
        .search-bar button {
            padding: 10px 15px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
        
        .filters {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .filter-group {
            margin-bottom: 15px;
        }
        
        .filter-group h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .filter-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .filter-option {
            background-color: #f0f0f0;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .filter-option:hover, .filter-option.active {
            background-color: #3498db;
            color: white;
        }
        
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .course-card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
        }
        
        .course-image {
            height: 160px;
            background-size: cover;
            background-position: center;
        }
        
        .course-content {
            padding: 20px;
        }
        
        .course-category {
            display: inline-block;
            background-color: #e74c3c;
            color: white;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        .course-title {
            font-size: 20px;
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        
        .course-description {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .course-level {
            background-color: #ecf0f1;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 12px;
        }
        
        .course-price {
            font-weight: bold;
            font-size: 18px;
            color: #2c3e50;
        }
        
        .course-price.free {
            color: #27ae60;
        }
        
        .course-price.discounted {
            display: flex;
            align-items: center;
        }
        
        .original-price {
            text-decoration: line-through;
            color: #7f8c8d;
            font-size: 14px;
            margin-right: 8px;
        }
        
        .rating {
            color: #f39c12;
            margin-bottom: 15px;
        }
        
        .enroll-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .enroll-btn:hover {
            background-color: #2980b9;
        }
        
        .enroll-btn.enrolled {
            background-color: #27ae60;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 10px;
        }
        
        .page-btn {
            padding: 8px 15px;
            background-color: #ecf0f1;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .page-btn.active {
            background-color: #3498db;
            color: white;
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-content">
            <h1>Каталог курсов</h1>
            <div class="search-bar">
                <input type="text" placeholder="Поиск курсов...">
                <button><i class="fas fa-search"></i></button>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="filters">
            <div class="filter-group">
                <h3>Цена</h3>
                <div class="filter-options">
                    <div class="filter-option active">Все</div>
                    <div class="filter-option">Бесплатные</div>
                    <div class="filter-option">Платные</div>
                </div>
            </div>
            
            <div class="filter-group">
                <h3>Категории</h3>
                <div class="filter-options" id="category-filters">
                    <!-- Категории будут загружены через JavaScript -->
                </div>
            </div>
            
            <div class="filter-group">
                <h3>Рейтинг</h3>
                <div class="filter-options">
                    <div class="filter-option active">Все</div>
                    <div class="filter-option"><i class="fas fa-star"></i> 4+</div>
                    <div class="filter-option"><i class="fas fa-star"></i> 3+</div>
                    <div class="filter-option"><i class="fas fa-star"></i> 2+</div>
                </div>
            </div>
        </div>
        
        <div class="courses-grid" id="courses-container">
            <!-- Курсы будут загружены через JavaScript -->
        </div>
        
        <div class="pagination">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">3</button>
            <button class="page-btn">></button>
        </div>
    </div>
    
    <script>
        // Здесь будет JavaScript код для загрузки данных и взаимодействия
        document.addEventListener('DOMContentLoaded', function() {
            // Загрузка категорий
            loadCategories();
            
            // Загрузка курсов
            loadCourses();
            
            // Обработчики событий для фильтров
            setupFilterHandlers();
        });
        
        function loadCategories() {
            // В реальном приложении здесь был бы AJAX запрос к серверу
            const categories = [
                {id: 1, name: "Программирование"},
                {id: 2, name: "Дизайн"},
                {id: 3, name: "Маркетинг"},
                {id: 4, name: "Бизнес"},
                {id: 5, name: "Фотография"}
            ];
            
            const container = document.getElementById('category-filters');
            container.innerHTML = '<div class="filter-option active">Все</div>';
            
            categories.forEach(category => {
                const option = document.createElement('div');
                option.className = 'filter-option';
                option.textContent = category.name;
                option.dataset.categoryId = category.id;
                container.appendChild(option);
            });
        }
        
        function loadCourses() {
            // В реальном приложении здесь был бы AJAX запрос к серверу
            const courses = [
                {
                    id: 1,
                    title: "Основы JavaScript",
                    category: "Программирование",
                    short_description: "Изучите основы JavaScript для веб-разработки",
                    price: 0,
                    discount: 0,
                    level: "Начинающий",
                    rating: 4.5,
                    enrolled: false,
                    image: "https://via.placeholder.com/300x160?text=JavaScript"
                },
                {
                    id: 2,
                    title: "Photoshop для начинающих",
                    category: "Дизайн",
                    short_description: "Освойте Adobe Photoshop с нуля",
                    price: 29.99,
                    discount: 19.99,
                    level: "Начинающий",
                    rating: 4.2,
                    enrolled: true,
                    image: "https://via.placeholder.com/300x160?text=Photoshop"
                },
                {
                    id: 3,
                    title: "Продвинутый Python",
                    category: "Программирование",
                    short_description: "Углубленное изучение Python для опытных разработчиков",
                    price: 49.99,
                    discount: 0,
                    level: "Продвинутый",
                    rating: 4.8,
                    enrolled: false,
                    image: "https://via.placeholder.com/300x160?text=Python"
                },
                {
                    id: 4,
                    title: "SMM стратегии",
                    category: "Маркетинг",
                    short_description: "Как эффективно продвигать бизнес в социальных сетях",
                    price: 39.99,
                    discount: 29.99,
                    level: "Средний",
                    rating: 3.9,
                    enrolled: false,
                    image: "https://via.placeholder.com/300x160?text=SMM"
                }
            ];
            
            const container = document.getElementById('courses-container');
            container.innerHTML = '';
            
            courses.forEach(course => {
                const card = document.createElement('div');
                card.className = 'course-card';
                
                let priceHtml;
                if (course.price === 0) {
                    priceHtml = `<div class="course-price free">Бесплатно</div>`;
                } else if (course.discount > 0) {
                    priceHtml = `
                        <div class="course-price discounted">
                            <span class="original-price">$${course.price.toFixed(2)}</span>
                            $${course.discount.toFixed(2)}
                        </div>
                    `;
                } else {
                    priceHtml = `<div class="course-price">$${course.price.toFixed(2)}</div>`;
                }
                
                // Генерация звезд рейтинга
                const fullStars = Math.floor(course.rating);
                const hasHalfStar = course.rating % 1 >= 0.5;
                let starsHtml = '';
                
                for (let i = 1; i <= 5; i++) {
                    if (i <= fullStars) {
                        starsHtml += '<i class="fas fa-star"></i>';
                    } else if (i === fullStars + 1 && hasHalfStar) {
                        starsHtml += '<i class="fas fa-star-half-alt"></i>';
                    } else {
                        starsHtml += '<i class="far fa-star"></i>';
                    }
                }
                
                starsHtml += ` <span>(${course.rating.toFixed(1)})</span>`;
                
                card.innerHTML = `
                    <div class="course-image" style="background-image: url('${course.image}')"></div>
                    <div class="course-content">
                        <span class="course-category">${course.category}</span>
                        <h3 class="course-title">${course.title}</h3>
                        <p class="course-description">${course.short_description}</p>
                        <div class="course-meta">
                            <span class="course-level">${course.level}</span>
                            ${priceHtml}
                        </div>
                        <div class="rating">${starsHtml}</div>
                        <button class="enroll-btn ${course.enrolled ? 'enrolled' : ''}" data-course-id="${course.id}">
                            ${course.enrolled ? 'Записан' : 'Записаться'}
                        </button>
                    </div>
                `;
                
                container.appendChild(card);
            });
            
            // Добавляем обработчики событий для кнопок записи
            document.querySelectorAll('.enroll-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const courseId = this.dataset.courseId;
                    enrollToCourse(courseId, this);
                });
            });
        }
        
        function setupFilterHandlers() {
            // Обработчики для фильтров цены
            document.querySelectorAll('.filter-options .filter-option').forEach(option => {
                option.addEventListener('click', function() {
                    // Удаляем активный класс у всех опций в этой группе
                    this.parentNode.querySelectorAll('.filter-option').forEach(opt => {
                        opt.classList.remove('active');
                    });
                    
                    // Добавляем активный класс к выбранной опции
                    this.classList.add('active');
                    
                    // Здесь должна быть логика фильтрации курсов
                    // В реальном приложении это был бы запрос к серверу с параметрами фильтрации
                });
            });
        }
        
        function enrollToCourse(courseId, buttonElement) {
            // В реальном приложении здесь был бы AJAX запрос к серверу
            console.log(`Запись на курс с ID: ${courseId}`);
            
            // Имитация успешной записи
            buttonElement.textContent = 'Записан';
            buttonElement.classList.add('enrolled');
            
            // Можно добавить уведомление
            alert('Вы успешно записаны на курс!');
        }
    </script>
</body>
</html>
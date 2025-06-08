<?php
include 'C:/xampp/htdocs/Academy/database.php'; // Assuming your database connection is in config.php

// Get course ID from URL
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch course details
$course_query = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$course_query->bind_param("i", $course_id);
$course_query->execute();
$course = $course_query->get_result()->fetch_assoc();

if (!$course) {
    die("Course not found");
}

// Fetch category name
$category_query = $conn->prepare("SELECT name FROM categories WHERE id = ?");
$category_query->bind_param("i", $course['id_category']);
$category_query->execute();
$category = $category_query->get_result()->fetch_assoc()['name'];

// Fetch all sections for this course
$sections_query = $conn->prepare("SELECT * FROM section WHERE id_course = ? ORDER BY id");
$sections_query->bind_param("i", $course_id);
$sections_query->execute();
$sections = $sections_query->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch all lessons and tests for each section
foreach ($sections as &$section) {
    // Lessons for this section
    $lessons_query = $conn->prepare("SELECT * FROM lessons WHERE id_section = ? ORDER BY id");
    $lessons_query->bind_param("i", $section['id']);
    $lessons_query->execute();
    $section['lessons'] = $lessons_query->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Tests for this section
    $tests_query = $conn->prepare("SELECT * FROM tests WHERE id_section = ? ORDER BY id");
    $tests_query->bind_param("i", $section['id']);
    $tests_query->execute();
    $section['tests'] = $tests_query->get_result()->fetch_all(MYSQLI_ASSOC);
}
unset($section); // Break the reference

// Determine what to display in the main content area
$content_type = isset($_GET['type']) ? $_GET['type'] : 'intro';
$content_id = isset($_GET['content_id']) ? intval($_GET['content_id']) : 0;
$current_content = null;

if ($content_type === 'lesson' && $content_id) {
    $lesson_query = $conn->prepare("SELECT * FROM lessons WHERE id = ?");
    $lesson_query->bind_param("i", $content_id);
    $lesson_query->execute();
    $current_content = $lesson_query->get_result()->fetch_assoc();
    $current_content['type'] = 'lesson';
} elseif ($content_type === 'test' && $content_id) {
    $test_query = $conn->prepare("SELECT * FROM tests WHERE id = ?");
    $test_query->bind_param("i", $content_id);
    $test_query->execute();
    $current_content = $test_query->get_result()->fetch_assoc();
    $current_content['type'] = 'test';
    
    // For tests, also get the questions
    if ($current_content) {
        $questions_query = $conn->prepare("SELECT * FROM questions WHERE id_test = ?");
        $questions_query->bind_param("i", $content_id);
        $questions_query->execute();
        $current_content['questions'] = $questions_query->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - Learning Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            height: calc(100vh - 56px);
            overflow-y: auto;
            position: sticky;
            top: 56px;
        }
        .section-title {
            cursor: pointer;
            font-weight: 600;
        }
        .lesson-item, .test-item {
            padding-left: 20px;
            cursor: pointer;
        }
        .lesson-item:hover, .test-item:hover {
            background-color: #f8f9fa;
        }
        .active-lesson {
            background-color: #e9ecef;
            font-weight: 500;
        }
        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
        }
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        .test-question {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Learning Platform</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi bi-person-circle"></i> My Profile</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar with course content -->
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="p-3">
                    <h5><?php echo htmlspecialchars($course['title']); ?></h5>
                    <hr>
                    <div class="course-content">
                        <?php foreach ($sections as $section): ?>
                            <div class="section-title mb-2" data-bs-toggle="collapse" href="#section-<?php echo $section['id']; ?>">
                                <i class="bi bi-folder-fill"></i> <?php echo htmlspecialchars($section['title']); ?>
                            </div>
                            <div class="collapse show" id="section-<?php echo $section['id']; ?>">
                                <?php foreach ($section['lessons'] as $lesson): ?>
                                    <div class="lesson-item mb-1 <?php echo ($content_type === 'lesson' && $content_id == $lesson['id']) ? 'active-lesson' : ''; ?>"
                                         onclick="loadLesson(<?php echo $lesson['id']; ?>)">
                                        <i class="bi bi-play-circle"></i> <?php echo htmlspecialchars($lesson['title']); ?>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php foreach ($section['tests'] as $test): ?>
                                    <div class="test-item mb-1 <?php echo ($content_type === 'test' && $content_id == $test['id']) ? 'active-lesson' : ''; ?>"
                                         onclick="loadTest(<?php echo $test['id']; ?>)">
                                        <i class="bi bi-question-circle"></i> <?php echo htmlspecialchars($test['title']); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Main content area -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
                <?php if ($content_type === 'intro' || !$current_content): ?>
                    <!-- Course introduction -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h1 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h1>
                            <p class="text-muted">Category: <?php echo htmlspecialchars($category); ?></p>
                            
                            <?php if ($course['Picture_Link']): ?>
                                <img src="<?php echo htmlspecialchars($course['Picture_Link']); ?>" class="img-fluid mb-3" alt="Course image">
                            <?php endif; ?>
                            
                            <h3>About this course</h3>
                            <div class="mb-4"><?php echo nl2br(htmlspecialchars($course['description'])); ?></div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h4>Requirements</h4>
                                    <ul>
                                        <?php foreach (explode("\n", $course['requirements']) as $req): ?>
                                            <?php if (trim($req)): ?>
                                                <li><?php echo htmlspecialchars(trim($req)); ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h4>Course Details</h4>
                                    <ul>
                                        <li>Language: <?php echo htmlspecialchars($course['language']); ?></li>
                                        <li>Price: $<?php echo number_format($course['price'], 2); ?></li>
                                        <li>Created: <?php echo date('F j, Y', strtotime($course['date_added'])); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($current_content['type'] === 'lesson'): ?>
                    <!-- Lesson content -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h2 class="card-title"><?php echo htmlspecialchars($current_content['title']); ?></h2>
                            
                            <?php if ($current_content['video_url']): ?>
                                <div class="video-container mb-4">
                                    <iframe src="<?php echo htmlspecialchars($current_content['video_url']); ?>" frameborder="0" allowfullscreen></iframe>
                                </div>
                            <?php endif; ?>
                            
                            <div class="lesson-summary mb-4">
                                <h4>Summary</h4>
                                <p><?php echo nl2br(htmlspecialchars($current_content['summary'])); ?></p>
                            </div>
                            
                            <?php if ($current_content['attachment']): ?>
                                <div class="attachments">
                                    <h4>Attachments</h4>
                                    <a href="<?php echo htmlspecialchars($current_content['attachment']); ?>" class="btn btn-outline-primary" download>
                                        <i class="bi bi-download"></i> Download Materials
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif ($current_content['type'] === 'test'): ?>
                    <!-- Test content -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h2 class="card-title"><?php echo htmlspecialchars($current_content['title']); ?></h2>
                            <p class="text-muted">Duration: <?php echo $current_content['duration']; ?></p>
                            
                            <form id="testForm">
                                <?php foreach ($current_content['questions'] as $index => $question): ?>
                                    <div class="test-question">
                                        <h5>Question <?php echo $index + 1; ?></h5>
                                        <p><?php echo htmlspecialchars($question['title']); ?></p>
                                        
                                        <?php 
                                        $options = json_decode($question['options'], true);
                                        $correct_answers = json_decode($question['correct_answers'], true);
                                        ?>
                                        
                                        <?php if ($question['type'] === 'single'): ?>
                                            <?php foreach ($options as $key => $option): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" 
                                                           name="question_<?php echo $question['id_question']; ?>" 
                                                           id="q<?php echo $question['id_question']; ?>_opt<?php echo $key; ?>"
                                                           value="<?php echo htmlspecialchars($option); ?>">
                                                    <label class="form-check-label" for="q<?php echo $question['id_question']; ?>_opt<?php echo $key; ?>">
                                                        <?php echo htmlspecialchars($option); ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php elseif ($question['type'] === 'multiple'): ?>
                                            <?php foreach ($options as $key => $option): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="question_<?php echo $question['id_question']; ?>[]" 
                                                           id="q<?php echo $question['id_question']; ?>_opt<?php echo $key; ?>"
                                                           value="<?php echo htmlspecialchars($option); ?>">
                                                    <label class="form-check-label" for="q<?php echo $question['id_question']; ?>_opt<?php echo $key; ?>">
                                                        <?php echo htmlspecialchars($option); ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php elseif ($question['type'] === 'number'): ?>
                                            <div class="form-group">
                                                <input type="number" class="form-control" 
                                                       name="question_<?php echo $question['id_question']; ?>"
                                                       placeholder="Enter your answer">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                                
                                <button type="submit" class="btn btn-primary">Submit Test</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function loadLesson(lessonId) {
            window.location.href = `course.php?id=<?php echo $course_id; ?>&type=lesson&content_id=${lessonId}`;
        }
        
        function loadTest(testId) {
            window.location.href = `course.php?id=<?php echo $course_id; ?>&type=test&content_id=${testId}`;
        }
        
        // Handle test submission
        document.getElementById('testForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            // Here you would typically send the form data to the server for processing
            alert('Test submitted! In a real application, this would be sent to the server for grading.');
            
            // After submission, you might want to show the results
            // This is just a placeholder - in a real app you'd get the results from the server
            const questions = document.querySelectorAll('.test-question');
            questions.forEach(q => {
                // This is just a simulation - in a real app you'd compare with correct answers from the server
                const inputs = q.querySelectorAll('input[type="radio"], input[type="checkbox"], input[type="number"]');
                inputs.forEach(input => {
                    input.disabled = true;
                    // Simulate correct answers (random for demo)
                    if (Math.random() > 0.5) {
                        input.parentElement.classList.add('text-success');
                    } else {
                        input.parentElement.classList.add('text-danger');
                    }
                });
            });
        });
    </script>
</body>
</html>
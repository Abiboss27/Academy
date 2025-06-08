<?php
  include 'database.php'; // Подключение только один раз!
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <title>ABIBOSS ACADEMY</title>
  <link rel="icon" type="image/x-icon" href="assets/images/logo.png">
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
</head>

<body>

<header>
  <div class="container header-container">
    <div class="logo">ABIBOSS <span>ACADEMY</span></div>
    <nav>
      <ul>
        <li><a href="index.php">Главная</a></li>
        <li><a href="login.html">Вход</a></li>
        <li><a href="courses/index.php">Курсы</a></li>
        <li><a href="about_us.html">О нас</a></li>
      </ul>
    </nav>
  </div>
</header>

<section class="section main-banner" id="top" data-section="section1" style="position: relative; overflow: hidden;">
  <img src="assets/images/pic.jpg" alt="Главный баннер" style="width: 100%; height: auto; display: block;">
  <div class="video-overlay header-text" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <div class="caption">
            <h6>Приветствуем, студенты!</h6>
            <h2>Добро пожаловать в мир онлайн-образования</h2>
            <div class="main-button-red">
              <div><a href="register.html">Присоединяйтесь сейчас!</a></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="services" >
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <div class="owl-service-item owl-carousel">
          <div class="item">
            <div class="icon">
              <img src="assets/images/service-icon-01.png" alt="">
            </div>
            <div class="down-content">
              <h4>Гибкий график - учитесь в любое удобное время</h4>
            </div>
          </div>
          <div class="item">
            <div class="icon">
              <img src="assets/images/service-icon-02.png" alt="">
            </div>
            <div class="down-content">
              <h4>Преподаватели-эксперты с практическим опытом</h4>
            </div>
          </div>
          <div class="item">
            <div class="icon">
              <img src="assets/images/service-icon-03.png" alt="">
            </div>
            <div class="down-content">
              <h4>Интерактивные материалы и современные методики</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="our-courses" id="courses">
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <div class="section-heading">
          <h2>Популярные курсы</h2>
        </div>
      </div>
      <div class="col-lg-12">
        <div class="owl-service-item owl-carousel">
          <?php
          $query = "SELECT c.title, c.language, c.price, c.id_statut, c.discount, l.name AS level, 
                    c.Picture_Link, CA.name AS category 
                    FROM courses c 
                    JOIN levels l ON c.id_level = l.id 
                    JOIN categories CA ON c.id_category = CA.id 
                    WHERE c.id_statut = 1 
                    ORDER BY c.id DESC 
                    LIMIT 5";
          $result = mysqli_query($conn, $query);

          if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
             
              if (!empty($row['Picture_Link']) && strpos($row['Picture_Link'], 'data:image') === 0) {
                $imageSrc = $row['Picture_Link'];
              } else {
                $imageSrc = 'assets/images/default-course.png'; // Фолбек, если нет картинки
              }

              echo '
                <div class="item">
                  <div class="icon">
                    <img src="' . $imageSrc . '" alt="' . htmlspecialchars($row['title']) . '">
                  </div>
                  <div class="down-content" style="text-align: center">
                    <h4><strong>' . htmlspecialchars($row['title']) . '</strong></h4>
                    <p>Категория: ' . htmlspecialchars($row['category']) . '</p>
                    <p>Уровень: ' . htmlspecialchars($row['level']) . '</p>
                    <p>Язык: ' . htmlspecialchars($row['language']) . '</p>
                  </div>
                </div>
              ';
            }
          } else {
            echo '<div class="item"><p>Курсы не найдены.</p></div>';
          }

             function getCount(mysqli $conn, string $query): int {
              $result = $conn->query($query);
              if ($result && $row = $result->fetch_assoc()) {
                  return (int)$row['total'];
              }
              return 0;
          }

          $totalCourses = getCount($conn, "SELECT COUNT(*) as total FROM courses");
          $categories = getCount($conn, "SELECT COUNT(*) as total FROM categories");
          $teachers = getCount($conn, "SELECT COUNT(*) as total FROM users WHERE id_role = 3");
          $activeStudents = getCount($conn, "SELECT COUNT(*) as total FROM users WHERE id_role = 2");
        
          mysqli_close($conn);
          ?>
        </div>
      </div>
    </div>
  </div>
  <div class="button-container">
    <a href="courses/index.php" class="button-red">Все курсы</a>
  </div>
</section>

<section class="our-facts">
  <div class="container">
    <div class="row">
      <div class="col-lg-6">
        <div class="row">
          <div class="col-lg-12">
            <h2>Факты о нашей академии</h2>
          </div>
          <div class="col-lg-6">
            <div class="row">
              <div class="col-12">
                <div class="count-area-content">
                  <div class="count-digit"><?=$activeStudents?></div>
                  <div class="count-title">Количество студентов</div>
                </div>
              </div>
              <div class="col-12">
                <div class="count-area-content">
                  <div class="count-digit"><?=$teachers?></div>
                  <div class="count-title">Количество Преподавателей</div>
                </div>
              </div>
               <div class="col-12">
                <div class="count-area-content">
                  <div class="count-digit"><?=$totalCourses?></div>
                  <div class="count-title">Количество курсов</div>
                </div>
              </div>
               <div class="col-12">
                <div class="count-area-content">
                  <div class="count-digit"><?=$categories?></div>
                  <div class="count-title">Количество категории курсов</div>
                </div>
              </div>
            </div>
          </div>
         
        </div>
      </div>
      <div class="col-lg-6 align-self-center">
        <div>
          <img src="https://images.unsplash.com/photo-1610484826967-09c5720778c7?q=80&w=1770&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Студенты академии">
        </div>
      </div>
    </div>
  </div>
</section>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/isotope.min.js"></script>
<script src="assets/js/owl-carousel.js"></script>
<script src="assets/js/slick-slider.js"></script>
<script src="assets/js/custom.js"></script>
<script>
  //according to loftblog tut
  $('.nav li:first').addClass('active');

  var showSection = function showSection(section, isAnimate) {
    var
      direction = section.replace(/#/, ''),
      reqSection = $('.section').filter('[data-section="' + direction + '"]'),
      reqSectionPos = reqSection.offset().top - 0;

    if (isAnimate) {
      $('body, html').animate({
        scrollTop: reqSectionPos
      }, 800);
    } else {
      $('body, html').scrollTop(reqSectionPos);
    }
  };

  var checkSection = function checkSection() {
    $('.section').each(function () {
      var
        $this = $(this),
        topEdge = $this.offset().top - 80,
        bottomEdge = topEdge + $this.height(),
        wScroll = $(window).scrollTop();
      if (topEdge < wScroll && bottomEdge > wScroll) {
        var
          currentId = $this.data('section'),
          reqLink = $('a').filter('[href*=\\#' + currentId + ']');
        reqLink.closest('li').addClass('active').
        siblings().removeClass('active');
      }
    });
  };

  $('.main-menu, .responsive-menu, .scroll-to-section').on('click', 'a', function (e) {
    e.preventDefault();
    showSection($(this).attr('href'), true);
  });

  $(window).scroll(function () {
    checkSection();
  });
</script>
</body>
</html>

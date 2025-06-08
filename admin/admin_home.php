<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <link rel="stylesheet" href="./src/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>

<body class="hold-transition sidebar-mini">
  <div class="wrapper">
    <?php include './header.php' ?>
    <div class="content-wrapper">
    <?php include './nav.php' ?>

    </div>
  </div>
  <script src="./src/js/jquery.min.js"></script>
  <script src="./src/js/bootstrap.bundle.min.js"></script>
  <script src="./src/js/adminlte.min.js"></script>
</body>

</html>


<script>
$(document).ready(function() {
  $('.menu-link').on('click', function(e) {
    e.preventDefault();
    const url = $(this).data('url');

    $('#main-content').html('<div class="text-center my-4">Загрузка...</div>');

    $.get(url, function(data) {
      $('#main-content').html(data);
    }).fail(function() {
      $('#main-content').html('<div class="text-danger">Ошибка загрузки данных.</div>');
    });
  });
});
</script>



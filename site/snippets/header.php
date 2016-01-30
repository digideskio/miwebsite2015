<!DOCTYPE html>
<html lang="de">

  <?php
    $data = isset($content) ? $content : $page;
  ?>
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/assets/lib/bootstrap/dist/css/bootstrap.css">
  </head>
  <body>

    <header class="page-header container">
      <h1 class="title"><!-- Todo: SEO taugliche Kontruktion entwickeln. -->
        <figure>
          <img src="../../assets/img/mi-logo-2016.svg" alt="Medieninformatik am Campus Gummersbach" class="logo">
        </figure>
      </h1>

      <?php snippet( 'menu', array('uid' => $data->uid(), "content" =>$data)); ?>

    </header>






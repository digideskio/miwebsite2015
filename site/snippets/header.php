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

    <!--script src="https://use.typekit.net/uwc8kbp.js"></script>
    <script>try{Typekit.load({ async: true });}catch(e){}</script-->

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/assets/lib/bootstrap/dist/css/bootstrap.css">
  </head>
  <body>

    <header class="page-header container">
      <h1 class="title"><!-- Todo: SEO taugliche Kontruktion entwickeln. -->
        <figure class="text-xs-center">
          <div class="navbar">
            <img id="mi-box" class="logo" src="../../assets/img/mi-box.svg" alt="Medieninformatik am Campus Gummersbach">
          </div>
          <img id="mi-unten" class="logo" src="../../assets/img/mi-unten.svg" alt="Medieninformatik am Campus Gummersbach">
        </figure>
      </h1>

      <?php snippet( 'menu', array('uid' => $data->uid(), "content" =>$data)); ?>

    </header>






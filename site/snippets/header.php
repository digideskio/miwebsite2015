<!DOCTYPE html>
<html lang="de">

  <?php
    $data = isset($content) ? $content : $page;
    $def_lang = (c::get("lang_layouts")) ? c::get("lang_layouts"): $site->defaultLanguage()->code();
  ?>
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <script src="https://use.typekit.net/uwc8kbp.js"></script>
    <script>try{Typekit.load({ async: true });}catch(e){}</script>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/assets/lib/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
  </head>
  <body>

    <header class="page-header container">
      <div class="header--large">
        <h1 class="title"><!-- Todo: SEO taugliche Kontruktion entwickeln. -->
          <figure class="text-xs-center">
            <img id="mi-box" class="logo" src="/assets/img/mi-box.svg" alt="Medieninformatik am Campus Gummersbach">
            <img id="mi-unten" class="logo" src="/assets/img/mi-unten.svg" alt="Medieninformatik am Campus Gummersbach">
          </figure>
        </h1>

        <?php snippet( 'menu', array('uid' => $data->uid(), "content" =>$data)); ?>
      </div>

      <div class="header--tiny navbar-fixed-top">
        <h1 class="title"><!-- Todo: SEO taugliche Kontruktion entwickeln. -->
          <figure class="text-xs-center">
            <img id="mi-box-tiny" class="logo" src="/assets/img/mi-box.svg" alt="Medieninformatik am Campus Gummersbach">
          </figure>
        </h1>
      </div>

    </header>

    <?php if($data->uid() != "home" && $data->content($def_lang)->headline_position() != "hide"): ?>
    <div class="container">
        <?php pattern('01-atoms/headline', ['text' => $data->title(), "class" => "main-headline"]); ?>
    </div>
    <?php endif; ?>






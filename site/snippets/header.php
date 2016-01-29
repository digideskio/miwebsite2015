<!DOCTYPE html>

<?php
include c::get('basispfad').'/assets/php/functions.php';
setlocale(LC_TIME, "de_DE");
?>

<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

	<title><?php echo $site->title()->html() ?> | <?php echo $page->title()->html() ?></title>
	<meta name="description" content="<?php echo $site->description()->html() ?>">
	<meta name="keywords" content="<?php echo $site->keywords()->html() ?>">

	<link rel="shortcut icon" href="assets/img/favicon.ico">

	<link type="text/css" rel="stylesheet" href="/assets/lib/blueimp/css/blueimp-gallery.min.css">

    <!-- Core CSS -->
    <?php echo css('assets/css/style.php') ?>
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700' rel='stylesheet' type='text/css'>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script>
		// Picture element HTML5 shiv
		document.createElement( "picture" );
	</script>
	<script src="/assets/lib/picturefill.min.js" async></script>
  </head>


<!--?php snippet('menu') ?-->

<body>
	<nav class="navbar navbar-default navbar-white navbar-static-top" role="navigation">
		<div class="container logo">
			<div class="row">
				<div class="col-md-6 col-sm-2 col-xs-10">
						<a href="/"><img src="<?php echo url('assets/img/bahn_logo.svg') ?>" alt="<?php echo $site->title()->html() ?>" width="120" ></a>	<h1 class="hidden-lg hidden-md hidden-sm"><bold>PXR.</bold></h1>
				</div>



				<div class="col-md-6 col-sm-9 hidden-xs text-right">
					<h1 class="pull-right"><bold>PXR.</bold> Konzernprojekt Reisendeninformation</h1>
				</div>


				<div class="col-sm-1 col-xs-2 hidden-md hidden-lg ">
					<button type="button" id="nav-icon" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-nav" aria-expanded="false">
						<span></span>
						<span></span>
						<span></span>
						<span class="sr-only">Toggle navigation</span>
					</button>
				</div>

		  	</div>
		</div>
		<div id="nav-container">
			<div class="container">

					<div class="collapse navbar-collapse navbar-right" id="main-nav">
						<ul class="nav navbar-nav">

                            <?php foreach($pages->visible() as $item): ?>
                            <li class="navitem <?php ecco($item->isOpen(), '-active') ?>">
                                <a href="<?php echo $item->url() ?>"><?php echo html($item->title()) ?></a></li>
                            <?php endforeach ?>
						</ul>
					</div>
                </div>
			</div>
	</nav>

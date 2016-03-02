<?php

	$data = isset($content) ? $content : $page;

?>

<nav id="main-navigation" class="navigation">

  <ul class="nav">

  <?php foreach($pages->visible() AS $p): ?>
    <li class="nav-item">
      <a class="nav-link" href="/<?=$p->uid();?>"><?= $p->title(); ?></a>
    </li>
  <?php endforeach; ?>

  </ul>

</nav>



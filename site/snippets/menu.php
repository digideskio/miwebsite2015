<?php

	$data = isset($content) ? $content : $page;

?>

<nav id="main-navigation" class="navigation">

  <ul class="nav">

  <?php foreach($pages->visible() AS $p):

    $uid = $p->uid();
    $active = ($p->isOpen()) ? "-active" : "";
    $kinder = $p->children()->visible();

    if($kinder && $kinder->count() > 0): ?>

    <li class="navitem <?=$active;?>">
      <a class="navlink" href="#" type="button" data-toggle="collapse" data-target="#<?=$uid;?>_submenue"><?= $p->title(); ?></a>
        <ul class="subnav collapse" id="<?=$uid;?>_submenue">
        <?php foreach($kinder AS $kind):
          $hide_in_lang = (preg_match("=(1|true|TRUE)=",$kind->hide_in_lang())) ? true : false;
          if($hide_in_lang) continue;
        ?>

          <li class="navitem"><a class="navlink" href="/<?=$kind->uri();?>"><?php echo html($kind->title()); ?></a></li>
        <?php endforeach; ?>
        </ul>
    </li>

  <?php else: ?>
    <li class="navitem <?=$active;?>">
      <a class="navlink" href="/<?=$p->uid();?>"><?= $p->title(); ?></a>
    </li>
  <?php endif; ?>
  <?php endforeach; ?>

  </ul>

</nav>

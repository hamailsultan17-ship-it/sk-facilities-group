<?php
require_once __DIR__ . '/includes/site.php';

$pageTitle = 'Blog – ' . $SITE['legal'];
$pageDesc  = 'Industry insights from ' . $SITE['legal'] . ': practical advice on manned guarding, patrols, CCTV, alarm response and keeping your premises safe.';
$home = 'index.php'; $current = 'blog';
require __DIR__ . '/includes/header.php';

$bg = 'assets/img/about-banner.jpg'; // same banner as About Us page
?>
<main id="main">

<!-- Banner -->
<section class="pagehead pagehead--svc" style="background-image:url('<?= e($bg) ?>')">
  <div class="wrap"><h1>Blog</h1></div>
</section>

<section class="insights blogpage">
  <div class="wrap">
    <p class="insights__kicker">Stay Informed</p>
    <h2>Industry Insights</h2>
    <div class="cards cards--3">
      <?php foreach ($posts as $k => $p): ?>
      <article class="card post">
        <div class="card__img"><?= pic('blog-' . ($k + 1), 'Blog image ' . ($k + 1), $p['title']) ?></div>
        <div class="card__body">
          <?php if (!empty($p['date'])): ?><time class="post__date" datetime="<?= e($p['date']) ?>"><?= e(date('j M Y', strtotime($p['date']))) ?></time><?php endif; ?>
          <h3><?= e($p['title']) ?></h3>
          <p><?= e($p['excerpt']) ?></p>
          <a class="btn btn--sm" href="contact.php#quote">Enquire now<span class="sr"> about <?= e($p['title']) ?></span></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

<?php
require_once __DIR__ . '/includes/site.php';

$pageTitle = 'Jobs – ' . $SITE['legal'];
$pageDesc  = 'Join ' . $SITE['legal'] . '. We are looking for security guards, door supervisors, mobile patrol officers and cleaners who take pride in their work.';
$home = 'index.php'; $current = 'jobs';
require __DIR__ . '/includes/header.php';

$bg = 'assets/img/about-banner.jpg'; // same banner as About Us page
$roles = [
    ['shield', 'Security Guards', 'On-site officers for offices, retail units, construction sites and warehouses.', 'jobs-security-guards'],
    ['door',   'Door Supervisors', 'Front-of-house safety and customer care for bars, clubs and events.', 'jobs-door-supervisors'],
    ['car',    'Mobile Patrol Officers', 'Vehicle patrols, alarm response and lock-up checks across several sites.', 'jobs-mobile-patrol'],
    ['sparkle','Cleaners', 'Commercial, domestic and after-build cleaning teams.', 'jobs-cleaners'],
];
$steps = [
    ['Apply', 'Send us your details and the role you are interested in.'],
    ['We review', 'We read every application and get in touch if your skills and experience match.'],
    ['Interview', 'Meet the team, ask your questions and tell us about yourself.'],
    ['Training', 'If you are successful, we train you properly for the role before you start.'],
];
$mail = 'mailto:' . $SITE['email'] . '?subject=' . rawurlencode('Job application');
?>
<main id="main">

<!-- Banner -->
<section class="pagehead pagehead--svc" style="background-image:url('<?= e($bg) ?>')">
  <div class="wrap"><h1>Jobs</h1></div>
</section>

<!-- Intro -->
<section class="abt">
  <div class="wrap abt__close jobs__intro">
    <h2>Jobs at <?= e($SITE['name']) ?></h2>
    <p>We believe our people make the difference, and our strength lies in the quality of our team. If you take pride in your work, care about customers and want to join a company that values its staff, <?= e($SITE['name']) ?> could be the right place for you.</p>
    <p>We offer good training, a supportive working environment and the chance to build a rewarding career in security and facilities services.</p>
  </div>
</section>

<!-- Roles -->
<section class="abt abt--alt">
  <div class="wrap svcgrid-wrap">
    <h2>Roles we recruit for</h2>
    <div class="svcgrid svcgrid--4">
      <?php foreach ($roles as $r): ?>
      <article class="svcgrid__item jobs__role"><div class="jobs__img"><?= pic($r[3], $r[1], $r[1]) ?></div><?= icon($r[0]) ?><h3><?= e($r[1]) ?></h3><p><?= e($r[2]) ?></p></article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- How to apply -->
<section class="abt">
  <div class="wrap svcgrid-wrap">
    <h2>How to apply</h2>
    <ol class="steps">
      <?php foreach ($steps as $n => $s): ?>
      <li class="steps__item" tabindex="0"><span class="steps__num"><?= $n + 1 ?></span><div><h3><?= e($s[0]) ?></h3><p><?= e($s[1]) ?></p></div></li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<!-- CTA -->
<section class="svccta">
  <div class="wrap">
    <h2>Ready to join the team?</h2>
    <p>Email your CV and the role you are interested in to <a href="mailto:<?= e($SITE['email']) ?>"><?= e($SITE['email']) ?></a>, or send us a message through our contact form. Questions about the process? Call <a href="tel:<?= e($tel) ?>"><?= e($SITE['phone']) ?></a>.</p>
    <p><a class="btn btn--line" href="contact.php#quote">Use the contact form</a></p>
    <p class="jobs__thanks">Thank you for considering <?= e($SITE['name']) ?> as your next step. We look forward to hearing from you.</p>
  </div>
</section>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

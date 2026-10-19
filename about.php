<?php
require_once __DIR__ . '/includes/site.php';

$pageTitle = 'About Us – ' . $SITE['legal'];
$pageDesc  = 'About ' . $SITE['legal'] . ': a privately owned company providing dependable security, cleaning, horticulture and training services for businesses and homes.';
$home = 'index.php'; $current = 'about';
require __DIR__ . '/includes/header.php';

$bg = is_file(__DIR__ . '/assets/img/about-banner.jpg') ? 'assets/img/about-banner.jpg' : 'assets/img/hero-poster.jpg';
?>
<main id="main">

<!-- Banner -->
<section class="pagehead" style="background-image:url('<?= e($bg) ?>')">
  <div class="wrap"><h1>About <?= e($SITE['legal']) ?>.</h1></div>
</section>

<!-- 1. Who we are -->
<section class="abt">
  <div class="wrap abt__row">
    <div class="abt__media"><?= pic('about-1', 'About image', 'Security officer on site') ?></div>
    <div class="abt__text">
      <p><?= e($SITE['name']) ?> is a private company built on a deep commitment to dependable security, cleaning and training services. We were set up in <?= e($SITE['reg_in']) ?> with a clear vision: to become a trusted partner for businesses and homes, supporting commercial, retail, construction and distribution organisations.</p>
      <p>Our approach is customer-centric. We take real pride in delivering services that do what we promise, and we always start by listening to what you need before we recommend a solution.</p>
    </div>
  </div>
</section>

<!-- 2. People -->
<section class="abt abt--alt">
  <div class="wrap abt__row abt__row--flip">
    <div class="abt__media"><?= pic('about-2', 'Our people', 'Cleaning in progress') ?></div>
    <div class="abt__text">
      <p>At <?= e($SITE['name']) ?>, our strength lies in our people. We choose our staff for their professionalism and customer care skills, matching them with our dedication to service excellence. We place a high value on our team and invest in their training, so they can deliver the standard of service our clients expect.</p>
      <p>We understand that security needs change. That is why we keep training up to date, so our team is ready for the changing demands of today&rsquo;s security landscape.</p>
      <p>Our ability to respond quickly to customer needs sets us apart. We tailor our services to the unique needs of each client, creating bespoke solutions that fit your requirements.</p>
    </div>
  </div>
</section>

<!-- 3. How we work -->
<section class="abt">
  <div class="wrap abt__row">
    <div class="abt__media"><?= pic('about-3', 'How we work', 'Member of the team at work') ?></div>
    <div class="abt__text">
      <p>Customer satisfaction is at the core of our operations. At the start of every contract we agree clear expectations with you, and we stay in regular contact so our services keep matching what you need.</p>
      <p>From manned guarding, mobile patrols, CCTV and alarm response to cleaning, horticulture and training courses, you deal with one team that is easy to reach and quick to respond.</p>
      <p><a class="btn" href="index.php#quote">Get a Free Quote</a></p>
    </div>
  </div>
</section>

<!-- Closing line -->
<section class="abt abt--alt">
  <div class="wrap abt__close">
    <p>Choose <?= e($SITE['legal']) ?> for your peace of mind, and a partnership built on trust, quality and commitment.</p>
  </div>
</section>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

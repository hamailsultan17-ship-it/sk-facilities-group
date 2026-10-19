<?php
session_start();
require_once __DIR__ . '/includes/site.php';

$_SESSION['csrf']    = bin2hex(random_bytes(16));
$_SESSION['form_ts'] = time();

$serviceOptions = array_values(array_unique(array_merge(array_column($secNav, 'title'), array_column($sec, 'title'), ['Cleaning Services'], array_column($cleanNav, 'title'), ['Horticulture Services'], array_column($hortiNav, 'title'), ['Training Courses', 'Something else'])));
$video = is_file(__DIR__ . '/assets/video/hero.mp4');
$certs = [
    1 => 'SIA approved contractor status for the provision of security guarding and keyholding.',
    2 => e($SITE['legal']) . ' is an approved Safe Contractor for health and safety.',
    3 => 'ISO approved 9001:2015 which assess the (QMS) Quality Management System.',
    4 => 'Approved BIIAB centre for providing Level 2 Award for working as Security Guarding and Keyholding.',
    5 => 'COP 119 - Code of Practice for Labour Provision - Free Quote',
    6 => 'Associate member of the National Association of Security Dog Users.',
];
$badges = [
    ['staff', 'Highly Trained Staff'], ['response', 'Quick Response'], ['bespoke', 'Bespoke Solutions'],
    ['247', 'Available 24/7 365 days'], ['uk', 'UK Wide Coverage'], ['rates', 'Competitive rates'],
];

$pageTitle = $SITE['name'] . ' – Security, Cleaning & Training Services';
$pageDesc  = $SITE['name'] . ' provides security, cleaning and training services for businesses and homes. Helping your business with reliable, tailored support.';
$home = ''; $current = 'home'; $withSchema = true;
require __DIR__ . '/includes/header.php';
?>
<main id="main">

<!-- 2. HERO: video + centred text -->
<section class="hero">
  <?php if ($video): ?>
  <video class="hero__video" autoplay muted loop playsinline preload="metadata" poster="assets/img/hero-poster.jpg" aria-hidden="true">
    <source src="assets/video/hero.mp4" type="video/mp4">
  </video>
  <?php endif; ?>
  <div class="hero__shade" aria-hidden="true"></div>
  <div class="wrap hero__copy">
    <h1>Secure. Protect. Trust.</h1>
    <p>Comprehensive security, cleaning and training solutions tailored to safeguard your home, business, and peace of mind. Trust in reliability, powered by innovation.</p>
  </div>
</section>

<?php require __DIR__ . '/includes/quote_form.php'; ?>

<!-- 4. CERTIFICATIONS (apni logos yahan lagao) -->
<section class="certs" id="certifications" aria-label="Accreditations">
  <div class="wrap certs__grid">
    <?php foreach ($certs as $n => $line): ?>
    <div class="cert">
      <?= pic("cert-$n", "Certification logo $n", '', 'cert__img') ?>
      <p><?= $line ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- 5. ABOUT -->
<section class="about" id="about">
  <div class="wrap about__grid">
    <div class="about__text">
      <h2>SK Facilities Group</h2>
      <p>Welcome to SK Facilities Group, your trusted partner in providing dependable security, cleaning and training services for homes and businesses. We are a privately-owned company, committed to the safety and peace of mind of clients across commercial, retail, construction and distribution sectors.</p>
      <p>Our growth comes from building bespoke strategies, tailored to each client&rsquo;s needs. We deliver complete solutions that fit smoothly into your operations, so you can focus on your core business with confidence.</p>
      <p>Join us at SK Facilities Group, where helping your business is what we do.</p>
      <p><a class="link" href="about.php">Read more about us</a></p>
    </div>
    <div class="about__img"><?= pic('about-main', 'About image', 'SK Facilities Group team at work') ?></div>
  </div>
</section>

<!-- 6. COMMITTED TO YOUR SAFETY -->
<section class="commit">
  <div class="wrap">
    <h2>Committed to Your Safety</h2>
    <p class="commit__lead">Delivering reliable, round-the-clock protection with expert staff and innovative solutions tailored to your needs.</p>
    <ul class="commit__list">
      <?php foreach ($badges as [$ic, $t]): ?>
      <li><img class="commit__ico" src="assets/img/commit/<?= e($ic) ?>.png" alt="" loading="lazy" width="340" height="250"><span><?= e($t) ?></span></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<!-- 7. OUR SERVICES -->
<section class="services" id="services">
  <div class="wrap">
    <h2>Our Services</h2>
    <div class="svc3">
      <a class="svc" href="#security"><?= pic('service-security', 'Security image', '') ?><h3>Security Services</h3></a>
      <a class="svc" href="#quote" data-pick="Cleaning Services"><?= pic('service-cleaning', 'Cleaning image', '') ?><h3>Cleaning Services</h3></a>
      <a class="svc" href="#quote" data-pick="Training Courses"><?= pic('service-training', 'Training image', '') ?><h3>Training Courses</h3></a>
    </div>
  </div>
</section>

<!-- 7b. HORTICULTURE -->
<section class="horti" id="horticulture">
  <div class="wrap">
    <p class="horti__kicker">Grounds &amp; Greenery</p>
    <h2>Horticulture</h2>
    <p class="horti__lead">Healthy, well-kept outdoor spaces say a lot about a business. Our horticulture team looks after your grounds so they stay safe, tidy and welcoming.</p>
    <div class="horti__grid">
      <?php foreach ($horti as $k => $h): ?>
      <article class="hcard">
        <div class="hcard__img"><?= pic('horticulture-' . ($k + 1), $h[1], $h[1]) ?></div>
        <div class="hcard__body">
          <h3><?= e($h[1]) ?></h3>
          <p><?= e($h[2]) ?></p>
          <a class="link" href="<?= e($hortiNav[$k]['url']) ?>">Learn more<span class="sr"> about <?= e($h[1]) ?></span></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 8. TRUSTED BY LEADING BRANDS -->
<section class="brands">
  <div class="wrap">
    <h2>Trusted by Leading Brands</h2>
    <p class="brands__lead">Ensuring safety and security for respected organisations across industries.</p>
    <ul class="brands__list">
      <?php for ($b = 1; $b <= 4; $b++): ?><li><?= pic("brand-$b", "Client logo $b", '', 'brand__img') ?></li><?php endfor; ?>
    </ul>
  </div>
</section>

<!-- 9. SECURITY SERVICES -->
<section class="security" id="security">
  <div class="wrap">
    <h2>Security Services</h2>
    <p class="security__lead">Your trusted contractor for comprehensive security solutions, including manned guarding, mobile patrols, and CCTV monitoring. Protecting businesses and communities.</p>
    <div class="cards">
      <?php foreach ($sec as $k => $i): ?>
      <article class="card">
        <div class="card__img"><?= pic('security-' . ($k + 1), 'Service image ' . ($k + 1), '') ?></div>
        <div class="card__body">
          <h3><?= e($i['title']) ?></h3>
          <p><?= e($i['blurb']) ?></p>
          <div class="card__links">
            <?php if (isset($secUrl[$i['title']])): ?><a class="link" href="<?= e($secUrl[$i['title']]) ?>">Learn more<span class="sr"> about <?= e($i['title']) ?></span></a><?php endif; ?>
            <a class="link" href="#quote" data-pick="<?= e($i['title']) ?>">Get a quote<span class="sr"> for <?= e($i['title']) ?></span></a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <p class="center"><a class="btn" href="#quote">Get a quick quote now</a></p>
  </div>
</section>

<!-- 10. LET'S TALK + GET IN TOUCH -->
<section class="talk" id="contact">
  <div class="wrap">
    <h2>Let&rsquo;s Talk About Your Security Needs</h2>
    <p class="talk__lead">Take the first step toward safer surroundings. At SK Facilities Group, we&rsquo;re committed to delivering results that exceed expectations. Let&rsquo;s connect today to discuss your needs!</p>
    <div class="talk__grid">
      <div class="talk__info">
        <h3>Get in touch</h3>
        <p>Call us at <a href="tel:<?= e($tel) ?>"><?= e($SITE['phone']) ?></a><br>
           Email us at <a href="mailto:<?= e($SITE['email']) ?>"><?= e($SITE['email']) ?></a></p>
        <p class="talk__addr"><strong><?= e($SITE['legal']) ?></strong><br><?= implode('<br>', array_map('e', $SITE['address'])) ?></p>
        <p class="talk__co">Company number <?= e($SITE['reg_no']) ?><br>Registered in <?= e($SITE['reg_in']) ?></p>
      </div>
      <div class="talk__cta">
        <p>Have a question about our services or need a quote? Click the button below to get started.</p>
        <a class="btn" href="#quote">Get a quick quote now</a>
      </div>
    </div>
  </div>
</section>

<!-- 11. INDUSTRY INSIGHTS -->
<section class="insights" id="insights">
  <div class="wrap">
    <p class="insights__kicker">Stay Informed</p>
    <h2>Industry Insights</h2>
    <div class="slider" data-slider aria-roledescription="carousel" aria-label="Industry insights">
      <div class="slider__viewport" data-slider-viewport>
        <div class="slider__track" data-slider-track>
          <?php foreach ($posts as $k => $p): ?>
          <article class="card post">
            <div class="card__img"><?= pic('blog-' . ($k + 1), 'Blog image ' . ($k + 1), '') ?></div>
            <div class="card__body">
              <?php if (!empty($p['date'])): ?><time class="post__date" datetime="<?= e($p['date']) ?>"><?= e(date('j M Y', strtotime($p['date']))) ?></time><?php endif; ?>
              <h3><?= e($p['title']) ?></h3>
              <p><?= e($p['excerpt']) ?></p>
              <a class="btn btn--sm" href="#quote">Enquire now<span class="sr"> about <?= e($p['title']) ?></span></a>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

<?php
require_once __DIR__ . '/includes/site.php';

$pageTitle = 'Certifications – ' . $SITE['legal'];
$pageDesc  = 'The accreditations and memberships behind ' . $SITE['legal'] . ': SIA, SafeContractor, ISO 9001, BIIAB, COP 119 and NASDU.';
$home = 'index.php'; $current = 'certifications';
require __DIR__ . '/includes/header.php';

$bg = 'assets/img/about-banner.jpg'; // same banner as About Us page

/* Logo = assets/img/cert-N.* (homepage wali logos hi). Link = us body ki website. */
$certList = [
    [1, 'SIA', 'https://www.services.sia.homeoffice.gov.uk/Pages/acs-roac.aspx',
        'SIA approved contractor status for the provision of security guarding and keyholding. The Security Industry Authority (SIA) is the government-appointed body that regulates the UK’s private security industry, so this status shows we meet its standards.'],
    [2, 'SafeContractor', 'https://www.safecontractor.com/en-gb/',
        'Health and safety comes first in everything we do. As an approved SafeContractor, we show that our operations protect our clients, our staff and the public.'],
    [3, 'ISO 9001:2015', 'https://www.british-assessment.co.uk/',
        'ISO 9001:2015 is the international standard for quality management systems (QMS). It means we have a clear, audited way of working that helps our services meet and exceed what clients expect.'],
    [4, 'BIIAB', 'https://biiab.co.uk/',
        'Training and development are the foundation of our service. As an approved BIIAB centre we can offer Level 2 awards for working in the private security industry, so our team stays up to date with best practice.'],
    [5, 'COP 119', 'https://amtivo.com/uk/standards/cop-119/certification/',
        'COP 119 is the Code of Practice for Labour Provision. Certification shows we supply labour responsibly and meet the standards expected of a reputable provider.'],
    [6, 'NASDU', 'https://nasdu.co.uk/',
        'We are an associate company of the National Association of Security Dog Users (NASDU), showing our commitment to professional security dog services and high standards in canine security, training and compliance.'],
];
?>
<main id="main">

<!-- Banner -->
<section class="pagehead pagehead--svc" style="background-image:url('<?= e($bg) ?>')">
  <div class="wrap"><h1>Certifications</h1></div>
</section>

<?php foreach ($certList as $k => $c): ?>
<section class="abt<?= $k % 2 === 1 ? ' abt--alt' : '' ?> certpage">
  <div class="wrap certpage__row<?= $k % 2 === 1 ? ' certpage__row--flip' : '' ?>">
    <a class="certpage__logo" href="<?= e($c[2]) ?>" target="_blank" rel="noopener" aria-label="<?= e($c[1]) ?> (opens in a new tab)"><?= pic('cert-' . $c[0], $c[1] . ' logo', $c[1] . ' logo', 'cert__img') ?></a>
    <div class="certpage__text">
      <h2><?= e($c[1]) ?></h2>
      <p><?= e($c[3]) ?></p>
      <p><a class="link" href="<?= e($c[2]) ?>" target="_blank" rel="noopener">Visit <?= e($c[1]) ?><span class="sr"> (opens in a new tab)</span></a></p>
    </div>
  </div>
</section>
<?php endforeach; ?>

<!-- CTA -->
<section class="svccta">
  <div class="wrap">
    <h2>Work with a company you can trust</h2>
    <p>Want to see our paperwork or ask about our standards? Get in touch and we will be happy to help.</p>
    <p><a class="btn" href="contact.php#quote">Get a Free Quote</a></p>
  </div>
</section>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

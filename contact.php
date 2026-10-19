<?php
session_start();
require_once __DIR__ . '/includes/site.php';

$_SESSION['csrf']    = bin2hex(random_bytes(16));
$_SESSION['form_ts'] = time();

$pageTitle = 'Contact Us – ' . $SITE['legal'];
$pageDesc  = 'Contact ' . $SITE['legal'] . ' for a free, no-obligation quote on security, cleaning, horticulture and training services. Call, email or send us a message.';
$home = 'index.php'; $current = 'contact';
require __DIR__ . '/includes/header.php';

$bg = 'assets/img/banner-contact.jpg';
?>
<main id="main">

<!-- Banner -->
<section class="pagehead pagehead--svc" style="background-image:url('<?= e($bg) ?>')">
  <div class="wrap"><h1>Contact Us</h1></div>
</section>

<!-- Get in touch -->
<section class="cpage">
  <div class="wrap cpage__in" data-form-wrap>
    <div class="cpage__info">
      <h2>Get in touch</h2>
      <p>Call us at <a href="tel:<?= e($tel) ?>"><?= e($SITE['phone']) ?></a><br>
         Email us at <a href="mailto:<?= e($SITE['email']) ?>"><?= e($SITE['email']) ?></a>.</p>
      <p class="cpage__addr"><?= e($SITE['legal']) ?></p>
      <p class="cpage__addr"><?= e($SITE['address'][0]) ?>,<br><?= e($SITE['address'][1]) ?>,</p>
      <p class="cpage__addr"><?= e($SITE['address'][2]) ?></p>
      <p class="cpage__addr"><?= e($SITE['hours']) ?></p>
    </div>

    <div class="cpage__form" id="quote" data-form-panel>
      <form class="qf cform" method="post" action="api/submit.php" novalidate data-contact-form>
        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
        <div class="hp" aria-hidden="true"><label>Leave this empty<input type="text" name="sk_trap_q7" tabindex="-1" autocomplete="off" data-lpignore="true" data-1p-ignore="true"></label></div>
        <div class="qf__alert" role="alert" data-form-alert hidden></div>
        <div class="cform__grid">
          <div class="field"><label class="sr" for="f-name">Name</label>
            <input id="f-name" name="name" type="text" placeholder="Name" autocomplete="name" required maxlength="100"><small class="field__err" data-err-for="name"></small></div>
          <div class="field"><label class="sr" for="f-phone">Phone</label>
            <input id="f-phone" name="phone" type="tel" placeholder="Phone" autocomplete="tel" maxlength="25"><small class="field__err" data-err-for="phone"></small></div>
          <div class="field"><label class="sr" for="f-email">Email</label>
            <input id="f-email" name="email" type="email" placeholder="Email" autocomplete="email" required maxlength="150"><small class="field__err" data-err-for="email"></small></div>
          <div class="field"><label class="sr" for="f-business">Business Name</label>
            <input id="f-business" name="business" type="text" placeholder="Business Name" autocomplete="organization" maxlength="120"></div>
          <div class="field field--full"><label class="sr" for="f-message">Message</label>
            <textarea id="f-message" name="message" rows="5" placeholder="Message" required maxlength="2000"></textarea><small class="field__err" data-err-for="message"></small></div>
        </div>
        <button class="btn" type="submit" data-submit><span data-submit-label>Submit</span></button>
        <div class="qf__success" data-form-done role="status" aria-live="polite" hidden>
          <span class="qf__tick"><?= icon('check') ?></span>
          <strong>Your message has been sent successfully!</strong>
        </div>
      </form>
    </div>
  </div>
</section>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>

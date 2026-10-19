<?php /* Shared footer + chat widget. Needs includes/site.php and $home (set by header.php). */ ?>
<!-- 12. FOOTER -->
<footer class="footer">
  <div class="wrap footer__grid">
    <div><h3>Quick Links</h3><ul>
      <li><a href="<?= $home ?>#top">Home</a></li>
      <li><a href="about.php">About Us</a></li>
      <li><a href="contact.php">Contact Us</a></li>
      <li><a href="certifications.php">Certifications</a></li>
      <li><a href="jobs.php">Jobs</a></li>
      <li><a href="blog.php">Blog</a></li></ul></div>
    <div><h3>Security Services</h3><ul>
      <?php foreach ($secNav as $n): ?><li><a href="<?= e($n['url']) ?>"><?= e($n['title']) ?></a></li><?php endforeach; ?></ul></div>
    <div><h3>Other Services</h3><ul>
      <li><a href="<?= $home ?>#quote" data-pick="Cleaning Services">Cleaning Services</a></li>
      <li><a href="<?= $home ?>#horticulture">Horticulture</a></li>
      <li><a href="<?= $home ?>#quote" data-pick="Training Courses">Training Courses</a></li></ul></div>
    <div><h3>Contact Us</h3><ul class="footer__contact">
      <li><?= icon('phone') ?><a href="tel:<?= e($tel) ?>"><?= e($SITE['phone']) ?></a></li>
      <li><?= icon('mail') ?><a href="mailto:<?= e($SITE['email']) ?>"><?= e($SITE['email']) ?></a></li>
      <li><?= icon('clock') ?><span><?= e($SITE['hours']) ?></span></li>
      <li><?= icon('map') ?><span><?= implode('<br>', array_map('e', $SITE['address'])) ?></span></li></ul></div>
  </div>
  <div class="footer__base wrap">
    <p>&copy; Copyrights <?= date('Y') ?> <?= e($SITE['name']) ?></p>
    <p class="footer__reg"><?= e($SITE['name']) ?> is Registered in <?= e($SITE['reg_in']) ?>. Registered office: <?= e(implode(', ', $SITE['address'])) ?>. Company registration number: <?= e($SITE['reg_no']) ?>.<br>
      <?= e($SITE['reg_type']) ?>, incorporated <?= e($SITE['incorporated']) ?>. Nature of business: <?= e($SITE['sic']) ?>.</p>
  </div>
</footer>

<!-- 13. FLOATING CHAT WIDGET (hamesha screen par) -->
<div class="chat" data-chat>
  <div class="chat__panel" id="chat-panel" role="dialog" aria-label="Chat with us" hidden>
    <div class="chat__head">
      <button class="chat__back" type="button" data-chat-close aria-label="Minimise chat"><svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg></button>
      <div class="chat__title"><strong>Customer Support</strong><span class="chat__online" data-chat-status>We are here to help</span></div>
      <button class="chat__menu-btn" type="button" data-chat-menu-btn aria-label="Chat options" aria-expanded="false"><svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
      <div class="chat__menu" data-chat-menu hidden>
        <button type="button" data-act="name"><svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4L19 9l-4-4L4 16v4z"/></svg>Change Name</button>
        <button type="button" data-act="transcript"><svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>Email transcript</button>
        <button type="button" data-act="sound"><svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 9h4l5-4v14l-5-4H4z"/><path d="M16.5 9a4 4 0 010 6"/></svg><span data-sound-label>Sound On</span></button>
        <button type="button" data-act="new"><svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16v11H9l-5 4V5z"/><path d="M12 8v5M9.5 10.5h5"/></svg>Start new chat</button>
      </div>
    </div>
    <div class="chat__msgs" data-chat-msgs role="log" aria-live="polite">
      <div class="chat__line chat__line--bot"><span class="chat__av" aria-hidden="true"><?= icon('shield') ?></span><div>
        <small class="chat__who">Customer Support</small>
        <p class="chat__msg chat__msg--bot">&#128075; Hi! How can we help?</p></div></div>
      <div class="chat__chips" data-chat-chips></div>
    </div>
    <form class="chat__form" data-chat-form autocomplete="off">
      <p class="chat__err" data-chat-err hidden></p>
      <div class="chat__row">
        <input type="text" name="body" maxlength="1000" placeholder="Type here and press enter.." aria-label="Your message" required>
        <button type="button" class="chat__tool" data-chat-like aria-label="Send thumbs up"><svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 11v9H4v-9h3zM7 11l4-7c1.5 0 2.5 1 2 3l-.5 3H19a2 2 0 012 2.3l-1.2 6A2 2 0 0117.8 20H7"/></svg></button>
        <button type="button" class="chat__tool" data-chat-emoji-btn aria-label="Add emoji"><svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M8.5 14c1 1.5 2.2 2.2 3.5 2.2s2.5-.7 3.5-2.2"/><path d="M9 9.5h.01M15 9.5h.01"/></svg></button>
        <button type="submit" class="chat__send" aria-label="Send message" hidden><?= icon('send') ?></button>
      </div>
      <div class="chat__emojis" data-chat-emojis hidden></div>
    </form>
  </div>
  <p class="chat__pill"><?= icon('shield') ?> <?= e($SITE['name']) ?> Support</p>
  <div class="chat__launch">
    <svg class="chat__arc" viewBox="0 0 120 70" aria-hidden="true"><defs><path id="arc" d="M8 62 C 14 22, 58 -2, 112 22"/></defs>
      <text><textPath href="#arc" startOffset="2%">We Are Here!</textPath></text></svg>
    <span class="chat__wave" aria-hidden="true">👋</span>
    <button class="chat__btn" type="button" aria-expanded="false" aria-controls="chat-panel" aria-label="Open chat"><span class="chat__ic chat__ic--open"><?= icon('chat') ?></span><span class="chat__ic chat__ic--close"><?= icon('chev') ?></span><span class="chat__badge" aria-hidden="true">1</span></button>
    <button class="chat__hide" type="button" aria-label="Hide chat greeting"><?= icon('close') ?></button>
  </div>
</div>

<script src="assets/js/main.js?v=8"></script>
</body>
</html>

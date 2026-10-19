(function () {
  'use strict';

  /* ---------- Mobile menu & dropdowns ---------- */
  var burger = document.querySelector('.burger');
  var nav = document.getElementById('nav');
  function closeMenu() {
    if (!nav) return;
    nav.classList.remove('is-open');
    if (burger) { burger.setAttribute('aria-expanded', 'false'); burger.setAttribute('aria-label', 'Open menu'); }
  }
  if (burger && nav) {
    burger.addEventListener('click', function () {
      var open = nav.classList.toggle('is-open');
      burger.setAttribute('aria-expanded', open);
      burger.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    });
    nav.addEventListener('click', function (e) {
      var a = e.target.closest('a');
      if (a && !a.closest('.has-sub > .nav__link, .nav__toggle')) closeMenu();
      else if (a && window.matchMedia('(min-width:1061px)').matches === false && a.matches('.has-sub > .nav__link')) closeMenu();
    });
  }
  document.querySelectorAll('.nav__toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var li = btn.closest('.has-sub');
      var open = li.classList.toggle('is-open');
      btn.setAttribute('aria-expanded', open);
    });
  });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('.has-sub.is-open').forEach(function (li) { li.classList.remove('is-open'); });
    closeMenu();
    closeChat();
  });

  /* ---------- Hero video: low-power / data-saver par play na kare ---------- */
  var vid = document.querySelector('.hero__video');
  if (vid) {
    var conn = navigator.connection || {};
    if (conn.saveData) { vid.removeAttribute('autoplay'); vid.pause(); }
    else { var pr = vid.play(); if (pr && pr.catch) pr.catch(function () {}); }
  }

  /* ---------- "Get a quote" links preselect the service ---------- */
  document.querySelectorAll('[data-pick]').forEach(function (a) {
    a.addEventListener('click', function () {
      var sel = document.getElementById('f-service');
      if (sel) sel.value = a.getAttribute('data-pick');
      setTimeout(function () { var n = document.getElementById('f-name'); if (n) n.focus({ preventScroll: true }); }, 600);
    });
  });

  /* ---------- Floating chat (api/chat.php): bot jawab foran deta hai, staff online ho to wo bhi ---------- */
  var chat = document.querySelector('[data-chat]');
  var chatBtn = chat && chat.querySelector('.chat__btn');
  var chatPanel = chat && chat.querySelector('.chat__panel');
  var chatState = { token: null, last: 0, unseen: 0, timer: null, sending: false, opened: false };
  var CHAT_KEY = 'sk_chat_token';
  var chatSchedule = function () {};   // chat chalne par neeche set hota hai

  function chatStore(set) {
    try { if (set === null) localStorage.removeItem(CHAT_KEY); else if (set) localStorage.setItem(CHAT_KEY, set); else return localStorage.getItem(CHAT_KEY); } catch (e) {}
    return null;
  }
  function chatBadge() {
    var b = chat.querySelector('.chat__badge');
    if (!b) return;
    if (chatState.token || chatState.opened) { b.textContent = chatState.unseen; b.style.display = chatState.unseen ? '' : 'none'; }
  }
  function openChat() {
    chatPanel.hidden = false; chat.classList.add('is-open');
    chatBtn.setAttribute('aria-expanded', 'true'); chatBtn.setAttribute('aria-label', 'Close chat');
    chatState.opened = true; chatState.unseen = 0; chatBadge();
    var m = chat.querySelector('[data-chat-msgs]'); m.scrollTop = m.scrollHeight;
    setTimeout(function () { var i = chat.querySelector('[name=body]'); if (i) i.focus({ preventScroll: true }); }, 50);
    chatSchedule(true);
  }
  function closeChat() {
    if (!chat || chatPanel.hidden) return;
    chatPanel.hidden = true; chat.classList.remove('is-open');
    chatBtn.setAttribute('aria-expanded', 'false'); chatBtn.setAttribute('aria-label', 'Open chat');
  }

  if (chat) {
    var msgBox = chat.querySelector('[data-chat-msgs]');
    var form = chat.querySelector('[data-chat-form]');
    var chipBox = chat.querySelector('[data-chat-chips]');
    var errBox = chat.querySelector('[data-chat-err]');
    var statusEl = chat.querySelector('[data-chat-status]');
    var sendBtn = chat.querySelector('.chat__send');
    var seen = {};
    var typingEl = null, queue = [], playing = false, playBeep = function () {};
    var MENU = [
      { label: 'I have a question', text: 'I have a question' },
      { label: 'Our services', text: 'What services do you offer?' },
      { label: 'Get a quote', href: '#quote' },
      { label: 'Talk to a person', text: 'I want to talk to a person' }
    ];

    function chatApi(body) {
      return fetch('api/chat.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(body) })
        .then(function (r) { return r.text().then(function (t) { var d = null; try { d = JSON.parse(t); } catch (e) {} return { status: r.status, data: d }; }); });
    }
    function setOnline(on) {
      if (!statusEl) return;
      statusEl.textContent = on ? 'Team online' : 'We reply instantly';
      statusEl.classList.toggle('is-off', !on);
    }
    function showErr(t) { errBox.textContent = t || ''; errBox.hidden = !t; }
    function toBottom() { msgBox.scrollTop = msgBox.scrollHeight; }
    function iconShield() {
      var sv = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
      sv.setAttribute('class', 'ico'); sv.setAttribute('viewBox', '0 0 24 24'); sv.setAttribute('fill', 'none'); sv.setAttribute('stroke', 'currentColor');
      sv.setAttribute('stroke-width', '2'); sv.setAttribute('stroke-linecap', 'round'); sv.setAttribute('stroke-linejoin', 'round'); sv.setAttribute('aria-hidden', 'true');
      sv.innerHTML = '<path d="M12 3l7 3v5c0 4.5-3 8.2-7 10-4-1.8-7-5.5-7-10V6l7-3z"/><path d="M9 12l2 2 4-4"/>';
      return sv;
    }

    /* ---- chips (quick replies) ---- */
    function renderChips(list) {
      chipBox.textContent = '';
      (list || []).forEach(function (c) {
        var el = document.createElement(c.href ? 'a' : 'button');
        el.className = 'chat__chip'; el.textContent = c.label;
        if (c.href) {
          el.href = (c.href.charAt(0) === '#' && !document.querySelector(c.href)) ? 'index.php' + c.href : c.href;   // other pages: go to the landing page section
          if (/^https?:/.test(c.href)) { el.target = '_blank'; el.rel = 'noopener'; }
          else if (c.href.charAt(0) === '#' && document.querySelector(c.href)) el.addEventListener('click', closeChat);
        } else {
          el.type = 'button';
          el.addEventListener('click', function () { sendText(c.text || c.label); });
        }
        chipBox.appendChild(el);
      });
      msgBox.appendChild(chipBox); toBottom();
    }

    /* ---- messages ---- */
    function buildMsg(m) {
      var kind = m.from === 'visitor' ? 'visitor' : (m.from === 'bot' ? 'bot' : 'agent');
      var p = document.createElement('p');
      p.className = 'chat__msg chat__msg--' + kind;
      p.appendChild(document.createTextNode(m.text));
      var t = document.createElement('small'); t.textContent = m.time; p.appendChild(t);
      if (kind === 'visitor') return p;
      var line = document.createElement('div'); line.className = 'chat__line chat__line--' + kind;
      var av = document.createElement('span'); av.className = 'chat__av'; av.appendChild(iconShield());
      var col = document.createElement('div');
      var who = document.createElement('small'); who.className = 'chat__who'; who.textContent = kind === 'agent' ? 'SK Team' : 'Customer Support';
      col.appendChild(who); col.appendChild(p); line.appendChild(av); line.appendChild(col);
      return line;
    }
    function put(m) { msgBox.insertBefore(buildMsg(m), chipBox); toBottom(); }
    function showTyping(on) {
      if (on && !typingEl) {
        typingEl = document.createElement('div'); typingEl.className = 'chat__line chat__line--bot';
        var av = document.createElement('span'); av.className = 'chat__av'; av.appendChild(iconShield());
        var dots = document.createElement('p'); dots.className = 'chat__msg chat__msg--bot chat__typing'; dots.innerHTML = '<i></i><i></i><i></i>';
        typingEl.appendChild(av); typingEl.appendChild(dots);
        msgBox.insertBefore(typingEl, chipBox); toBottom();
      } else if (!on && typingEl) { typingEl.remove(); typingEl = null; }
    }
    // Bot ke jawab "typing..." dikha kar ek ek karke aate hain (natural lagta hai)
    function play(done) {
      if (playing) return; playing = true;
      (function next() {
        var m = queue.shift();
        if (!m) { playing = false; showTyping(false); if (done) done(); return; }
        showTyping(true);
        setTimeout(function () { showTyping(false); put(m); next(); }, Math.min(1400, 500 + m.text.length * 6));
      })();
    }

    function addMsgs(list, fromPoll, animateBot, chips) {
      var add = 0, delayed = false;
      list.forEach(function (m) {
        if (seen[m.id]) return; seen[m.id] = true;
        if (m.id > chatState.last) chatState.last = m.id;
        if (fromPoll && m.from !== 'visitor') add++;
        if (m.from !== 'visitor') playBeep();
        if (animateBot && m.from === 'bot') { queue.push(m); delayed = true; }
        else put(m);
      });
      if (add && chatPanel.hidden) { chatState.unseen += add; chatBadge(); chat.classList.remove('tag-off'); }
      if (delayed) { chipBox.textContent = ''; play(function () { renderChips(chips); }); }
      else if (chips) renderChips(chips);
    }
    function startedUi() { /* naam/email ab chat mein hi likh sakte hain */ }

    function poll() {
      if (!chatState.token) return Promise.resolve();
      return chatApi({ action: 'poll', token: chatState.token, after: chatState.last }).then(function (r) {
        if (!r.data) return;
        if (r.data.reset) { chatState.token = null; chatStore(null); chatState.last = 0; seen = {}; return; }
        if (r.data.ok) { setOnline(r.data.online); addMsgs(r.data.messages, true, false, null); }
      }).catch(function () {});
    }
    // Panel khula ho to har 3 second, band ho to har 20 second (staff ke reply ka badge dikhane ke liye)
    chatSchedule = function (now) {
      clearTimeout(chatState.timer);
      if (!chatState.token) return;
      var run = function () {
        if (document.hidden) { chatState.timer = setTimeout(run, 8000); return; }
        poll().then(function () { chatState.timer = setTimeout(run, chatPanel.hidden ? 20000 : 3000); });
      };
      if (now) run(); else chatState.timer = setTimeout(run, chatPanel.hidden ? 20000 : 3000);
    };

    chatBtn.addEventListener('click', function (e) { e.stopPropagation(); chatPanel.hidden ? openChat() : closeChat(); });
    chat.querySelectorAll('[data-chat-close]').forEach(function (el) { el.addEventListener('click', closeChat); });
    document.addEventListener('click', function (e) {
      // typing ke dauran bahar click se chat band na ho
      if (!e.target.isConnected || chat.contains(e.target)) return;
      var inp = chat.querySelector('[name=body]');
      if (inp && inp.value.trim()) return;
      closeChat();
    });
    var hide = chat.querySelector('.chat__hide');
    if (hide) hide.addEventListener('click', function () { chat.classList.add('tag-off'); });

    function sendText(body) {
      body = (body || '').trim();
      if (!body || chatState.sending) return;
      showErr('');
      chatState.sending = true; sendBtn.disabled = true;
      chipBox.textContent = '';
      var savedName = ''; try { savedName = localStorage.getItem('sk_chat_name') || ''; } catch (er) {}
      chatApi({ action: 'send', token: chatState.token, body: body, name: savedName, after: chatState.last })
        .then(function (r) {
          var d = r.data;
          if (!d) { showErr('Sorry, chat is unavailable right now (code ' + r.status + '). Please call us or use the contact form.'); renderChips(MENU); return; }
          if (d.ok) {
            form.elements.body.value = '';
            if (d.token) { chatState.token = d.token; chatStore(d.token); startedUi(); }
            setOnline(d.online);
            // Bot ne jawab diya to uske chips, warna (staff baat kar raha ho) koi chips nahi
            addMsgs(d.messages, false, true, d.chips && d.chips.length ? d.chips : null);
            chatSchedule(false);
          } else {
            showErr((d.message || 'Could not send. Please try again.') + (d.detail ? ' (' + d.detail + ')' : ''));
            renderChips(MENU);
          }
        })
        .catch(function () { showErr('We could not reach the server. Please check your connection and try again.'); renderChips(MENU); })
        .then(function () { chatState.sending = false; sendBtn.disabled = false; });
    }
    form.addEventListener('submit', function (e) { e.preventDefault(); sendText(form.elements.body.value); });


    /* ---- sound ---- */
    var soundOn = false;
    try { soundOn = localStorage.getItem('sk_chat_sound') === '1'; } catch (e) {}
    var soundLabel = chat.querySelector('[data-sound-label]');
    function syncSound() { if (soundLabel) soundLabel.textContent = soundOn ? 'Sound Off' : 'Sound On'; }
    syncSound();
    var actx = null;
    function beep() {
      if (!soundOn) return;
      try {
        actx = actx || new (window.AudioContext || window.webkitAudioContext)();
        var o = actx.createOscillator(), g = actx.createGain();
        o.type = 'sine'; o.frequency.value = 880; g.gain.value = 0.06;
        o.connect(g); g.connect(actx.destination); o.start(); o.stop(actx.currentTime + 0.12);
      } catch (e) {}
    }
    playBeep = beep;

    /* ---- menu (hamburger) ---- */
    var menu = chat.querySelector('[data-chat-menu]');
    var menuBtn = chat.querySelector('[data-chat-menu-btn]');
    function toggleMenu(open) {
      menu.hidden = !open; menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    menuBtn.addEventListener('click', function (e) { e.stopPropagation(); toggleMenu(menu.hidden); });
    chat.addEventListener('click', function (e) { if (!menu.hidden && !e.target.closest('[data-chat-menu]')) toggleMenu(false); });
    function note(text) { put({ id: 'n' + Date.now(), from: 'bot', text: text, time: '' }); }
    menu.addEventListener('click', function (e) {
      var b = e.target.closest('button[data-act]'); if (!b) return;
      var act = b.getAttribute('data-act'); toggleMenu(false);
      if (act === 'sound') {
        soundOn = !soundOn; syncSound();
        try { localStorage.setItem('sk_chat_sound', soundOn ? '1' : '0'); } catch (er) {}
        if (soundOn) beep();
      } else if (act === 'name') {
        var cur = ''; try { cur = localStorage.getItem('sk_chat_name') || ''; } catch (er) {}
        var nm = window.prompt('What should we call you?', cur);
        if (nm === null) return; nm = nm.trim().slice(0, 100);
        try { localStorage.setItem('sk_chat_name', nm); } catch (er) {}
        if (chatState.token) chatApi({ action: 'rename', token: chatState.token, name: nm }).catch(function () {});
        if (nm) note('Nice to meet you, ' + nm + '!');
      } else if (act === 'transcript') {
        if (!chatState.token) { note('Send a message first, then you can email the chat to yourself.'); return; }
        var em = window.prompt('Send this chat to which email address?');
        if (!em) return; em = em.trim();
        chatApi({ action: 'transcript', token: chatState.token, email: em }).then(function (r) {
          note(r.data && r.data.ok ? 'Done! The chat has been emailed to ' + em + '.' : ((r.data && r.data.message) || 'Sorry, we could not send the email.'));
        }).catch(function () { note('We could not reach the server. Please try again.'); });
      } else if (act === 'new') {
        if (!window.confirm('Start a new chat? This clears the current conversation on your screen.')) return;
        chatState.token = null; chatState.last = 0; seen = {}; queue = []; clearTimeout(chatState.timer); chatStore(null);
        msgBox.querySelectorAll('.chat__line, .chat__msg--visitor').forEach(function (n) { n.remove(); });
        put({ id: 'w' + Date.now(), from: 'bot', text: '👋 Hi! How can we help?', time: '' });
        renderChips(MENU);
      }
    });

    /* ---- thumbs up + emoji ---- */
    var likeBtn = chat.querySelector('[data-chat-like]');
    likeBtn.addEventListener('click', function () { sendText('👍'); });
    var emojiBox = chat.querySelector('[data-chat-emojis]');
    var emojiBtn = chat.querySelector('[data-chat-emoji-btn]');
    ['😊', '😀', '🙂', '🙏', '👍', '👋', '❤️', '😎', '🤝', '👏'].forEach(function (em) {
      var b = document.createElement('button'); b.type = 'button'; b.textContent = em; b.setAttribute('aria-label', 'Emoji ' + em);
      b.addEventListener('click', function () { var i = form.elements.body; i.value += em; i.focus(); });
      emojiBox.appendChild(b);
    });
    emojiBtn.addEventListener('click', function () { emojiBox.hidden = !emojiBox.hidden; });

    renderChips(MENU);

    // Purani chat wapas load (page refresh ke baad bhi)
    var saved = chatStore();
    if (saved && /^[a-f0-9]{32}$/.test(saved)) {
      chatState.token = saved;
      chatApi({ action: 'poll', token: saved, after: 0 }).then(function (r) {
        var d = r.data;
        if (!d) return;
        if (d.reset) { chatState.token = null; chatStore(null); return; }
        if (d.ok) {
          setOnline(d.online);
          if (d.messages.length) { var first = msgBox.querySelector('.chat__line'); if (first) first.remove(); }
          addMsgs(d.messages, false, false, d.messages.length ? MENU.slice(1) : null);
          chatBadge(); chatSchedule(false);
        }
      }).catch(function () {});
    }
  }

  /* ---------- Quote form (AJAX -> api/submit.php) ---------- */
  var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
  var phoneRe = /^[0-9+()\-.\s]{6,25}$/;

  document.querySelectorAll('[data-contact-form]').forEach(function (form) {
    var wrap = form.closest('[data-form-wrap]');
    var done = wrap.querySelector('[data-form-done]');
    var alertBox = form.querySelector('[data-form-alert]');
    var btn = form.querySelector('[data-submit]');
    var label = form.querySelector('[data-submit-label]');

    function setError(name, msg) {
      var field = form.elements[name];
      if (!field) return;
      var box = field.closest('.field');
      var out = box.querySelector('[data-err-for]');
      box.classList.toggle('has-error', !!msg);
      field.setAttribute('aria-invalid', msg ? 'true' : 'false');
      if (out) out.textContent = msg || '';
    }
    function showAlert(msg, detail) {
      alertBox.textContent = msg;
      if (detail) {   // sirf tab jab config.php mein DEBUG = true ho
        var small = document.createElement('small');
        small.style.cssText = 'display:block;margin-top:.4rem;opacity:.85;word-break:break-word';
        small.textContent = 'Debug: ' + detail;
        alertBox.appendChild(small);
      }
      alertBox.hidden = false;
      alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    function clearAll() { clearTimeout(done._t); done.hidden = true; ['name', 'email', 'phone', 'message'].forEach(function (n) { setError(n, ''); }); alertBox.hidden = true; }
    function val(n) { return (form.elements[n].value || '').trim(); }

    function validate() {
      var ok = true, first = null;
      [
        ['name', val('name').length < 2, 'Please enter your name.'],
        ['email', !emailRe.test(val('email')), 'Please enter a valid email address.'],
        ['phone', val('phone') && !phoneRe.test(val('phone')), 'Please enter a valid phone number.'],
        ['message', val('message').length < 5, 'Please tell us a little about what you need.']
      ].forEach(function (r) {
        setError(r[0], r[1] ? r[2] : '');
        if (r[1]) { ok = false; if (!first) first = form.elements[r[0]]; }
      });
      if (first) first.focus();
      return ok;
    }

    ['name', 'email', 'phone', 'message'].forEach(function (n) {
      form.elements[n].addEventListener('input', function () { setError(n, ''); done.hidden = true; });
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearAll();
      if (!validate()) return;

      var payload = {};
      new FormData(form).forEach(function (v, k) { payload[k] = v; });
      btn.disabled = true; label.textContent = 'Sending…';

      fetch(form.getAttribute('action'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(payload)
      })
        .then(function (res) {
          // Server kabhi JSON ke bajaye HTML/error page de to bhi crash na ho
          return res.text().then(function (txt) {
            var data = null;
            try { data = JSON.parse(txt); } catch (err) { /* not JSON */ }
            return { status: res.status, data: data, raw: txt };
          });
        })
        .then(function (r) {
          if (!r.data) {
            if (window.console) console.error('Form: server did not return JSON (HTTP ' + r.status + '):', r.raw.slice(0, 500));
            showAlert('Sorry, something went wrong on our side (code ' + r.status + '). Please try again shortly or call us.');
            return;
          }
          if (r.data.ok) {
            // Form blank ho jata hai (naya message bhejne ke liye tayyar), success message button ke neeche dikhta hai
            form.reset();
            if (r.data.csrf && form.elements.csrf) form.elements.csrf.value = r.data.csrf;   // naya token, taake dobara bhej sakein
            done.hidden = false;
            done.classList.remove('is-in'); void done.offsetWidth; done.classList.add('is-in');
            clearTimeout(done._t); done._t = setTimeout(function () { done.hidden = true; }, 5000);   // 5 second baad khud gayab
            done.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          } else {
            if (r.data.errors) Object.keys(r.data.errors).forEach(function (k) { setError(k, r.data.errors[k]); });
            showAlert(r.data.message || 'Something went wrong. Please try again.', r.data.detail);
          }
        })
        .catch(function (err) {
          if (window.console) console.error('Form: request failed', err);
          showAlert('We could not reach the server. Please check your connection and try again, or call us.');
        })
        .then(function () { btn.disabled = false; label.textContent = 'Submit'; });
    });
  });

  /* ---------- Industry Insights: continuously floating cards (endless loop) ---------- */
  document.querySelectorAll('[data-slider]').forEach(function (root) {
    var viewport = root.querySelector('[data-slider-viewport]');
    var track = root.querySelector('[data-slider-track]');
    var originals = Array.prototype.slice.call(track.children);
    var total = originals.length;
    if (total < 2) return;
    // Visitors who prefer reduced motion simply see the normal card grid
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var SPEED = 55;            // pixels per second - raise for faster, lower for slower
    var x = 0, loopW = 0, raf = null, last = 0, inView = false;

    root.classList.add('is-live');

    // One full set of clones after the real cards: when the last card leaves, the first is already there
    originals.forEach(function (card) {
      var cl = card.cloneNode(true);
      cl.setAttribute('aria-hidden', 'true');
      cl.classList.add('is-clone');
      cl.querySelectorAll('a,button').forEach(function (el) { el.setAttribute('tabindex', '-1'); });
      track.appendChild(cl);
    });

    function measure() {
      var w = viewport.clientWidth;
      root.style.setProperty('--per-view', w >= 900 ? 3 : (w >= 600 ? 2 : 1));
      var gap = parseFloat(getComputedStyle(track).columnGap) || 0;
      loopW = total * (track.children[0].getBoundingClientRect().width + gap);
    }
    function frame(t) {
      if (!inView || document.hidden) { raf = null; return; }
      var dt = last ? Math.min(t - last, 64) : 16;   // cap so a background tab does not cause a jump
      last = t;
      x += SPEED * dt / 1000;
      if (loopW && x >= loopW) x -= loopW;           // seamless wrap back to the first card
      track.style.transform = 'translate3d(' + (-x) + 'px,0,0)';
      raf = requestAnimationFrame(frame);
    }
    function start() { if (!raf) { last = 0; raf = requestAnimationFrame(frame); } }

    document.addEventListener('visibilitychange', function () { if (!document.hidden && inView) start(); });
    var rz;
    window.addEventListener('resize', function () {
      clearTimeout(rz);
      rz = setTimeout(function () { var r = loopW ? x / loopW : 0; measure(); x = r * loopW; }, 120);
    });

    measure();
    // Start floating once the visitor scrolls down to the section; carries on from the same place if they return
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        inView = entries[0].isIntersecting;
        if (inView) start();
      }, { threshold: 0.2 }).observe(root);
    } else { inView = true; start(); }
  });
})();

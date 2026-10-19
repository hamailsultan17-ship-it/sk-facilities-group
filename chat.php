<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
admin_require();
$openId = (int)($_GET['id'] ?? 0);
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Live chat - SK Facilities Group</title><link rel="icon" href="../assets/img/favicon.png"><link rel="stylesheet" href="admin.css">
<style>
.chatadm{display:grid;grid-template-columns:320px 1fr;gap:1rem;height:calc(100vh - 150px);min-height:420px}
.clist,.cview{background:#fff;border:1px solid #d3e0da;border-radius:8px;overflow:hidden;display:flex;flex-direction:column;min-height:0}
.clist h2,.cview__head{margin:0;padding:.8rem 1rem;background:#eef4f0;border-bottom:1px solid #d3e0da;font-size:1rem;color:#0b3d3a}
.clist__rows{overflow:auto;flex:1}
.crow{display:block;width:100%;text-align:left;background:#fff;border:0;border-bottom:1px solid #e3ece8;padding:.7rem 1rem;cursor:pointer;font:inherit;color:inherit}
.crow:hover{background:#fafdfb}.crow.on{background:#e8f3ee}.crow.closed{opacity:.55}
.crow b{display:flex;justify-content:space-between;gap:.5rem}.crow small{color:#56706b;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dot{background:#d62828;color:#fff;border-radius:10px;min-width:20px;padding:0 6px;font-size:.75rem;line-height:20px;text-align:center}
.cview__head{display:flex;justify-content:space-between;align-items:center;gap:.6rem;flex-wrap:wrap}
.cview__head span{font-weight:400;color:#56706b;font-size:.9rem}
.cmsgs{flex:1;overflow:auto;padding:1rem;display:flex;flex-direction:column;gap:.5rem;background:#f8fbf9}
.m{max-width:78%;padding:.55rem .8rem;border-radius:12px;white-space:pre-wrap;word-break:break-word}
.m small{display:block;font-size:.72rem;opacity:.65;margin-top:.15rem}
.m.visitor{align-self:flex-start;background:#fff;border:1px solid #d3e0da;border-bottom-left-radius:3px}
.m.agent{align-self:flex-end;background:#0b3d3a;color:#fff;border-bottom-right-radius:3px}
.m.bot{align-self:center;background:#eef4f0;color:#56706b;font-size:.85rem;text-align:center}
.creply{display:flex;gap:.6rem;padding:.7rem;border-top:1px solid #d3e0da}
.creply textarea{flex:1;resize:none;height:54px;padding:.6rem .7rem;border:1.5px solid #b9cbc4;border-radius:6px;font:inherit}
.empty{margin:auto;color:#56706b;text-align:center;padding:2rem}
.btn.sm{padding:.35rem .7rem;font-size:.85rem;background:#56706b}
@media (max-width:800px){.chatadm{grid-template-columns:1fr;height:auto}.clist__rows{max-height:220px}.cview{height:70vh}}
</style></head>
<body>
<header class="bar"><strong>SK Facilities Group &ndash; Live chat</strong>
<nav><a href="index.php">Enquiries</a><a href="../" target="_blank" rel="noopener">View website</a><a href="logout.php">Log out</a></nav></header>
<main class="wrap">
<p class="muted small" style="margin-top:0">Keep this page open to show visitors that your team is <b>online</b>. New messages appear automatically.</p>
<div class="chatadm">
  <section class="clist"><h2>Conversations <span id="totalUnread"></span></h2><div class="clist__rows" id="rows"><p class="empty">Loading&hellip;</p></div></section>
  <section class="cview">
    <div class="cview__head"><div><strong id="vName">Select a conversation</strong> <span id="vMail"></span></div><button class="btn sm" id="closeBtn" type="button" hidden>Close chat</button></div>
    <div class="cmsgs" id="msgs"><p class="empty">No conversation selected.</p></div>
    <form class="creply" id="reply" hidden><textarea id="replyText" placeholder="Type your reply (Enter to send, Shift+Enter for a new line)" maxlength="2000"></textarea><button class="btn" type="submit">Send</button></form>
  </section>
</div>
</main>
<script>
(function () {
  var cur = <?= $openId ?: 0 ?>, last = 0, lastUnread = -1, baseTitle = document.title;
  var rows = document.getElementById('rows'), msgs = document.getElementById('msgs'), reply = document.getElementById('reply'),
      text = document.getElementById('replyText'), closeBtn = document.getElementById('closeBtn'), curClosed = false;

  function api(body) {
    return fetch('chat_api.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) })
      .then(function (r) { return r.json().catch(function () { return { ok: false, message: 'Bad response' }; }).then(function (d) { d._status = r.status; return d; }); });
  }
  function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
  function beep() { try { var c = new (window.AudioContext || window.webkitAudioContext)(), o = c.createOscillator(), g = c.createGain(); o.connect(g); g.connect(c.destination); o.frequency.value = 880; g.gain.value = .06; o.start(); o.stop(c.currentTime + .15); } catch (e) {} }

  function loadList() {
    api({ action: 'list' }).then(function (d) {
      if (d._status === 401) { location.href = 'login.php'; return; }
      if (!d.ok) return;
      if (lastUnread >= 0 && d.unread > lastUnread) beep();
      lastUnread = d.unread;
      document.title = (d.unread ? '(' + d.unread + ') ' : '') + baseTitle;
      document.getElementById('totalUnread').innerHTML = d.unread ? '<span class="dot">' + d.unread + '</span>' : '';
      if (!d.sessions.length) { rows.innerHTML = '<p class="empty">No chats yet.<br>They will show up here.</p>'; return; }
      rows.innerHTML = d.sessions.map(function (s) {
        return '<button type="button" class="crow' + (s.id === cur ? ' on' : '') + (s.closed ? ' closed' : '') + '" data-id="' + s.id + '"><b><span>' + esc(s.name) + '</span>' +
          (s.unread ? '<span class="dot">' + s.unread + '</span>' : '') + '</b><small>' + esc(s.last) + '</small><small>' + esc(s.time) + (s.closed ? ' &middot; closed' : '') + '</small></button>';
      }).join('');
    });
  }
  rows.addEventListener('click', function (e) { var b = e.target.closest('.crow'); if (b) openChat(parseInt(b.dataset.id, 10)); });

  function addMsgs(list) {
    var atEnd = msgs.scrollHeight - msgs.scrollTop - msgs.clientHeight < 80;
    list.forEach(function (m) {
      var d = document.createElement('div'); d.className = 'm ' + m.from;
      d.innerHTML = esc(m.text) + '<small>' + (m.from === 'agent' ? 'You' : m.from === 'bot' ? 'Auto reply' : 'Visitor') + ' &middot; ' + m.time + '</small>';
      msgs.appendChild(d); last = m.id;
    });
    if (list.length && atEnd) msgs.scrollTop = msgs.scrollHeight;
  }
  function openChat(id) {
    cur = id; last = 0; msgs.innerHTML = ''; history.replaceState(null, '', '?id=' + id);
    pollMsgs(true); loadList();
  }
  function pollMsgs(first) {
    if (!cur) return;
    var id = cur;
    api({ action: 'messages', id: id, after: last }).then(function (d) {
      if (id !== cur || !d.ok) return;
      if (first) {
        document.getElementById('vName').textContent = d.session.name;
        document.getElementById('vMail').innerHTML = d.session.email ? '&lt;' + esc(d.session.email) + '&gt;' : '';
        reply.hidden = false; closeBtn.hidden = false; curClosed = d.session.closed; closeBtn.textContent = curClosed ? 'Reopen chat' : 'Close chat';
      }
      addMsgs(d.messages);
      if (first) msgs.scrollTop = msgs.scrollHeight;
    });
  }
  reply.addEventListener('submit', function (e) {
    e.preventDefault();
    var t = text.value.trim(); if (!t || !cur) return;
    text.disabled = true;
    api({ action: 'reply', id: cur, body: t }).then(function (d) {
      text.disabled = false;
      if (!d.ok) { alert(d.message || 'Could not send.'); return; }
      text.value = ''; text.focus(); pollMsgs(false); loadList();
      if (d.emailed) alert('The visitor had left the page, so your reply was also emailed to them.');
      else if (d.mailError) alert('The visitor had left the page. Your reply was saved but the email could not be sent: ' + d.mailError);
    });
  });
  text.addEventListener('keydown', function (e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); reply.requestSubmit(); } });
  closeBtn.addEventListener('click', function () {
    api({ action: curClosed ? 'reopen' : 'close', id: cur }).then(function () { curClosed = !curClosed; closeBtn.textContent = curClosed ? 'Reopen chat' : 'Close chat'; loadList(); });
  });

  loadList(); if (cur) openChat(cur);
  setInterval(function () { loadList(); pollMsgs(false); }, 3000);
})();
</script>
</body></html>

document.addEventListener('DOMContentLoaded', () => {
  const chat = document.querySelector('.chat');
  const messagesEl = document.querySelector('.messages');
  const form = document.getElementById('send-form') || document.querySelector('.composer form');
  const input = form ? form.querySelector('.input, textarea') : null;

  const meId = document.body.getAttribute('data-user-id') || '';
  const fromPath = (location.pathname.match(/\/messagerie\/(\d+)/) || [])[1] || null;
  let chatUserId = (chat && chat.getAttribute('data-chat-user-id')) || fromPath || null;

  let sendUrl = form && form.getAttribute('action')
    ? form.getAttribute('action')
    : (chatUserId ? `/messagerie/ajax/send/${encodeURIComponent(chatUserId)}` : null);

  let syncUrl = null;
  if (chatUserId) {
    syncUrl = (afterId = 0) => `/messagerie/${encodeURIComponent(chatUserId)}/sync?afterId=${encodeURIComponent(afterId)}`;
  } else if (sendUrl) {
    const m = sendUrl.match(/\/messagerie\/ajax\/send\/(\d+)/);
    if (m) {
      chatUserId = m[1];
      syncUrl = (afterId = 0) => `/messagerie/${encodeURIComponent(chatUserId)}/sync?afterId=${encodeURIComponent(afterId)}`;
    }
  }

  const removePlaceholder = () => {
    messagesEl?.querySelector('[data-role="empty-placeholder"]')?.remove();
  };

  const lastMessageId = () => {
    const last = messagesEl?.querySelector('.message:last-of-type');
    return last ? parseInt(last.getAttribute('data-id') || '0', 10) : 0;
  };

  const atBottom = () => {
    if (!messagesEl) return true;
    const delta = messagesEl.scrollHeight - messagesEl.scrollTop - messagesEl.clientHeight;
    return delta < 30;
  };

  const scrollToBottom = () => {
    if (messagesEl) messagesEl.scrollTop = messagesEl.scrollHeight;
  };

  const appendMessage = ({ id, content, createdAt, senderId }) => {
    if (!messagesEl) return;
    removePlaceholder();
    chat?.classList.add('has-thread');

    const wrap = document.createElement('div');
    const mine = String(senderId) === String(meId);
    wrap.className = 'message ' + (mine ? 'from-me' : 'from-them');
    if (id) wrap.dataset.id = id;

    const bubble = document.createElement('div');
    bubble.className = 'bubble';
    bubble.textContent = content;

    const time = document.createElement('div');
    time.className = 'time';
    time.textContent = createdAt;

    wrap.appendChild(bubble);
    wrap.appendChild(time);
    messagesEl.appendChild(wrap);
  };

  // ----- ENTER => submit -----
  if (form && input) {
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        if (typeof form.requestSubmit === 'function') form.requestSubmit();
        else form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
      }
    });
  }

  if (form && input) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const val = input.value.trim();
      if (!val) return;
      if (!sendUrl) {
        console.warn('[messagerie] Pas d’URL d’envoi détectée');
        return;
      }

      const stick = atBottom();


      appendMessage({
        id: null,
        content: val,
        createdAt: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        senderId: meId
      });
      if (stick) scrollToBottom();
      input.value = '';

      try {
        const res = await fetch(sendUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({ content: val })
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.success) {
          console.error('[messagerie] Échec envoi', res.status, data);
        } else if (data.message) {
          const last = messagesEl?.querySelector('.message:last-of-type');
          if (last && !last.dataset.id) {
            last.dataset.id = data.message.id;
            const t = last.querySelector('.time');
            if (t) t.textContent = data.message.createdAt;
          }
        }
      } catch (err) {
        console.error('[messagerie] Erreur réseau', err);
      }
    });
  }

  let pollTimer = null;

  const fetchNew = async () => {
    if (!syncUrl) return;
    try {
      const res = await fetch(syncUrl(lastMessageId()), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!res.ok) return;
      const data = await res.json();
      if (Array.isArray(data.messages) && data.messages.length) {
        const stick = atBottom();
        for (const m of data.messages) appendMessage(m);
        if (stick) scrollToBottom();
      }
    } catch (e) {
      console.error('[messagerie] Sync error', e);
    }
  };

  if (messagesEl && messagesEl.querySelectorAll('.message[data-id]').length) {
    removePlaceholder();
    chat?.classList.add('has-thread');
    scrollToBottom();
  }
  if (syncUrl) {
    pollTimer = setInterval(fetchNew, 4000);
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) { clearInterval(pollTimer); pollTimer = null; }
      else { fetchNew(); if (!pollTimer) pollTimer = setInterval(fetchNew, 4000); }
    });
  }
});

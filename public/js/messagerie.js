document.addEventListener('DOMContentLoaded', () => {
  const chat = document.querySelector('.chat');
  const contacts = document.querySelectorAll('.contacts .contact');


  contacts.forEach(c => {
    c.addEventListener('click', () => {
      contacts.forEach(x => x.classList.remove('is-active'));
      c.classList.add('is-active');
      chat?.classList.add('has-thread');     
      const messages = chat?.querySelector('.messages');
      if (messages) messages.scrollTop = messages.scrollHeight;
    });
  });


  const input = document.querySelector('.composer .input');
  const sendBtn = document.querySelector('.composer .send');

  function addMessage() {
    const val = (input?.value || '').trim();
    if (!val) return;
    chat.classList.add('has-thread');
    const messages = chat.querySelector('.messages');
    if (!messages) return;

    const wrap = document.createElement('div');
    wrap.className = 'message from-me';
    const bubble = document.createElement('div');
    bubble.className = 'bubble';
    bubble.textContent = val;
    const time = document.createElement('div');
    time.className = 'time';
    const d = new Date();
    const hh = String(d.getHours()).padStart(2,'0');
    const mm = String(d.getMinutes()).padStart(2,'0');
    time.textContent = `${hh}:${mm}`;

    wrap.appendChild(bubble);
    wrap.appendChild(time);
    messages.appendChild(wrap);

    input.value = '';
    messages.scrollTop = messages.scrollHeight;
  }

  input?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); addMessage(); }
  });
  sendBtn?.addEventListener('click', addMessage);
});

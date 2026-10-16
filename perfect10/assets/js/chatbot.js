/* Published-content assistant. No storage, transcripts, API credentials or audio uploads. */
(function () {
  'use strict';
  var root = document.querySelector('[data-p10-chat]');
  if (!root || typeof p10Chat === 'undefined') return;
  var dialog = root.querySelector('dialog');
  var find = function (name) { return root.querySelector('[data-chat-' + name + ']'); };
  var input = root.querySelector('input');
  var log = find('log');
  var status = find('status');
  var busy = false;
  var controller;
  var muted = true;
  var last = '';
  var recognition;
  var flow = null;
  var generation = 0;
  var synth = window.speechSynthesis;
  var follow = find('follow');
  find('provider').textContent = p10Chat.provider === 'openai'
    ? 'Optional OpenAI relevance ranking is enabled: redacted text and public extracts may be sent to OpenAI. No audio or full conversation is sent. Local answers are used if it is unavailable.'
    : 'Local search mode: no messages are sent to an AI provider.';

  function bubble(text, speaker) {
    var el = document.createElement('div');
    el.className = 'p10-chat-message p10-chat-message--' + speaker;
    var label = document.createElement('strong');
    label.textContent = speaker === 'user' ? 'You: ' : 'Assistant: ';
    el.appendChild(label);
    el.appendChild(document.createTextNode(text));
    log.appendChild(el);
    log.scrollTop = log.scrollHeight;
    return el;
  }
  function button(parent, label, callback) {
    var el = document.createElement('button');
    el.type = 'button'; el.textContent = label;
    el.addEventListener('click', callback); parent.appendChild(el); return el;
  }
  function stopVoice() {
    if (synth) synth.cancel();
    if (recognition) { try { recognition.abort(); } catch (e) { /* Already stopped. */ } }
    find('mic').textContent = 'Use microphone';
  }
  function speak(text) {
    if (!synth || muted || !text) return;
    synth.cancel();
    var speech = new SpeechSynthesisUtterance(text);
    speech.lang = document.documentElement.lang || 'en';
    speech.onerror = function () { status.textContent = 'Voice output unavailable; please read the text.'; };
    synth.speak(speech);
  }
  function reset() {
    generation++; if (controller) controller.abort(); stopVoice();
    flow = null; busy = false; last = ''; input.value = ''; input.type = 'text';
    input.maxLength = 600; log.replaceChildren(); status.textContent = '';
    follow.hidden = true;
    bubble('How can I help you find published profiles, locations or website information?', 'assistant');
  }
  find('open').addEventListener('click', function () {
    if (typeof dialog.showModal !== 'function') {
      status.textContent = 'Your browser does not support this chat dialog.';
      window.location.href = p10Chat.root.replace(/\/wp-json\/.*$/, '/'); return;
    }
    dialog.showModal(); find('open').setAttribute('aria-expanded', 'true'); input.focus();
  });
  find('close').addEventListener('click', function () { dialog.close(); });
  dialog.addEventListener('close', function () {
    generation++; if (controller) controller.abort(); busy = false; flow = null;
    input.value = ''; stopVoice(); find('open').setAttribute('aria-expanded', 'false'); find('open').focus();
    // Contact details collected for an unfinished follow-up are held only in memory and cleared on close.
    log.replaceChildren(); bubble('How can I help you find public website information?', 'assistant');
  });
  find('stop').addEventListener('click', stopVoice);
  find('reset').addEventListener('click', function () { reset(); input.focus(); });
  find('repeat').addEventListener('click', function () { if (!muted) speak(last); else status.textContent = 'Enable voice responses first, or read the last response above.'; });
  find('mute').addEventListener('click', function () {
    muted = !muted; if (muted) stopVoice();
    find('mute').textContent = muted ? 'Voice muted' : 'Voice enabled';
    find('mute').setAttribute('aria-pressed', String(muted));
  });
  if (!synth) { find('mute').disabled = true; find('repeat').disabled = true; }
  var Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (Recognition) {
    find('mic').hidden = false;
    recognition = new Recognition(); recognition.interimResults = false;
    recognition.lang = document.documentElement.lang || 'en';
    recognition.onresult = function (event) {
      input.value = event.results[0][0].transcript.slice(0, 600);
      status.textContent = 'Review the recognized text, then press Send.'; input.focus();
    };
    recognition.onerror = function () { status.textContent = 'Microphone unavailable or permission denied. Please type instead.'; };
    recognition.onend = function () { find('mic').textContent = 'Use microphone'; };
    find('mic').addEventListener('click', function () {
      if (busy || flow) { status.textContent = 'Please finish the current request using text.'; return; }
      try { recognition.start(); find('mic').textContent = 'Listening…'; status.textContent = 'Browser speech service listening. Stop voice cancels.'; }
      catch (e) { status.textContent = 'Microphone already active or unavailable. Use text instead.'; }
    });
  }
  async function request(path, payload) {
    controller = new AbortController();
    var timeout = setTimeout(function () { controller.abort(); }, 22000);
    try {
      var response = await fetch(p10Chat.root + path, {
        method: 'POST', credentials: 'same-origin', signal: controller.signal,
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': p10Chat.nonce },
        body: JSON.stringify(payload)
      });
      var data = await response.json();
      if (!response.ok) throw new Error(data.message || 'Request failed.');
      return data;
    } finally { clearTimeout(timeout); }
  }
  function sourceLink(parent, url, title) {
    try {
      var parsed = new URL(url, window.location.href);
      if (!/^https?:$/.test(parsed.protocol) || parsed.origin !== window.location.origin) return;
      var a = document.createElement('a'); a.href = parsed.href; a.textContent = title;
      parent.appendChild(a);
    } catch (e) { /* Invalid server-provided URL is not rendered. */ }
  }
  function collect(text) {
    if (flow.step === 'consent' || flow.step === 'confirm') {
      bubble('Choose the consent/confirmation button, or reset to cancel. Nothing has been saved.', 'assistant'); return;
    }
    if (flow.step === 'name') {
      if (text.length > 80) { bubble('Please keep the name to 80 characters.', 'assistant'); return; }
      flow.data.name = text; flow.step = 'contact'; bubble('What email address or phone number should the administrator use? Provide just one.', 'assistant');
    } else if (flow.step === 'contact') {
      if (text.indexOf('@') >= 0 && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(text) && text.length <= 150) flow.data.email = text;
      else if (/^\+?[0-9 ()-]{7,40}$/.test(text)) flow.data.phone = text;
      else { bubble('Please enter a valid email address or phone number, or reset to cancel.', 'assistant'); return; }
      flow.step = 'location'; bubble('Which profile or location is this about? Type “skip” if none. This is context only, not a booking.', 'assistant');
    } else if (flow.step === 'location') {
      if (text.length > 120) { bubble('Please use no more than 120 characters.', 'assistant'); return; }
      flow.data.location = text.toLowerCase() === 'skip' ? '' : text;
      flow.step = 'message'; bubble('Please type a short enquiry (600 characters maximum). Do not include sensitive information.', 'assistant');
    } else if (flow.step === 'message') {
      flow.data.message = text; flow.step = 'confirm';
      var generationAtConfirmation = generation;
      var el = bubble('Save this name, contact detail, profile/location context and enquiry for administrator follow-up? No chat transcript will be saved. Reset cancels.', 'assistant');
      button(el, 'Consent and save enquiry', async function () {
        if (!flow || flow.step !== 'confirm' || busy || generation !== generationAtConfirmation) return;
        busy = true; status.textContent = 'Saving your consented enquiry…';
        try {
          var result = await request('/followup', Object.assign({}, flow.data, { consent: true, notice: p10Chat.notice }));
          if (generation !== generationAtConfirmation) return;
          flow = null; log.replaceChildren(); bubble(result.message, 'assistant');
          status.textContent = 'Saved. ' + result.notification.replace(/_/g, ' ');
        } catch (e) { if (generation === generationAtConfirmation) status.textContent = e.name === 'AbortError' ? 'Request timed out or was cancelled. Saving may have reached the server; check before retrying.' : e.message; }
        finally { if (generation === generationAtConfirmation) busy = false; }
      });
    }
  }
  follow.addEventListener('click', function () {
    if (!p10Chat.followup || busy || flow) return;
    stopVoice(); flow = { step: 'consent', data: {} }; follow.hidden = true;
    var el = bubble('Optional follow-up: with your consent we will collect and save your name, email OR phone, related profile/location and short enquiry in a private administrator area. No chat transcript is saved. See the privacy policy for retention and deletion. Do you want to continue?', 'assistant');
    sourceLink(el, p10Chat.privacy, 'Privacy policy');
    button(el, 'Yes, continue', function () {
      if (!flow || flow.step !== 'consent') return;
      flow.step = 'name'; bubble('What name should we use? You can reset or close at any time before saving.', 'assistant'); input.focus();
    });
    button(el, 'No, cancel', function () { reset(); input.focus(); });
  });
  find('form').addEventListener('submit', async function (event) {
    event.preventDefault(); var text = input.value.trim();
    if (!text || busy) return;
    input.value = '';
    // Do not echo contact details into the chat log while collecting follow-up data.
    if (flow) { collect(text); return; }
    bubble(text, 'user'); busy = true; status.textContent = 'Searching published website content…';
    var started = generation;
    try {
      var data = await request('', { message: text });
      if (started !== generation) return;
      var el = bubble(data.answer, 'assistant'); last = data.answer;
      (data.sources || []).forEach(function (source) {
        var row = document.createElement('p'); sourceLink(row, source.url, source.title);
        if (source.text) row.appendChild(document.createTextNode(' — ' + source.text));
        el.appendChild(row);
      });
      sourceLink(el, data.directory, 'Browse directory'); log.scrollTop = log.scrollHeight; speak(last);
      status.textContent = data.provider_status.indexOf('fallback') >= 0 ? 'Provider unavailable; local published information shown.' : '';
      follow.hidden = !p10Chat.followup;
    } catch (e) {
      if (started === generation) status.textContent = e.name === 'AbortError' ? 'Request timed out or was cancelled. Please try text again.' : e.message;
    } finally { if (started === generation) busy = false; }
  });
  reset();
})();

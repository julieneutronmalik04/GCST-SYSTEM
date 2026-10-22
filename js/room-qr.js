'use strict';
(() => {
  const modal = document.getElementById('roomQrModal');
  const table = document.getElementById('roomsTableBody');
  if (!modal || !table) return;
  const endpoint = new URL('../modules/rooms.php', document.currentScript.src);
  const backend = new URL('../', document.currentScript.src);
  const image = document.getElementById('roomQrImage');
  const name = document.getElementById('roomQrName');
  const code = document.getElementById('roomQrCode');
  const status = document.getElementById('roomQrStatus');
  const download = document.getElementById('downloadRoomQrBtn');
  const print = document.getElementById('printRoomQrBtn');
  let version = 0;

  async function view(id) {
    const request = ++version;
    image.hidden = true;
    image.removeAttribute('src');
    name.textContent = '';
    code.textContent = '';
    status.textContent = 'Loading room QR code…';
    download.disabled = print.disabled = true;
    modal.classList.remove('hidden');
    try {
      const response = await fetch(endpoint + '?action=qr&id=' + encodeURIComponent(id), { credentials: 'same-origin', cache: 'no-store' });
      const data = await response.json();
      if (!response.ok || !data.success) throw new Error(data.message || 'Unable to load this room.');
      if (request !== version) return;
      name.textContent = data.room.room_name;
      code.textContent = data.room.room_id;
      image.alt = 'QR code for room ' + data.room.room_id;
      image.onload = () => {
        if (request !== version) return;
        image.hidden = false;
        status.textContent = 'Scan this QR code to read the room code.';
        download.disabled = print.disabled = false;
      };
      image.onerror = () => {
        if (request !== version) return;
        image.hidden = true;
        status.textContent = 'Unable to load the QR image. Close this preview and try again.';
      };
      image.src = new URL(data.room.qr_code_path, backend).href;
    } catch (error) {
      if (request === version) status.textContent = error.message;
    }
  }
  table.addEventListener('click', event => {
    const button = event.target.closest('[data-room-qr]');
    if (button) view(button.dataset.roomQr);
  });
  window.viewRoomQR = view;

  download.addEventListener('click', async () => {
    download.disabled = true;
    const source = image.src;
    const filename = code.textContent.replace(/[^a-z0-9._-]/gi, '_') + '-Room-QR.png';
    try {
      const response = await fetch(source, { credentials: 'same-origin' });
      if (!response.ok) throw new Error('QR download failed.');
      const blob = await response.blob();
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = filename;
      document.body.append(link);
      link.click();
      link.remove();
      setTimeout(() => URL.revokeObjectURL(url), 1000);
    } catch (error) { status.textContent = error.message; }
    finally { download.disabled = false; }
  });
  print.addEventListener('click', () => {
    const frame = document.createElement('iframe');
    frame.title = 'Print room QR label';
    frame.style.cssText = 'position:fixed;width:1px;height:1px;opacity:0;pointer-events:none;border:0';
    document.body.append(frame);
    const doc = frame.contentDocument;
    doc.open();
    doc.write('<!doctype html><html><head><title>Room QR Label</title><style>@page{margin:15mm}body{font-family:Arial,sans-serif;text-align:center;color:#111}h1{font-size:28pt;margin:12mm 0 4mm}p{font-size:18pt}img{width:65mm;height:65mm;object-fit:contain}</style></head><body></body></html>');
    doc.close();
    const heading = doc.createElement('h1'); heading.textContent = code.textContent;
    const label = doc.createElement('p'); label.textContent = name.textContent;
    const qr = doc.createElement('img'); qr.alt = image.alt;
    qr.onload = () => { frame.contentWindow.focus(); frame.contentWindow.print(); };
    qr.onerror = () => { status.textContent = 'Unable to prepare the room label for printing.'; frame.remove(); };
    frame.contentWindow.onafterprint = () => frame.remove();
    doc.body.append(heading, label, qr);
    qr.src = image.src;
  });
  // Keep this modal independent of the shared dashboard's initialization.
  modal.querySelector('[data-close="roomQrModal"]')?.addEventListener('click', () => modal.classList.add('hidden'));
})();

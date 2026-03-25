/**
 * Show a Bootstrap 5 toast notification.
 *
 * @param {string} title   - Bold header text
 * @param {string} message - Body message
 * @param {string} type    - Bootstrap colour: 'success' | 'danger' | 'info' | 'warning'
 */
function showToast(title, message, type) {
  const id = `toast-${Date.now()}`;
  const html = `
    <div id="${id}" class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">
          <strong>${title}</strong> ${message}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>`;
  document.getElementById('toastContainer').insertAdjacentHTML('beforeend', html);
  const toastEl = document.getElementById(id);
  const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
  toast.show();
  toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

// Simulate click on hidden file input
document.addEventListener('click', (event) => {
  if (event.target.closest('#btn-upload-img')) {
    event.preventDefault();
    document.getElementById('file-image').click();
  }
});

// Upload image on file selection
document.addEventListener('change', (event) => {
  if (!event.target.matches('#file-image')) return;

  const fileInput = event.target;
  if (!fileInput.files.length) return;

  document.getElementById('faviconResults').classList.add('d-none');

  const btn = document.getElementById('btn-upload-img');
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Uploading...';
  btn.disabled = true;

  const formData = new FormData();
  formData.append('userfile', fileInput.files[0]);

  fetch('/upload', { method: 'POST', body: formData })
    .then((response) => response.json())
    .then((data) => {
      btn.innerHTML = '<i class="bi bi-upload"></i> Upload';
      btn.disabled = false;
      fileInput.value = '';

      if (data.error) {
        showToast('Error', data.error, 'danger');
      } else {
        showToast('Image uploaded!', 'Image uploaded and icons created.', 'success');
        document.getElementById('img-preview').src = data.url;
        document.getElementById('btnFaviconDownload').href = data.zip;
        document.getElementById('faviconResults').classList.remove('d-none');
        getHistory();
      }
    })
    .catch(() => {
      btn.innerHTML = '<i class="bi bi-upload"></i> Upload';
      btn.disabled = false;
      showToast('Error', 'An unexpected error occurred.', 'danger');
    });
});

// Fetch and render upload history
function getHistory() {
  const historySection = document.getElementById('faviconHistory');
  if (!historySection) return;

  fetch('/gethistory')
    .then((response) => response.json())
    .then((data) => {
      if (Array.isArray(data) && data.length > 0) {
        historySection.classList.remove('d-none');
        const list = document.getElementById('history-list');
        list.innerHTML = '';
        data.forEach((item) => {
          list.insertAdjacentHTML(
            'beforeend',
            `<li><img data-name="${item.name}" src="${item.url}" class="img-favicon-history" alt="Saved favicon"></li>`
          );
        });
      }
    });
}

getHistory();

// Regenerate from a history item on click
document.addEventListener('click', (event) => {
  const img = event.target.closest('.img-favicon-history');
  if (!img) return;

  event.preventDefault();
  document.getElementById('faviconResults').classList.add('d-none');
  showToast('Processing', 'Loading favicon...', 'info');

  const filename = img.getAttribute('data-name');

  fetch('/gethistory', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `filename=${encodeURIComponent(filename)}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.error) {
        showToast('Error', data.error, 'danger');
      } else {
        showToast('Favicon loaded!', 'Favicon loaded successfully.', 'success');
        document.getElementById('img-preview').src = data.url;
        document.getElementById('btnFaviconDownload').href = `${data.zip}?v=${Date.now()}`;
        document.getElementById('faviconResults').classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    });
});

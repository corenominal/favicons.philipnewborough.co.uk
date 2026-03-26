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

let pendingDeleteFilename = null;

// Simulate click on hidden file input, validating manifest form first
document.addEventListener('click', (event) => {
  if (event.target.closest('#btn-upload-img')) {
    event.preventDefault();
    const manifestData = getManifestData();
    if (!validateManifest(manifestData)) {
      const errEl = document.getElementById('manifest-errors');
      errEl.classList.remove('d-none');
      errEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }
    document.getElementById('manifest-errors').classList.add('d-none');
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

  const manifestData = getManifestData();
  formData.append('manifest_name', manifestData.name);
  formData.append('manifest_shortname', manifestData.shortname);
  formData.append('manifest_description', manifestData.description);
  formData.append('manifest_theme_color', manifestData.theme_color);
  formData.append('manifest_background_color', manifestData.background_color);
  formData.append('manifest_display', manifestData.display);

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
        updateUsageSnippet();
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
            `<li class="history-item">
              <img data-name="${item.name}" src="${item.url}" class="img-favicon-history" alt="Saved favicon">
              <button class="btn-delete-history" data-name="${item.name}" aria-label="Delete this icon"><i class="bi bi-trash"></i></button>
            </li>`
          );
        });
      } else {
        historySection.classList.add('d-none');
      }
    });
}

getHistory();

// Open delete confirmation modal
document.addEventListener('click', (event) => {
  const btn = event.target.closest('.btn-delete-history');
  if (!btn) return;

  pendingDeleteFilename = btn.getAttribute('data-name');
  const modal = new bootstrap.Modal(document.getElementById('deleteHistoryModal'));
  modal.show();
});

// Confirm delete
const confirmDeleteBtn = document.getElementById('btn-confirm-delete');
if (confirmDeleteBtn) {
  confirmDeleteBtn.addEventListener('click', () => {
    if (!pendingDeleteFilename) return;

    confirmDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Deleting...';
    confirmDeleteBtn.disabled = true;

    fetch('/deletehistory', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `filename=${encodeURIComponent(pendingDeleteFilename)}`,
    })
      .then((response) => response.json())
      .then((data) => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteHistoryModal'));
        modal.hide();
        confirmDeleteBtn.innerHTML = 'Delete';
        confirmDeleteBtn.disabled = false;
        pendingDeleteFilename = null;
        if (data.error) {
          showToast('Error', data.error, 'danger');
        } else {
          showToast('Deleted', 'Icon removed from history.', 'success');
          getHistory();
        }
      })
      .catch(() => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteHistoryModal'));
        modal.hide();
        confirmDeleteBtn.innerHTML = 'Delete';
        confirmDeleteBtn.disabled = false;
        showToast('Error', 'An unexpected error occurred.', 'danger');
      });
  });
}

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
        if (data.manifest) {
          fillManifestForm(data.manifest);
        }
        updateUsageSnippet();
        document.getElementById('faviconResults').classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    });
});

// --- Manifest helpers ---

function updateUsageSnippet() {
  const themeColor = document.getElementById('manifest-theme-color').value.trim() || '#ffffff';
  const el = document.getElementById('usage-theme-color');
  if (el) el.textContent = themeColor;
}

function resetFaviconResults() {
  const results = document.getElementById('faviconResults');
  if (!results.classList.contains('d-none')) {
    results.classList.add('d-none');
    document.getElementById('img-preview').src = '/assets/img/favicon-blank-image.png';
  }
}

function getManifestData() {
  return {
    name: document.getElementById('manifest-name').value.trim(),
    shortname: document.getElementById('manifest-shortname').value.trim(),
    description: document.getElementById('manifest-description').value.trim(),
    theme_color: document.getElementById('manifest-theme-color').value.trim(),
    background_color: document.getElementById('manifest-background-color').value.trim(),
    display: document.getElementById('manifest-display').value,
  };
}

function validateManifest(data) {
  const hexPattern = /^#[0-9a-fA-F]{6}$/;
  return (
    data.name.length > 0
    && data.shortname.length > 0
    && data.description.length > 0
    && hexPattern.test(data.theme_color)
    && hexPattern.test(data.background_color)
    && data.display.length > 0
  );
}

function fillManifestForm(manifest) {
  if (manifest.name) document.getElementById('manifest-name').value = manifest.name;
  if (manifest.short_name) document.getElementById('manifest-shortname').value = manifest.short_name;
  if (manifest.description) document.getElementById('manifest-description').value = manifest.description;
  if (manifest.theme_color) {
    document.getElementById('manifest-theme-color').value = manifest.theme_color;
    document.getElementById('manifest-theme-color-picker').value = manifest.theme_color;
  }
  if (manifest.background_color) {
    document.getElementById('manifest-background-color').value = manifest.background_color;
    document.getElementById('manifest-background-color-picker').value = manifest.background_color;
  }
  if (manifest.display) document.getElementById('manifest-display').value = manifest.display;
}

// Sync color picker → text input (live while dragging)
document.addEventListener('input', (event) => {
  if (event.target.matches('#manifest-theme-color-picker')) {
    document.getElementById('manifest-theme-color').value = event.target.value;
    resetFaviconResults();
  }
  if (event.target.matches('#manifest-background-color-picker')) {
    document.getElementById('manifest-background-color').value = event.target.value;
    resetFaviconResults();
  }
  const manifestTextFields = ['manifest-name', 'manifest-shortname', 'manifest-description', 'manifest-theme-color', 'manifest-background-color'];
  if (manifestTextFields.includes(event.target.id)) {
    resetFaviconResults();
  }
});

// Sync text input → color picker on valid hex entry
document.addEventListener('change', (event) => {
  if (event.target.matches('#manifest-display')) {
    resetFaviconResults();
  }
  const hexPattern = /^#[0-9a-fA-F]{6}$/;
  if (event.target.matches('#manifest-theme-color') && hexPattern.test(event.target.value)) {
    document.getElementById('manifest-theme-color-picker').value = event.target.value;
  }
  if (event.target.matches('#manifest-background-color') && hexPattern.test(event.target.value)) {
    document.getElementById('manifest-background-color-picker').value = event.target.value;
  }
});

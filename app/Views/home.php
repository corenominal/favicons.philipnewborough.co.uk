<?= $this->extend('templates/default') ?>

<?= $this->section('content') ?>
<div class="container">

    <!-- Toast container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer" aria-live="polite" aria-atomic="true"></div>

    <div class="row">
        <div class="col-12">
            <div class="mb-4 mt-4">
                <h1 class="mb-0"><?= esc($title) ?></h1>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-12">
            <div class="card">
                <h5 class="card-header">Web App Manifest Settings</h5>
                <div class="card-body">
                    <p class="text-body-secondary mb-3">These values are used to generate the <code>manifest.json</code> file included in the download. All fields are required before uploading.</p>
                    <div id="manifest-errors" class="alert alert-danger d-none" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Please fill in all required fields before uploading.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="manifest-name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="manifest-name" value="My App" placeholder="My App" required>
                            <div class="form-text">The full name of your web application.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="manifest-shortname" class="form-label fw-semibold">Short Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="manifest-shortname" value="My App" placeholder="My App" maxlength="12" required>
                            <div class="form-text">A short name used where space is limited (e.g. home screen). Max 12 characters.</div>
                        </div>
                        <div class="col-12">
                            <label for="manifest-description" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="manifest-description" value="A web application" placeholder="A web application" required>
                            <div class="form-text">A brief description of your web application.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="manifest-theme-color" class="form-label fw-semibold">Theme Color <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="manifest-theme-color-picker" value="#ffffff" title="Choose theme color">
                                <input type="text" class="form-control" id="manifest-theme-color" value="#ffffff" placeholder="#ffffff" maxlength="7" required>
                            </div>
                            <div class="form-text">The toolbar/accent colour shown in the browser when your app is open.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="manifest-background-color" class="form-label fw-semibold">Background Color <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="manifest-background-color-picker" value="#ffffff" title="Choose background color">
                                <input type="text" class="form-control" id="manifest-background-color" value="#ffffff" placeholder="#ffffff" maxlength="7" required>
                            </div>
                            <div class="form-text">The background colour shown on the splash screen when your app is launched.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="manifest-display" class="form-label fw-semibold">Display Mode <span class="text-danger">*</span></label>
                            <select class="form-select" id="manifest-display" required>
                                <option value="standalone" selected>standalone</option>
                                <option value="minimal-ui">minimal-ui</option>
                                <option value="fullscreen">fullscreen</option>
                                <option value="browser">browser</option>
                            </select>
                            <div class="form-text">
                                <strong>standalone</strong> hides browser UI &mdash; <strong>minimal-ui</strong> shows back/reload &mdash; <strong>fullscreen</strong> no browser chrome &mdash; <strong>browser</strong> normal tab.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-12">
            <div class="card">
                <h5 class="card-header">Upload Image</h5>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="container-img-preview bg-checked mb-3 text-center">
                                <img id="img-preview" src="/assets/img/favicon-blank-image.png" class="img-fluid" alt="Favicon preview">
                            </div>
                            <button id="btn-upload-img" class="btn btn-primary w-100 mb-3">
                                <i class="bi bi-upload"></i> Upload
                            </button>
                            <input type="file" id="file-image" class="d-none" accept="image/png">
                            <p class="p-icon-info text-body-secondary"><small>Image must be PNG and at least 512px &times; 512px.</small></p>
                        </div>

                        <div class="col-md-8">
                            <p>Click <strong>Upload</strong> to open a file picker and select your image. Once selected, the image will be uploaded and processed automatically &mdash; no need to click again.</p>
                            <p>Your image must be a PNG file and at least 512&times;512 pixels. From that image, this tool will:</p>
                            <ul>
                                <li>Create a classic <code>favicon.ico</code> file</li>
                                <li>Resize the icon to 16&times;16, 32&times;32, 48&times;48, 64&times;64, 128&times;128, 180&times;180, and 256&times;256 pixels</li>
                                <li>Generate two placeholder screenshots required by the Web App Manifest spec &mdash; a mobile one (750&times;1334px) and a desktop one (1280&times;720px) &mdash; each showing your icon centred on the manifest background colour</li>
                                <li>Create a <code>manifest.json</code> file using the settings above</li>
                                <li>Generate the HTML code to include in your website</li>
                            </ul>
                            <p class="mb-0">A download link will appear below once processing is complete.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="faviconResults" class="row d-none mb-5">
        <div class="col-12">
            <div class="card">
                <h5 class="card-header">Favicon Download</h5>
                <div class="card-body">
                    <a id="btnFaviconDownload" href="#" class="btn btn-primary btn-lg mb-5">
                        <i class="bi bi-download"></i> Download favicons.zip
                    </a>
                    <h6 class="mt-2 mb-3">Usage</h6>
                    <p>Extract the ZIP and copy all files to the root of your web server's public directory (the same folder as your <code>index.html</code>).</p>
                    <p>Add the following to the <code>&lt;head&gt;</code> section of every HTML page:</p>
                    <pre><code>&lt;link rel="shortcut icon" href="/favicon.ico"&gt;
&lt;link rel="icon" type="image/png" sizes="16x16" href="/icon-16x16.png"&gt;
&lt;link rel="icon" type="image/png" sizes="32x32" href="/icon-32x32.png"&gt;
&lt;link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png"&gt;
&lt;link rel="manifest" href="/manifest.json"&gt;
&lt;meta name="theme-color" content="<span id="usage-theme-color">#ffffff</span>"&gt;</code></pre>
                    <p class="text-body-secondary mb-0"><small>The remaining icon sizes and screenshots in the ZIP are referenced automatically by <code>manifest.json</code>.</small></p>
                </div>
            </div>
        </div>
    </div>

    <?php if (session()->get('user_uuid')): ?>
    <div id="faviconHistory" class="row d-none mb-5">
        <div class="col-12">
            <div class="card">
                <h5 class="card-header">History</h5>
                <div class="card-body">
                    <p>Select an icon to regenerate the .zip file download:</p>
                    <ul id="history-list" class="list-unstyled d-flex flex-wrap gap-2 mb-0"></ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete History Item Confirmation Modal -->
    <div class="modal fade" id="deleteHistoryModal" tabindex="-1" aria-labelledby="deleteHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteHistoryModalLabel">Delete Icon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to permanently delete this icon from your history? This cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="btn-confirm-delete">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="row mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="mb-0"><i class="bi bi-info-circle me-1"></i> <a href="<?= esc(config('Urls')->auth) ?>login?redirect=<?= urlencode(current_url()) ?>">Log in</a> to keep a history of your uploaded icons.</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div> <!-- /.container -->
<?= $this->endSection() ?>
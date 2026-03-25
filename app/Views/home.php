<?= $this->extend('templates/default') ?>

<?= $this->section('content') ?>
<div class="container">

    <!-- Toast container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer" aria-live="polite" aria-atomic="true"></div>

    <div class="row">
        <div class="col-12">
            <div class="border-bottom border-1 mb-4 pb-2 mt-4">
                <h1 class="mb-0"><?= esc($title) ?></h1>
            </div>
        </div>
    </div>

    <div class="row mb-5">
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
            <p>Upload a 512&times;512 pixel PNG image and this tool will:</p>
            <ul>
                <li>Create a classic <code>favicon.ico</code> file</li>
                <li>Resize the icon to 16&times;16, 32&times;32, 48&times;48, 64&times;64, 128&times;128, 180&times;180, 256&times;256 pixels</li>
                <li>Create a <code>manifest.json</code> file</li>
                <li>Generate the HTML code to include in your website</li>
            </ul>
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
                    <h2>Usage</h2>
                    <p>Copy all files to the root public directory of your project.</p>
                    <h4>Favicons</h4>
                    <p>Add the following to the head section of your HTML documents:</p>
                    <pre><code>&lt;link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png"&gt;
&lt;link rel="icon" type="image/png" sizes="32x32" href="/icon-32x32.png"&gt;
&lt;link rel="icon" type="image/png" sizes="16x16" href="/icon-16x16.png"&gt;</code></pre>
                    <h4>Web app manifest</h4>
                    <p>Edit the <code>manifest.json</code> file to suit, changing values for:</p>
                    <ul>
                        <li><code>name</code></li>
                        <li><code>short_name</code></li>
                        <li><code>description</code></li>
                    </ul>
                    <p>Add the following to the head section of your HTML documents:</p>
                    <pre><code>&lt;link rel="manifest" href="/manifest.json" /&gt;</code></pre>
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
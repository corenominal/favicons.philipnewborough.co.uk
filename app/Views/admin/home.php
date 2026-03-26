<?= $this->extend('templates/dashboard') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <div class="row">
        <div class="col-12">
            <div class="border-bottom border-1 mb-4 pb-3">
                <h2 class="mb-0">Admin Dashboard</h2>
            </div>
        </div>
    </div>

    <?php if (session()->getFlashdata('cleanup_message')) : ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
                <?= esc(session()->getFlashdata('cleanup_message')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Stat cards -->
    <div class="row g-3 mb-4">

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 fs-1 text-primary"><i class="bi bi-upload"></i></div>
                        <div>
                            <div class="fs-2 fw-bold"><?= esc($stats['guest_upload_count']) ?></div>
                            <div class="text-muted small">Guest Uploads (all time)</div>
                            <div class="small <?= $stats['active_guest_count'] > 0 ? 'text-success' : 'text-muted' ?>"><?= esc($stats['active_guest_count']) ?> active in last hour</div>
                            <div class="small <?= $stats['stale_guest_count'] > 0 ? 'text-warning' : 'text-muted' ?>"><?= esc($stats['stale_guest_count']) ?> stale (&gt; 1 hr)</div>
                        </div>
                    </div>
                    <?php if ($stats['stale_guest_count'] > 0) : ?>
                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#cleanupTmpModal">
                            <i class="bi bi-trash3"></i> Remove stale tmp directories
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 fs-1 text-info"><i class="bi bi-people-fill"></i></div>
                        <div>
                            <div class="fs-2 fw-bold"><?= esc($stats['history_user_count']) ?></div>
                            <div class="text-muted small">Users with History</div>
                            <div class="text-muted small"><?= esc($stats['user_output_count']) ?> with output dirs</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 fs-1 text-warning"><i class="bi bi-clock-history"></i></div>
                        <div>
                            <div class="fs-2 fw-bold"><?= esc($stats['total_history_items']) ?></div>
                            <div class="text-muted small">Total History Items</div>
                            <div class="text-muted small"><?= esc($stats['avg_history_per_user']) ?> avg per user</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 fs-1 text-success"><i class="bi bi-hdd-fill"></i></div>
                        <div>
                            <div class="fs-2 fw-bold"><?= esc($stats['disk_usage']) ?></div>
                            <div class="text-muted small">Uploads Disk Usage</div>
                            <div class="text-muted small">&nbsp;</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- User history breakdown -->
    <?php if (! empty($stats['history_users'])) : ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-person-lines-fill"></i>
                    <span class="fw-semibold">User History</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>User UUID</th>
                                <th class="text-end">History Items</th>
                                <th class="text-end">Last Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['history_users'] as $user) : ?>
                            <tr>
                                <td class="font-monospace small"><?= esc($user['uuid']) ?></td>
                                <td class="text-end"><?= esc($user['item_count']) ?></td>
                                <td class="text-end text-muted small"><?= esc($user['last_active']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($stats['stale_guest_count'] > 0) : ?>
    <!-- Cleanup tmp confirmation modal -->
    <div class="modal fade" id="cleanupTmpModal" tabindex="-1" aria-labelledby="cleanupTmpModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cleanupTmpModalLabel"><i class="bi bi-trash3 me-2"></i>Remove stale tmp directories</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1">This will permanently delete <strong><?= esc($stats['stale_guest_count']) ?> stale guest tmp <?= $stats['stale_guest_count'] === 1 ? 'directory' : 'directories' ?></strong> (older than 1 hour).</p>
                    <p class="mb-0 text-muted small">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="post" action="/admin/cleanup-tmp">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-trash3"></i> Yes, remove them
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>
<?= $this->endSection() ?>
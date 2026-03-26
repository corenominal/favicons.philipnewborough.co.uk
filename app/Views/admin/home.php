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
                        </div>
                    </div>
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

</div>
<?= $this->endSection() ?>
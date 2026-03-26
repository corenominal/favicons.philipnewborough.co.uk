<?php

namespace App\Controllers\Admin;

class Home extends BaseController
{
    public function index(): string
    {
        $data['title'] = 'Admin Dashboard';
        $data['js']    = ['admin/home'];
        $data['css']   = ['admin/home'];
        $data['stats'] = $this->gatherStats();

        return view('admin/home', $data);
    }

    private function gatherStats(): array
    {
        $faviconsDir = ROOTPATH . 'public/uploads/favicons/';
        $historyDir  = $faviconsDir . 'history/';

        // Guest uploads (tmp-* dirs)
        $tmpDirs          = glob($faviconsDir . 'tmp-*', GLOB_ONLYDIR) ?: [];
        $guestUploadCount = count($tmpDirs);

        $activeGuestCount = 0;
        $threshold        = time() - 3600;
        foreach ($tmpDirs as $dir) {
            if (filemtime($dir) >= $threshold) {
                $activeGuestCount++;
            }
        }

        // Registered users with an output dir (UUID dirs -- not tmp-*, not history)
        $userOutputCount = 0;
        if (is_dir($faviconsDir)) {
            foreach (new \DirectoryIterator($faviconsDir) as $item) {
                if (! $item->isDir() || $item->isDot()) {
                    continue;
                }
                $name = $item->getFilename();
                if ($name === 'history' || str_starts_with($name, 'tmp-')) {
                    continue;
                }
                $userOutputCount++;
            }
        }

        // Users with history and per-user item counts
        $historyUsers      = [];
        $totalHistoryItems = 0;
        if (is_dir($historyDir)) {
            foreach (new \DirectoryIterator($historyDir) as $item) {
                if (! $item->isDir() || $item->isDot()) {
                    continue;
                }
                $uuid     = $item->getFilename();
                $userPath = $historyDir . $uuid . '/';
                $pngFiles = glob($userPath . '*.png') ?: [];
                $count    = count($pngFiles);
                $totalHistoryItems += $count;

                $lastModified = 0;
                foreach ($pngFiles as $png) {
                    $mtime = filemtime($png);
                    if ($mtime > $lastModified) {
                        $lastModified = $mtime;
                    }
                }

                $historyUsers[] = [
                    'uuid'        => $uuid,
                    'item_count'  => $count,
                    'last_active' => $lastModified > 0 ? date('Y-m-d H:i', $lastModified) : '&mdash;',
                ];
            }
        }

        usort($historyUsers, fn($a, $b) => strcmp($b['last_active'], $a['last_active']));

        $avgPerUser = ($historyUsers !== [])
            ? round($totalHistoryItems / count($historyUsers), 1)
            : 0;

        return [
            'guest_upload_count'   => $guestUploadCount,
            'active_guest_count'   => $activeGuestCount,
            'user_output_count'    => $userOutputCount,
            'history_user_count'   => count($historyUsers),
            'total_history_items'  => $totalHistoryItems,
            'avg_history_per_user' => $avgPerUser,
            'disk_usage'           => $this->formatBytes($this->dirSize($faviconsDir)),
            'history_users'        => $historyUsers,
        ];
    }

    private function dirSize(string $dir): int
    {
        $size = 0;
        if (! is_dir($dir)) {
            return $size;
        }
        $iter = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iter as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }
        if ($bytes < 1073741824) {
            return round($bytes / 1048576, 1) . ' MB';
        }

        return round($bytes / 1073741824, 2) . ' GB';
    }
}

<?php

namespace App\Controllers;

use CodeIgniter\Files\File;

class Home extends BaseController
{
    public function index(): string
    {
        $data['js']    = ['home'];
        $data['css']   = ['home'];
        $data['title'] = 'Favicon & Manifest Generator';
        return view('home', $data);
    }

    public function gethistory(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userUuid = session()->get('user_uuid');

        if ($userUuid === null) {
            return $this->response->setJSON(['loggedOut' => true]);
        }

        $historyDir = ROOTPATH . 'public/uploads/favicons/history/' . $userUuid . '/';

        if (! is_dir($historyDir)) {
            return $this->response->setJSON([]);
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($historyDir)
        );
        $data = [];
        foreach ($files as $file) {
            if ($file->isDir()) {
                continue;
            }
            if ($file->getFilename() === '.gitkeep') {
                continue;
            }
            // Only include PNG source images, not manifest sidecar files
            if ($file->getExtension() !== 'png') {
                continue;
            }
            $data[] = [
                'name' => $file->getFilename(),
                'url'  => base_url('uploads/favicons/history/' . $userUuid . '/' . $file->getFilename()),
                'size' => $file->getSize(),
                'date' => date('Y-m-d H:i:s', $file->getMTime()),
            ];
        }

        return $this->response->setJSON($data);
    }

    public function upload(): \CodeIgniter\HTTP\ResponseInterface
    {
        $validationRule = [
            'userfile' => [
                'label' => 'Image File',
                'rules' => [
                    'uploaded[userfile]',
                    'is_image[userfile]',
                    'mime_in[userfile,image/png]',
                ],
            ],
        ];

        if (! $this->validate($validationRule)) {
            $errors = $this->validator->getErrors();
            return $this->response->setJSON(['error' => $errors['userfile']]);
        }

        $file = $this->request->getFile('userfile');

        if (! $file->hasMoved()) {
            $filenameOrig = $file->getName();
            $extension    = $file->getClientExtension();
            $filename     = md5($filenameOrig . microtime()) . '.' . $extension;
            $filepath     = WRITEPATH . 'uploads/' . $file->store('tmp', $filename);

            $fileinfo = \Config\Services::image('gd')
                ->withFile($filepath)
                ->getFile()
                ->getProperties(true);

            new File($filepath);

            $width  = $fileinfo['width'];
            $height = $fileinfo['height'];

            if ($width !== $height) {
                return $this->response->setJSON(['error' => 'The icon must be square.']);
            }
            if ($width < 512) {
                return $this->response->setJSON(['error' => 'The icon must be at least 512px x 512px.']);
            }

            $userUuid = session()->get('user_uuid');

            $manifestData = $this->extractManifestData();

            if ($userUuid !== null) {
                $historyDir = ROOTPATH . 'public/uploads/favicons/history/' . $userUuid . '/';
                if (! is_dir($historyDir)) {
                    mkdir($historyDir, 0755, true);
                }
                $history = $historyDir . $filename;
                copy($filepath, $history);
                unlink($filepath);

                // Save manifest settings alongside the history PNG
                $basename             = pathinfo($filename, PATHINFO_FILENAME);
                $manifestSettingsFile = $historyDir . $basename . '-manifest.json';
                file_put_contents($manifestSettingsFile, json_encode($manifestData, JSON_PRETTY_PRINT));

                return $this->response->setJSON($this->createFavicon($history, $manifestData));
            }

            $result = $this->createFavicon($filepath, $manifestData);
            unlink($filepath);
            $this->cleanupTmpFavicons();

            return $this->response->setJSON($result);
        }

        return $this->response->setJSON(['error' => 'File upload failed.']);
    }

    public function gethistoryitem(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userUuid = session()->get('user_uuid');

        if ($userUuid === null) {
            return $this->response->setJSON(['error' => 'You must be logged in to access history items.']);
        }

        $filename   = $this->request->getPost('filename');
        $historyDir = ROOTPATH . 'public/uploads/favicons/history/' . $userUuid . '/';
        $filepath   = $historyDir . basename($filename);

        if (! file_exists($filepath)) {
            return $this->response->setJSON(['error' => 'File not found.']);
        }

        // Load saved manifest settings for this history item if available
        $basename             = pathinfo(basename($filename), PATHINFO_FILENAME);
        $manifestSettingsFile = $historyDir . $basename . '-manifest.json';
        $manifestData         = [];
        if (file_exists($manifestSettingsFile)) {
            $manifestData = json_decode(file_get_contents($manifestSettingsFile), true) ?? [];
        }

        $result = $this->createFavicon($filepath, $manifestData);
        if (! empty($manifestData)) {
            $result['manifest'] = $manifestData;
        }

        return $this->response->setJSON($result);
    }

    public function deletehistory(): \CodeIgniter\HTTP\ResponseInterface
    {
        $userUuid = session()->get('user_uuid');

        if ($userUuid === null) {
            return $this->response->setJSON(['error' => 'You must be logged in to delete history items.']);
        }

        $filename   = basename((string) $this->request->getPost('filename'));
        $historyDir = ROOTPATH . 'public/uploads/favicons/history/' . $userUuid . '/';
        $filepath   = $historyDir . $filename;

        if (pathinfo($filename, PATHINFO_EXTENSION) !== 'png') {
            return $this->response->setJSON(['error' => 'Invalid file.']);
        }

        if (! file_exists($filepath)) {
            return $this->response->setJSON(['error' => 'File not found.']);
        }

        unlink($filepath);

        $basename             = pathinfo($filename, PATHINFO_FILENAME);
        $manifestSettingsFile = $historyDir . $basename . '-manifest.json';
        if (file_exists($manifestSettingsFile)) {
            unlink($manifestSettingsFile);
        }

        return $this->response->setJSON(['success' => true]);
    }

    private function cleanupTmpFavicons(): void
    {
        $baseDir   = ROOTPATH . 'public/uploads/favicons/';
        $threshold = time() - 3600;

        foreach (glob($baseDir . 'tmp-*', GLOB_ONLYDIR) as $dir) {
            if (filemtime($dir) < $threshold) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($files as $file) {
                    $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
                }
                rmdir($dir);
            }
        }
    }

    private function extractManifestData(): array
    {
        $allowedDisplays      = ['standalone', 'minimal-ui', 'fullscreen', 'browser'];
        $allowedOrientations  = ['any', 'natural', 'portrait', 'portrait-primary', 'portrait-secondary', 'landscape', 'landscape-primary', 'landscape-secondary'];
        $hexPattern           = '/^#[0-9a-fA-F]{6}$/';

        $themeColor  = $this->request->getPost('manifest_theme_color') ?? '#ffffff';
        $bgColor     = $this->request->getPost('manifest_background_color') ?? '#ffffff';
        $display     = $this->request->getPost('manifest_display') ?? 'standalone';
        $orientation = $this->request->getPost('manifest_orientation') ?? 'any';

        return [
            'name'             => trim((string) ($this->request->getPost('manifest_name') ?? 'My App')),
            'short_name'       => trim((string) ($this->request->getPost('manifest_shortname') ?? 'My App')),
            'description'      => trim((string) ($this->request->getPost('manifest_description') ?? 'A web application')),
            'theme_color'      => preg_match($hexPattern, $themeColor) ? $themeColor : '#ffffff',
            'background_color' => preg_match($hexPattern, $bgColor) ? $bgColor : '#ffffff',
            'display'          => in_array($display, $allowedDisplays, true) ? $display : 'standalone',
            'orientation'      => in_array($orientation, $allowedOrientations, true) ? $orientation : 'any',
        ];
    }

    private function createFavicon(string $filepath, array $manifestData = []): array
    {
        // Determine output directory based on login state
        $userUuid = session()->get('user_uuid');
        if ($userUuid !== null) {
            $outputDir = ROOTPATH . 'public/uploads/favicons/' . $userUuid . '/';
            $baseUrl   = 'uploads/favicons/' . $userUuid . '/';
        } else {
            $tmpId     = bin2hex(random_bytes(16));
            $outputDir = ROOTPATH . 'public/uploads/favicons/tmp-' . $tmpId . '/';
            $baseUrl   = 'uploads/favicons/tmp-' . $tmpId . '/';
        }

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $extension = pathinfo($filepath, PATHINFO_EXTENSION);

        copy($filepath, $outputDir . 'icon-512x512.' . $extension);

        $url = base_url($baseUrl . 'icon-512x512.' . $extension . '?v=' . microtime());

        $image = service('image', 'imagick');
        $sizes = [16, 32, 48, 64, 128, 180, 192, 256];
        foreach ($sizes as $size) {
            $new = $outputDir . 'icon-' . $size . 'x' . $size . '.png';
            $image->withFile($filepath)
                ->resize($size, $size, true)
                ->save($new);
        }

        // Rename icon-180x180.png to apple-touch-icon.png
        rename(
            $outputDir . 'icon-180x180.png',
            $outputDir . 'apple-touch-icon.png'
        );

        // Copy icon-512x512.png to icon.png
        copy(
            $outputDir . 'icon-512x512.png',
            $outputDir . 'icon.png'
        );

        // Create favicon.ico via Imagick
        $baseImage = new \Imagick($filepath);
        $baseImage->setImageFormat('png');
        $ico = new \Imagick();
        $ico->setFormat('ico');
        $resized = clone $baseImage;
        $resized->resizeImage(16, 16, \Imagick::FILTER_LANCZOS, 1, true);
        $ico->addImage($resized);
        $ico->writeImage($outputDir . 'favicon.ico');

        // Create screenshot images
        $bgColor = ! empty($manifestData['background_color']) ? $manifestData['background_color'] : '#ffffff';
        $icon512 = new \Imagick($outputDir . 'icon-512x512.png');

        $mobileScreenshot = new \Imagick();
        $mobileScreenshot->newImage(750, 1334, new \ImagickPixel($bgColor));
        $mobileScreenshot->setImageFormat('png');
        $mobileScreenshot->compositeImage($icon512, \Imagick::COMPOSITE_OVER, (int) ((750 - 512) / 2), (int) ((1334 - 512) / 2));
        $mobileScreenshot->writeImage($outputDir . 'mobile-screenshot-750-1334.png');

        $desktopScreenshot = new \Imagick();
        $desktopScreenshot->newImage(1280, 720, new \ImagickPixel($bgColor));
        $desktopScreenshot->setImageFormat('png');
        $desktopScreenshot->compositeImage($icon512, \Imagick::COMPOSITE_OVER, (int) ((1280 - 512) / 2), (int) ((720 - 512) / 2));
        $desktopScreenshot->writeImage($outputDir . 'desktop-screenshot-1280x720.png');

        // Write manifest.json
        $siteName = config('App')->siteName;
        $manifest = [
            'name'             => ! empty($manifestData['name']) ? $manifestData['name'] : $siteName,
            'short_name'       => ! empty($manifestData['short_name']) ? $manifestData['short_name'] : $siteName,
            'description'      => ! empty($manifestData['description']) ? $manifestData['description'] : $siteName,
            'icons'            => [
                ['src' => '/icon-16x16.png',   'sizes' => '16x16',   'type' => 'image/png', 'purpose' => 'maskable'],
                ['src' => '/icon-32x32.png',   'sizes' => '32x32',   'type' => 'image/png', 'purpose' => 'maskable'],
                ['src' => '/icon-48x48.png',   'sizes' => '48x48',   'type' => 'image/png', 'purpose' => 'maskable'],
                ['src' => '/icon-64x64.png',   'sizes' => '64x64',   'type' => 'image/png', 'purpose' => 'maskable'],
                ['src' => '/icon-128x128.png', 'sizes' => '128x128', 'type' => 'image/png', 'purpose' => 'maskable'],
                ['src' => '/icon-192x192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'maskable'],
                ['src' => '/icon-256x256.png', 'sizes' => '256x256', 'type' => 'image/png', 'purpose' => 'maskable'],
                ['src' => '/icon-256x256.png', 'sizes' => '256x256', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/icon-512x512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
            'screenshots'      => [
                [
                    'src'         => '/desktop-screenshot-1280x720.png',
                    'sizes'       => '1280x720',
                    'type'        => 'image/png',
                    'form_factor' => 'wide',
                ],
                [
                    'src'   => '/mobile-screenshot-750-1334.png',
                    'sizes' => '750x1334',
                    'type'  => 'image/png',
                ],
            ],
            'start_url'        => '/',
            'display'          => ! empty($manifestData['display']) ? $manifestData['display'] : 'standalone',
            'orientation'      => ! empty($manifestData['orientation']) ? $manifestData['orientation'] : 'any',
            'theme_color'      => ! empty($manifestData['theme_color']) ? $manifestData['theme_color'] : '#ffffff',
            'background_color' => $bgColor,
        ];
        file_put_contents(
            $outputDir . 'manifest.json',
            json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        );

        // Write README.md
        $themeColorReadme = ! empty($manifestData['theme_color']) ? $manifestData['theme_color'] : '#ffffff';
        $readme           = <<<EOT
# Usage
Extract the ZIP and copy all files to the root of your web server's public directory (the same folder as your `index.html`).

Add the following to the `<head>` section of every HTML page:

```
<link rel="shortcut icon" href="/favicon.ico">
<link rel="icon" type="image/png" sizes="16x16" href="/icon-16x16.png">
<link rel="icon" type="image/png" sizes="32x32" href="/icon-32x32.png">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="{$themeColorReadme}">
```

The remaining icon sizes and screenshots in the ZIP are referenced automatically by `manifest.json`.
EOT;
        file_put_contents($outputDir . 'README.md', $readme);

        // Delete and recreate zip
        $zipFile = $outputDir . 'favicons.zip';
        if (file_exists($zipFile)) {
            unlink($zipFile);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipFile, \ZipArchive::CREATE) === true) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($outputDir)
            );
            foreach ($files as $file) {
                if ($file->isDir() || $file->getFilename() === 'favicons.zip') {
                    continue;
                }
                $filePath     = $file->getRealPath();
                $relativePath = substr($filePath, strlen($outputDir));
                $zip->addFile($filePath, $relativePath);
            }
            $zip->close();
        }

        return [
            'url' => $url,
            'zip' => base_url($baseUrl . 'favicons.zip'),
        ];
    }
}

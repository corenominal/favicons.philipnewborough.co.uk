<?php

namespace App\Controllers;

use CodeIgniter\Files\File;

class Home extends BaseController
{
    public function index(): string
    {
        $data['js']    = ['home'];
        $data['css']   = ['home'];
        $data['title'] = 'Favicon Maker';
        return view('home', $data);
    }

    public function gethistory(): \CodeIgniter\HTTP\ResponseInterface
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(ROOTPATH . 'public/uploads/favicons/history/')
        );
        $data = [];
        foreach ($files as $file) {
            if ($file->isDir()) {
                continue;
            }
            if ($file->getFilename() === '.gitkeep') {
                continue;
            }
            $data[] = [
                'name' => $file->getFilename(),
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

            $history = ROOTPATH . 'public/uploads/favicons/history/' . $filename;
            copy($filepath, $history);
            unlink($filepath);

            return $this->createFavicon($history);
        }

        return $this->response->setJSON(['error' => 'File upload failed.']);
    }

    public function gethistoryitem(): \CodeIgniter\HTTP\ResponseInterface
    {
        $filename = $this->request->getPost('filename');
        $filepath = ROOTPATH . 'public/uploads/favicons/history/' . basename($filename);

        if (! file_exists($filepath)) {
            return $this->response->setJSON(['error' => 'File not found.']);
        }

        return $this->createFavicon($filepath);
    }

    private function createFavicon(string $filepath): \CodeIgniter\HTTP\ResponseInterface
    {
        $filename  = pathinfo($filepath, PATHINFO_FILENAME);
        $extension = pathinfo($filepath, PATHINFO_EXTENSION);
        $filename  = $filename . '.' . $extension;

        copy($filepath, ROOTPATH . 'public/uploads/favicons/icon-512x512.' . $extension);

        $url = base_url('uploads/favicons/icon-512x512.' . $extension . '?v=' . microtime());

        $image = service('image', 'imagick');
        $sizes = [16, 32, 48, 64, 128, 180, 192, 256];
        foreach ($sizes as $size) {
            $new = ROOTPATH . 'public/uploads/favicons/icon-' . $size . 'x' . $size . '.png';
            $image->withFile($filepath)
                ->resize($size, $size, true)
                ->save($new);
        }

        // Rename icon-180x180.png to apple-touch-icon.png
        rename(
            ROOTPATH . 'public/uploads/favicons/icon-180x180.png',
            ROOTPATH . 'public/uploads/favicons/apple-touch-icon.png'
        );

        // Copy icon-512x512.png to icon.png
        copy(
            ROOTPATH . 'public/uploads/favicons/icon-512x512.png',
            ROOTPATH . 'public/uploads/favicons/icon.png'
        );

        // Create favicon.ico via Imagick
        $baseImage = new \Imagick($filepath);
        $baseImage->setImageFormat('png');
        $ico = new \Imagick();
        $ico->setFormat('ico');
        $resized = clone $baseImage;
        $resized->resizeImage(16, 16, \Imagick::FILTER_LANCZOS, 1, true);
        $ico->addImage($resized);
        $ico->writeImage(ROOTPATH . 'public/uploads/favicons/favicon.ico');

        // Write manifest.json
        $siteName = config('App')->siteName;
        $manifest = [
            'name'             => $siteName,
            'short_name'       => $siteName,
            'description'      => $siteName,
            'icons'            => [
                ['src' => '/icon-16x16.png',  'sizes' => '16x16',   'type' => 'image/png'],
                ['src' => '/icon-32x32.png',  'sizes' => '32x32',   'type' => 'image/png'],
                ['src' => '/icon-48x48.png',  'sizes' => '48x48',   'type' => 'image/png'],
                ['src' => '/icon-64x64.png',  'sizes' => '64x64',   'type' => 'image/png'],
                ['src' => '/icon-128x128.png', 'sizes' => '128x128', 'type' => 'image/png'],
                ['src' => '/icon-192x192.png', 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => '/icon-256x256.png', 'sizes' => '256x256', 'type' => 'image/png'],
                ['src' => '/icon-512x512.png', 'sizes' => '512x512', 'type' => 'image/png'],
            ],
            'start_url'        => '/',
            'display'          => 'standalone',
            'theme_color'      => '#ffffff',
            'background_color' => '#ffffff',
        ];
        file_put_contents(
            ROOTPATH . 'public/uploads/favicons/manifest.json',
            json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        );

        // Write README.md
        $readme = <<<'EOT'
# Usage
Copy all files to the root public directory of your project.
### Favicons
Add the following to the head section of your HTML documents:
```
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/icon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/icon-16x16.png">
```
### Web app manifest
Edit the `manifest.json` file to suit, changing values for:
* name
* short_name
* description

Add the following to the head section of your HTML documents:
```
<link rel="manifest" href="/manifest.json" />
```
EOT;
        file_put_contents(ROOTPATH . 'public/uploads/favicons/README.md', $readme);

        // Delete and recreate zip
        $zipFile = ROOTPATH . 'public/uploads/favicons/favicons.zip';
        if (file_exists($zipFile)) {
            unlink($zipFile);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipFile, \ZipArchive::CREATE) === true) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(ROOTPATH . 'public/uploads/favicons/')
            );
            foreach ($files as $file) {
                if ($file->isDir() || strpos($file->getRealPath(), 'history') !== false) {
                    continue;
                }
                $filePath     = $file->getRealPath();
                $relativePath = substr($filePath, strlen(ROOTPATH . 'public/uploads/favicons/'));
                $zip->addFile($filePath, $relativePath);
            }
            $zip->close();
        }

        return $this->response->setJSON([
            'url' => $url,
            'zip' => base_url('uploads/favicons/favicons.zip'),
        ]);
    }
}

# favicons.philipnewborough.co.uk

A web application for generating favicons and web app manifests from a single source image. Upload a square PNG (minimum 512×512 px) and receive a ZIP containing all the icon sizes, a `manifest.json`, and ready-to-paste HTML — everything needed to add a favicon set to a modern website or Progressive Web App.

## Features

- Upload a PNG source image and generate the full favicon set in one click
- Produces multiple PNG icon sizes: 16, 32, 48, 64, 128, 192, 256, and 512 px
- Generates `favicon.ico`, `apple-touch-icon.png`, and `icon.png`
- Configurable `manifest.json` with name, short name, description, theme colour, background colour, and display mode
- Generates PWA screenshot images (mobile 750×1334 and desktop 1280×720) for the manifest
- Includes a `README.md` in the download with copy-paste `<head>` HTML
- Authenticated users get a per-user upload history with the ability to re-generate or delete past uploads
- Anonymous users are supported; temporary files are cleaned up automatically after one hour

## Tech Stack

- **PHP 8.2+** with the **CodeIgniter 4** framework
- **Imagick** PHP extension for image resizing and ICO generation
- **Bootstrap 5** for the front-end UI
- **ramsey/uuid** for per-user storage isolation
- **hermawan/codeigniter4-datatables** for admin data tables
- Code style: PSR-12 (PHP), BEM (CSS), Airbnb (JavaScript)

## Authentication

The application integrates with an external auth server via cookie-based sessions. Unauthenticated users can still use the generator; authenticated users gain access to a persistent upload history.

## Requirements

- PHP 8.2 or higher
- Imagick PHP extension
- Composer
- A web server pointed at the `public/` directory

## Setup

```bash
composer install
cp env .env
# Edit .env with your app URL, auth server URL, and other environment values
```

## Development

```bash
# Lint JavaScript
npx eslint public/assets/js/

# Run PHP tests
vendor/bin/phpunit
```


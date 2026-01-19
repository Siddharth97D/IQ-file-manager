# 🚀 Laravel Advanced File Manager

A powerful, production-ready **Laravel file manager package** with OS-like UI, media library, and file picker mode. Designed to be a drop-in solution for handling media, documents, and folders with a modern, responsive interface.

## 🌟 Why Use This Over Others?

While there are many file managers for Laravel, this package is built for developers who need a **complete product experience** rather than just a file uploader.

- **OS-Like Experience**: A full windowing/folder system that users already know how to use.
- **Built-in UI Customization**: Unlike UniSharp or alexusmai, we provide a dynamic theming engine to match your brand instantly.
- **Picker & Standalone Modes**: Easily switch between a full-page dashboard and a modal-based file picker for your CMS forms.
- **Performance at Scale**: Optimized for AWS S3 and local storage with deep search and pagination.
- **Zero Dependencies**: Powered by Alpine.js and Tailwind - no heavy jQuery or legacy JS required.

---

## 📋 Requirements

- **PHP**: 8.1, 8.2, or 8.3
- **Laravel**: 10.x, 11.x, or 12.x
- **Note**: Laravel 12 requires PHP 8.2 or higher

---

## ✨ Key Features

### ✅ Core Functionalities
- **Directory Structure**: Create nested folders, move files, and rename items.
- **AWS S3 Integration**: Just need s3 credentials to upload files to s3 from your laravel project.
- **Drag & Drop Uploads**: Simple drag-and-drop interface for uploading multiple files.
- **Smart Previews**: Built-in modal to preview Images, Videos, PDFs, and Folder details.
- **Advanced Search**: Filter by text, file type (Image, Video, Audio, Doc), date range, and location.
- **Bulk Actions**: Select multiple files to move or delete in batches.
- **Trash Bin**: Soft delete system with "Restore" and "Permanently Delete" options.
- **Zip Downloads**: Download entire folders as `.zip` archives.
- **Image Variants**: Auto-generate multiple responsive image sizes (Thumbnail, Small, Medium, Large) + WebP support.
- **Favorites & Preferences**: Mark files as favorites and store user-specific settings.
- **Headless API**: Full Sanctum-auth API for building custom frontends (React/Vue).
- **Dark Mode**: Built-in dark mode with auto-detection and toggle.
- **Keyboard Shortcuts**: Power-user hotkeys for quick navigation.

### 🎨 Customization
- **Dynamic Theming**: Change sidebar colors, active states, and fonts directly from settings.
- **Grid & List Views**: Toggle between visual grid layouts and detailed list tables.

---

## 🛠 Installation

### 1. Require the Package
```bash
composer require iqonic/laravel-advanced-file-manager
```

### 2. Publish Assets & Config
Publish the configuration file, migrations, and frontend assets:
```bash
php artisan vendor:publish --provider="Iqonic\FileManager\FileManagerServiceProvider"
```

### 3. Run Migrations
Create the necessary database tables:
```bash
php artisan migrate
```

### 4. Storage Link
Ensure your public storage is linked:
```bash
php artisan storage:link
```

---

## 🚀 Usage

### 1. Standalone Dashboard
Access the full file manager dashboard at:
```
/file-manager
```
(You can change this route in `config/file-manager.php`)

### 2. File Picker Mode (Integration)
Want to use this file manager to select files for a form in your own application? Use the **Picker Mode**.

**How it works:**
1. Open the file manager in a popup window with specific query parameters.
2. The user selects file(s) and clicks "Confirm Selection".
3. The window closes and sends the selected file data back to your main window via `postMessage`.

**Example Implementation:**

```javascript
// Function to open the File Manager
function openFileManager() {
    // Params:
    // pickerMode=true  -> Enables selection mode
    // multiple=false   -> Set to true for multi-select
    const width = 1000;
    const height = 700;
    const left = (screen.width - width) / 2;
    const top = (screen.height - height) / 2;

    window.open(
        '/file-manager?pickerMode=true&multiple=false', 
        'FileManager', 
        `width=${width},height=${height},top=${top},left=${left},resizable=yes,scrollbars=yes`
    );
}

// Listen for the selection event
window.addEventListener('message', (event) => {
    // Verify usage if running on different domains, generally optional for same-origin
    if (event.data.type === 'fm_selection') {
        const file = event.data.file; // OR event.data.files if multiple=true
        
        console.log('User selected:', file);
        
        // Example: Update your form inputs
        // document.getElementById('featured_image_input').value = file.url;
        // document.getElementById('preview_img').src = file.url;
    }
});
```

### 3. API Usage
You can also use the backend service programmatically.

```php
use Iqonic\FileManager\Facades\FileManager;

// Upload a file
$file = FileManager::upload($request->file('avatar'));

// Create a folder
$folder = FileManager::createFolder('New Gallery');

// Get Files in a Folder
$files = FileManager::listFiles(['folder_id' => $folder->id]);
```

---

### 4. Image Variants & Responsive Images
Every image upload automatically generates optimized variants based on your config.

```php
// Get URL for specific size (thumbnail, small, medium, large)
$url = $file->getVariantUrl('medium');

// Get responsive srcset for <img> tags
// Returns: "url_small.jpg 400w, url_medium.jpg 800w..."
$srcset = $file->srcset; 
```

### 5. User Preferences & Favorites
Store user-specific settings and mark files as favorites.

```php
use Iqonic\FileManager\Models\UserPreference;

// Save a preference
UserPreference::set($user->id, 'theme', 'dark');

// Retrieve a preference
$theme = UserPreference::get($user->id, 'theme', 'light');
```

### 6. Headless API
This package provides a full JSON API secured by Laravel Sanctum for headless implementations.

**Authentication:**
- Supports standard Web Session (for dashboard) OR Sanctum Tokens (for external apps).
- Issue Token: `POST /api/auth/token`
- Get User: `GET /api/user`

**Endpoints:**
All dashboard routes are available via API (e.g., `GET /api/files`, `POST /api/files/upload`), returning JSON responses.

### 7. Keyboard Shortcuts
Navigate faster with built-in hotkeys:
- **`?`**: Show Help Modal
- **`Ctrl/Cmd + A`**: Select All Files
- **`Delete` / `Backspace`**: Delete Selected Files
- **`Esc`**: Clear Selection / Close Modals
```

---

## ⚙️ Configuration

Check `config/file-manager.php` for all settings.

| Setting | Default | Description |
| :--- | :--- | :--- |
| `route_prefix` | `file-manager` | URL prefix for the dashboard. |
| `middleware` | `['web', 'auth']` | Middleware applied to routes. |
| `disk` | `public` | Storage disk (supports `s3`). |
| `upload.max_size_mb` | `100` | Max upload size per file. |
| `upload.allowed_mimes` | `[...]` | Allowed file types. |
| `image_variants` | `[...]` | Config for auto-generated sizes. |
| `features` | `[...]` | Feature flags (favorites, dark mode, etc). |

---

## 🔍 SEO & Use Cases

### Laravel File Manager with S3

This package is built to handle high-volume storage. By simply changing your disk to `s3` in the config(Settings), you gain a powerful **Laravel S3 file manager** that handles multi-part uploads and secure previews without taxing your web server.


### Laravel File Picker for Forms
Need a **Laravel file selector** for your blog's featured image? Use the `pickerMode` to turn the file manager into a modal popup. It returns clean file objects (URL, ID, Name) to your parent window via JS events.

### Laravel Media Library UI
If you find the default Spatie Media Library too "headless", use this package as your **Laravel media library UI**. It provides the visual layer you need to browse, search, and manage your processed media variants.

## If this package helped you, please ⭐ star the repo.
---

## 📄 License
MIT License.

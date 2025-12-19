# 🚀 Laravel Advanced File Manager

A powerful, production-ready file management package for Laravel applications. Designed to be a drop-in solution for handling media, documents, and folders with a modern, responsive UI.

## 🌟 Why Use This Package?

Unlike basic file uploaders, this package provides a full **Operating System-like experience** directly in your browser.

- **Zero Config UI**: Comes with a sleek, beautiful dashboard out of the box.
- **Developer Friendly**: Easy to install, widely configurable, and API-first.
- **Integration Ready**: Use it standalone or as a **File Picker** for your forms (CMS style).
- **Performance**: Optimized for large libraries with pagination, search, and efficient querying.

---

## ✨ Key Features

### ✅ Core Functionalities
- **Directory Structure**: Create nested folders, move files, and rename items.
- **Drag & Drop Uploads**: Simple drag-and-drop interface for uploading multiple files.
- **Smart Previews**: Built-in modal to preview Images, Videos, PDFs, and Folder details.
- **Advanced Search**: Filter by text, file type (Image, Video, Audio, Doc), date range, and location.
- **Bulk Actions**: Select multiple files to move or delete in batches.
- **Trash Bin**: Soft delete system with "Restore" and "Permanently Delete" options.
- **Zip Downloads**: Download entire folders as `.zip` archives.

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

## ⚙️ Configuration

Check `config/file-manager.php` for all settings.

| Setting | Default | Description |
| :--- | :--- | :--- |
| `route_prefix` | `file-manager` | URL prefix for the dashboard. |
| `middleware` | `['web', 'auth']` | Middleware applied to routes. |
| `disk` | `public` | Storage disk (supports `s3`). |
| `upload.max_size_mb` | `100` | Max upload size per file. |
| `upload.allowed_mimes` | `[...]` | Allowed file types. |

---

## 📄 License
MIT License.

# Changelog

All notable changes to this project will be documented in this file.

## [1.5.0] - 2026-01-30

### Added
- **Secure File Sharing**: Added ability to create public shareable links for files.
    - Password protection support.
    - Expiration date support.
    - Maximum download limit support.
    - Standalone download page with file preview.
- **Context Menu**: Added right-click context menu (3-dot menu) for quick file actions.
    - "Get Share Link" option integrated.
- **Preview Route**: Added dedicated preview route for shared files to preventing download count consumption on view.

### Fixed
- Fixed `ShareController` inheritance issue (`Class "Controller" not found`).
- Fixed `CSRF token mismatch` by adding meta tag to layout.
- Fixed `Method authorize does not exist` by adding `AuthorizesRequests` trait.
- Fixed conflicting migrations for `file_shares` and `file_manager_settings`.
- Fixed `Unable to retrieve file_size` error by correctly resolving file disk.
- Fixed "Link Expired" issue caused by previews counting as downloads.

## [1.4.0] - 2026-01-28
### Added
- **Secure File Sharing**: Generate public links with optional password protection and expiration.
- **Context Menu**: Right-click on files/folders for quick actions (Preview, Share, Rename, Download, Delete).
- **Share Management**: Track downloads and manage shared links via API.

## [1.3.0] - 2026-01-19

### Added
- **Headless API**: Complete Headless API foundation using Laravel Sanctum.
    - `POST /api/auth/token` - Issue API tokens.
    - `POST /api/auth/logout` - Revoke tokens.
    - `GET /api/user` - Get authenticated user.
- **Frontend Features**:
    - **Dark Mode**: Integrated dark mode with CSS variables and persistence (Local Storage + DB).
    - **Keyboard Shortcuts**: Added global shortcuts (`Ctrl+A`, `Del`, `?`, `Esc`) and help modal.
    - **Theme Manager**: JS utility for managing theme states and syncing with backend preferences.
- **Components**: Added `shortcuts-modal` Blade component.
- **Integration**: Auto-injected frontend assets into `layout.blade.php`.

### Changed
- **API Middleware**: Secured API routes to allow both Session (Web) and Sanctum (API) authentication.

## [1.2.0] - 2026-01-17

### Added
- **Image Variants System**: Automatically generates multiple responsive image sizes (Thumbnail, Small, Medium, Large) + WebP variants on upload.
- **Favorites System**: Mark files as favorites with `is_favorite` toggle API and filtering support.
- **User Preferences**: Generic key-value store database tables and API for saving user settings (theme, view mode, etc.).
- **New API Endpoints**:
    - `POST /api/files/{id}/favorite`
    - `GET /api/favorites`
    - `GET /api/preferences`
    - `POST /api/preferences`
    - `GET /api/files/{id}/variant/{preset}`
- **Models**: Added `UserPreference` model and `FileVariant` relationship.
- **Config**: Added `image_variants` configuration block with customizable presets.

### Changed
- **File Model**: Added `imageVariants` relationship and `is_favorite` to casts.
- **FileManagerService**: Updated `listFiles` to support `favorites_only` filter.
- **Image Processing**: Enhanced `ProcessImageJob` to trigger variant generation.

## [1.1.5] - 2026-01-16

### Changed
- **Code Quality**: Added return type declarations to Eloquent relationship methods for better IDE support and Laravel 10+ compatibility
- **Verified Compatibility**: Comprehensive code review confirms full compatibility with Laravel 10.x, 11.x, and 12.x

### Note
- Laravel 10/11/12 compatibility verified across all package components
- All core Laravel APIs used are stable across the supported versions
- No breaking changes or deprecated method usage found

## [1.2.0] - 2025-12-26

### Added
- **Laravel 12 Support**: Package now supports Laravel 10, 11, and 12
- **PHP 8.2 & 8.3 Support**: Added support for newer PHP versions
- **Intervention/Image v3 Support**: Now compatible with both v2.7 and v3.x (choose based on your needs)

### Changed
- **Extended PHP Requirement**: Now supports `^8.1|^8.2|^8.3`
- **Updated Dependencies**: All illuminate packages now support `^10.0|^11.0|^12.0`
- **Updated Dev Dependencies**: Orchestra Testbench and Pest packages updated for Laravel 12 compatibility

### Note
- Laravel 12 users must use PHP 8.2 or higher (Laravel 12 requirement)
- Existing Laravel 10 & 11 users can continue using PHP 8.1+
- Both Intervention/Image v2.7 and v3.x are supported - no migration required

## [1.1.4] - 2025-12-26

### Fixed
- **Critical**: Restored missing variable definitions in `ProcessImageJob` that caused "undefined variable $compressEnabled" error on fresh installations

## [1.1.3] - 2025-12-26

### Added
- **Enhanced PDF Preview**: PDFs now display significantly larger in the preview modal with improved readability
- **PDF Viewer Controls**: Added built-in PDF navigation toolbar, navigation panes, and optimized view parameters
- **Missing Migration**: Added `file_manager_settings` table migration that was previously missing

### Changed
- **PDF Display**: Removed size constraints and set minimum height of 600px for better PDF viewing experience
- **PDF Toolbar**: Added filename and document type display in preview modal

## [1.1.2] - 2025-12-23

### Added
- **Configurable Video Processing**: Added settings to enable/disable video thumbnails.
- **FFmpeg Customization**: Users can now specify custom paths for `ffmpeg` and `ffprobe` binaries in settings.
- **Diagnostic Tools**: Added a "Test FFmpeg" button in settings to verify binary connectivity and version info.
- **Setup Guidance**: Added direct links to FFmpeg documentation and setup help within the UI.

### Changed
- **Sequential Media Core**: Refactored `FileManagerService` to use Laravel Job Chaining. This ensures media processing (Compression, WebP, Thumbnails) completes *before* S3 synchronization, preventing data loss from premature local file deletion.
- **Job Reliability**: Processing jobs now gracefully handle missing disk files and binary execution errors.

## [1.1.1] - 2025-12-23

### Added
- **Hybrid S3 Serving**: Synced assets now continue to load from S3 even when synchronization is disabled in settings.

### Fixed
- "Call to a member function getCommand() on null" error in `S3SyncService` when S3 is disabled.
- Missing `bulkDownload` method and duplicate `dispatchS3Sync` declaration in `FileManagerService`.
- Strict `s3_enabled` guards for all new sync actions and bulk sync requests.

## [1.1.0] - 2025-12-23



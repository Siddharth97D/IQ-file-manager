# Changelog

All notable changes to this project will be documented in this file.

## [1.1.0] - 2025-12-23


### Added
- AWS S3 SDK dependency (`aws/aws-sdk-php`).
- Bulk Restore and Bulk Delete functionality in the Trash view.
- Pagination for the Trash view.
- Floating bulk action toolbars in Dashboard and Trash views.

### Changed
- Simplified Dashboard 3-dot menu to only include the "Rename" option.
- Optimized Alpine.js state management to avoid proxy issues during modal editing.
- Standardized file/folder selection logic across grid and list views.
- Downgraded `orchestra/testbench` to `^8.0` for PHP 8.1 compatibility.

### Fixed
- Broken "Rename" functionality for both files and folders.
- Trash view rendering issues when empty or containing many items.
- "Rename" API routing to correctly distinguish between files and folders.

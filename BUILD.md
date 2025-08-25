# Build Process for MemberPress Gift Reporter

This document explains how to create consistent distribution packages for the MemberPress Gift Reporter plugin.

## 🚀 Quick Build

### Option 1: Using the build script (Recommended)
```bash
./build.sh
```

### Option 2: Using npm scripts
```bash
# Build assets and create distribution package
npm run package

# Create distribution package only
npm run dist
```

## 📦 What the build process does

1. **Cleans up** previous build artifacts
2. **Copies files** to a temporary build directory, excluding development files
3. **Creates a zip file** with the correct folder structure
4. **Cleans up** temporary files
5. **Shows package contents** for verification

## 🗂️ Files included in distribution

- ✅ Core plugin files (`memberpress-gift-reporter.php`, `uninstall.php`)
- ✅ Includes directory (`class-admin.php`, `class-gift-report.php`)
- ✅ Assets (CSS and JS files, both source and minified)
- ✅ Languages directory (`.pot` file for translations)
- ✅ Documentation (`README.md`, `INSTALL.md`, `CHANGELOG.md`, `LICENSE`)
- ✅ Configuration files (`composer.json`, `package.json`, `phpcs.xml`)

## ❌ Files excluded from distribution

- 🔒 Development files (`.git/`, `build.sh`, `.distignore`)
- 🔒 IDE files (`.vscode/`, `.idea/`, `*.swp`, `*.swo`)
- 🔒 OS files (`.DS_Store`, `Thumbs.db`)
- 🔒 Temporary files (`*.tmp`, `*.log`, `*.bak`)
- 🔒 Node modules and lock files (`node_modules/`, `package-lock.json`)
- 🔒 Composer files (`vendor/`, `composer.lock`)
- 🔒 Build artifacts (`build/`, `*.zip`)

## 🎯 Output

The build process creates:
- **File:** `memberpress-gift-reporter.zip`
- **Size:** ~44KB
- **Structure:** When extracted, creates a `memberpress-gift-reporter/` folder

## 🔧 Customization

### Adding new exclusions
Edit the `.distignore` file to add new patterns for files/directories that should be excluded from the distribution.

### Modifying the build script
The `build.sh` script uses `rsync` with exclusion patterns. You can modify the exclusions in the script if needed.

## 🚨 Important Notes

1. **Always run the build script** from the plugin root directory
2. **Test the zip file** by extracting it to ensure it works correctly
3. **Version consistency** - the zip file will always create a `memberpress-gift-reporter/` folder regardless of version
4. **No conflicts** - the build process ensures no development files are included that could cause issues

## 🧪 Testing the distribution

After building, you can test the distribution by:

1. Extracting the zip file to a temporary location
2. Uploading it to a test WordPress site
3. Activating the plugin to ensure it works correctly
4. Checking that no development files are present

## 📋 Build Checklist

Before distributing:
- [ ] Run `./build.sh`
- [ ] Verify the zip file size (~44KB)
- [ ] Extract and test the zip file
- [ ] Check that no development files are included
- [ ] Verify the plugin activates without errors
- [ ] Test basic functionality

## 🔄 Version Updates

When updating the plugin version:

1. Update version in `memberpress-gift-reporter.php`
2. Update version in `package.json`
3. Update `CHANGELOG.md` with new changes
4. Run `./build.sh` to create the new distribution package
5. Test the new package before distribution

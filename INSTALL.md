# Installation Guide

## Quick Installation

### Method 1: Upload via WordPress Admin

1. **Download the plugin** from GitHub (click the green "Code" button → "Download ZIP")
2. **Go to WordPress Admin** → **Plugins** → **Add New**
3. **Click "Upload Plugin"** at the top of the page
4. **Choose the ZIP file** you downloaded
5. **Click "Install Now"**
6. **Activate the plugin**
7. **Go to MemberPress** → **Gift Report** to view reports

### Method 2: Upload via FTP

1. **Download the plugin** from GitHub
2. **Extract the ZIP file**
3. **Upload the `memberpress-gift-reporter` folder** to `/wp-content/plugins/`
4. **Go to WordPress Admin** → **Plugins**
5. **Find "MemberPress Gift Reporter"** and click **Activate**
6. **Go to MemberPress** → **Gift Report** to view reports

## Requirements

- ✅ WordPress 5.0 or higher
- ✅ PHP 7.4 or higher
- ✅ MemberPress plugin (active)
- ✅ MemberPress Gifting add-on (active)
- ✅ MySQL 5.7+ or MariaDB 10.2+

## First Time Setup

1. **Activate the plugin**
2. **Go to MemberPress** → **Gift Report**
3. **Verify MemberPress Gifting is active** (you'll see a notice if it's not)
4. **Start viewing your gift reports!**

## Using the Plugin

### Viewing Reports
- Navigate to **MemberPress** → **Gift Report** in your WordPress admin
- View summary statistics including total gifts, claim rate, and revenue
- Browse the detailed gift transaction table
- Export data to CSV for further analysis

### Exporting Data
- Click the **"Download CSV Report"** button
- The CSV file will contain all gift transaction data
- Use the exported data in Excel, Google Sheets, or other analysis tools

## Troubleshooting

### Plugin won't activate
- Check that MemberPress is installed and active
- Verify PHP version is 7.4 or higher
- Check WordPress version is 5.0 or higher

### No data appears in reports
- Ensure MemberPress Gifting add-on is active
- Verify you have completed gift transactions
- Check that you have admin permissions

### Export issues
- Ensure your server allows file downloads
- Check PHP memory limit (may need to increase for large datasets)
- Verify write permissions in WordPress uploads directory

## Support

If you need help:
1. Check the troubleshooting section above
2. Review the main README.md file
3. Create an issue on GitHub with detailed information

## Updates

To update the plugin:
1. Download the latest version from GitHub
2. Deactivate the current plugin
3. Upload the new version
4. Reactivate the plugin

Your report data and settings will be preserved during updates.

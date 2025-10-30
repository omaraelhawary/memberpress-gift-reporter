=== MemberPress Gift Reporter ===
Contributors: omaraelhawary
Tags: memberpress, gifting, reports, analytics, csv export
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin that generates comprehensive reports for the MemberPress Gifting add-on, showing the linkage between gift givers and recipients.

## 📝 Changelog

### Version 1.3.0 (2025-10-30)

#### 🎉 New Features
- **Resend Gift Email**: New action button to resend the gift email to the gifter with redemption link
- **Copy Redemption Link**: New action button to copy the redemption link directly to clipboard
- **Actions Column**: Added dedicated actions column in the report table with visual feedback

#### 🎨 Improvements
- **Enhanced User Experience**: Action buttons with tooltips, loading states, and success animations
- **Quick Customer Support**: Easily resend gift information when customers lose their original email
- **Manual Follow-ups**: Copy redemption links for manual outreach via email, chat, or phone

#### 🔧 Technical Updates
- Added action buttons UI with responsive design and accessibility features
- Implemented clipboard API with fallback support for older browsers
- Enhanced CSS with button animations and visual states (loading, success, hover)

### Version 1.2.0 (2025-10-29)

#### 🎉 New Features
- **Transaction ID Filter**: Search and filter by gift purchase transaction ID
- **Claim Transaction ID Filter**: Search and filter by gift redemption/claim transaction ID

#### 🎨 Improvements
- **Enhanced Summary Display**: Redesigned summary statistics with improved inline layout for better readability
- **Increased Filter Count**: Now featuring 10 powerful filters (up from 8) for even more precise data analysis

#### 🔧 Technical Updates
- Updated filter validation and sanitization for new transaction ID fields
- Enhanced JavaScript filtering logic to support transaction ID searches
- Improved data query system to handle transaction number lookups

### Version 1.1.0
- Advanced filtering system with 8 filters
- Date range filtering for purchases and redemptions
- Email filtering for gifters and recipients
- Product/membership filtering
- Gift status filtering
- Filtered CSV exports
- Modern admin interface

### Version 1.0.0
- Initial release
- Basic gift reporting functionality
- CSV export capability
- REST API endpoints

## 🎁 Features

- **Complete Gift Tracking**: Track gift purchases, redemptions, and status
- **Quick Actions**: Built-in action buttons for each gift transaction
  - 📧 Resend gift email to the gifter
  - 🔗 Copy redemption link to clipboard
- **Advanced Filtering System**: 10 powerful filters for precise data analysis
  - Date range filtering (purchase and redemption dates)
  - Gift status filtering (claimed/unclaimed)
  - Product/membership filtering
  - Email filtering (gifter and recipient)
  - Transaction ID filtering (purchase and claim transactions)
- **Smart Data Detection**: Intelligent messaging for no-data scenarios
- **Comprehensive Reports**: View detailed gift transaction data
- **Filtered CSV Export**: Export only filtered data, not all data
- **REST API**: Programmatic access to report data
- **Modern Admin Interface**: Clean, responsive, and user-friendly dashboard
- **Mobile Optimized**: Touch-friendly interface for all devices
- **Security**: Admin-only access with proper permissions

## 📸 Screenshots

### Admin Dashboard
![MemberPress Gift Report Dashboard](screenshots/dashboard.png)

*The MemberPress Gift Report dashboard showing advanced filtering options, summary statistics, and detailed gift transaction data with export functionality.*

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MemberPress plugin (active)
- MemberPress Gifting add-on (active)
- MySQL 5.7+ or MariaDB 10.2+

## 🚀 Installation

1. Download the plugin files
2. Upload the `memberpress-gift-reporter` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to **MemberPress** → **Gift Report** to view reports

## 📊 Usage

### Admin Dashboard

1. Go to **WordPress Admin** → **MemberPress** → **Gift Report**
2. Use the advanced filtering system to narrow down your data:
   - **Date Filters**: Filter by purchase date range
   - **Status Filters**: Filter by claimed/unclaimed status
   - **Product Filters**: Filter by specific memberships
   - **Email Filters**: Search by gifter or recipient email
   - **Transaction ID Filters**: Search by purchase or claim transaction ID
   - **Redemption Filters**: Filter by when gifts were claimed
3. View summary statistics and detailed gift data
4. Use action buttons in the **Actions** column:
   - 📧 **Resend Email**: Click to resend the gift email to the gifter
   - 🔗 **Copy Link**: Click to copy the redemption link to your clipboard
5. Click **Download CSV Report** to export filtered data
6. Use **Clear Filters** to reset all filters quickly

### REST API

Get report data programmatically:

```php
// Get report data
$response = wp_remote_get(home_url('/wp-json/mpgr/v1/report'));

// Export CSV
$response = wp_remote_post(home_url('/wp-json/mpgr/v1/export'));
```

## 📈 Report Data

The plugin tracks and reports on:

### Gift Purchase Information
- Transaction ID and number
- Purchase date and amount
- Gifter details (user ID, email, name)

### Product Information
- Product ID and name
- Gifted membership details

### Coupon Information
- Generated coupon code
- Coupon ID and status

### Redemption Information
- Recipient details (user ID, email, name)
- Redemption date and transaction
- Gift status (claimed/unclaimed/invalid)

### Summary Statistics
- Total gifts purchased (filtered)
- Claimed vs unclaimed gifts (filtered)
- Claim rate percentage (filtered)
- Total revenue generated (filtered)

### Advanced Filtering
- **Date Range Filtering**: Filter by purchase or redemption dates
- **Status Filtering**: Filter by gift status (claimed/unclaimed)
- **Product Filtering**: Filter by specific memberships
- **Email Filtering**: Search by gifter or recipient email addresses
- **Transaction ID Filtering**: Search by purchase transaction ID or claim transaction ID
- **Combined Filtering**: Use multiple filters simultaneously for precise data analysis

## 🔧 Configuration

### Customization

You can customize the plugin by:

1. **Styling**: Modify `assets/css/style.css`
2. **Functionality**: Extend the `MPGR_Gift_Report` class
3. **Admin Interface**: Customize `includes/class-admin.php`

## 🔒 Security

- **Admin-only access**: Reports require `manage_options` capability
- **Nonce verification**: All AJAX requests are secured
- **Data sanitization**: All user inputs are sanitized
- **SQL preparation**: All database queries use prepared statements

## 🐛 Troubleshooting

### No Data Appears

1. **Check MemberPress**: Ensure MemberPress is active
2. **Check Gifting Add-on**: Verify MemberPress Gifting is active
3. **Check Permissions**: Ensure you have admin access
4. **Check Database**: Verify gift transactions exist

### Export Issues

1. **Check File Permissions**: Ensure PHP can write to temp directory
2. **Check Memory Limit**: Large datasets may require more memory
3. **Check Timeout**: Long-running exports may timeout

### Styling Issues

1. **Clear Cache**: Clear any caching plugins
2. **Check CSS**: Verify CSS files are loading
3. **Check Conflicts**: Disable other plugins to test

## 📄 License

This plugin is licensed under the GPL v2 or later.

## 📞 Contact

- **Email**: omaraelhawary@gmail.com

---

**Note**: This plugin requires MemberPress and the MemberPress Gifting add-on to function properly.

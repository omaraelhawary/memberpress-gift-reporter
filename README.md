# MemberPress Gift Reporter

A WordPress plugin that generates comprehensive reports for the MemberPress Gifting add-on, showing the linkage between gift givers and recipients.

## 🎁 Features

- **Complete Gift Tracking**: Track gift purchases, redemptions, and status
- **Comprehensive Reports**: View detailed gift transaction data
- **CSV Export**: Download reports in spreadsheet format
- **REST API**: Programmatic access to report data
- **Admin Interface**: Clean, user-friendly admin dashboard
- **Security**: Admin-only access with proper permissions

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MemberPress plugin (active)
- MemberPress Gifting add-on (active)
- MySQL 5.7+ or MariaDB 10.2+

## 🚀 Installation

### Method 1: Upload Plugin Files

1. Download the plugin files
2. Upload the `memberpress-gift-reporter` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to **MemberPress** → **Gift Report** to view reports

### Method 2: Git Clone

```bash
cd wp-content/plugins
git clone https://github.com/omaraelhawary/memberpress-gift-reporter.git
```

## 📊 Usage

### Admin Dashboard

1. Go to **WordPress Admin** → **MemberPress** → **Gift Report**
2. View summary statistics and detailed gift data
3. Click **Download CSV Report** to export data

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
- Total gifts purchased
- Claimed vs unclaimed gifts
- Claim rate percentage
- Total revenue generated

## 🔧 Configuration

### Customization

You can customize the plugin by:

1. **Styling**: Modify `assets/css/style.css`
2. **Functionality**: Extend the `MPGR_Gift_Report` class
3. **Admin Interface**: Customize `includes/class-admin.php`

## 🛠️ Development

### File Structure

```
memberpress-gift-reporter/
├── memberpress-gift-reporter.php    # Main plugin file
├── includes/
│   ├── class-gift-report.php        # Core report functionality
│   └── class-admin.php              # Admin interface
├── assets/
│   ├── css/
│   │   └── style.css                # Frontend styles
│   └── js/
│       └── script.js                # Frontend JavaScript
├── languages/                       # Translation files
└── README.md                        # This file
```

### Database Queries

The plugin uses optimized SQL queries to join:

- `wp_mepr_transactions` - Main transaction data
- `wp_mepr_transaction_meta` - Gift-specific metadata
- `wp_users` - User information
- `wp_usermeta` - User profile data
- `wp_posts` - Product and coupon information

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

## 📝 Changelog

### Version 1.0.0
- Initial release
- Basic gift reporting functionality
- CSV export capability
- Admin interface
- REST API endpoints

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This plugin is licensed under the GPL v2 or later.

## 🆘 Support

For support, please:

1. Check the troubleshooting section above
2. Search existing issues on GitHub
3. Create a new issue with detailed information

## 🙏 Credits

- Built for MemberPress community
- Uses WordPress coding standards
- Follows WordPress plugin development best practices

## 📞 Contact

- **GitHub**: https://github.com/omaraelhawary
- **Email**: omaraelhawary@gmail.com

---

**Note**: This plugin requires MemberPress and the MemberPress Gifting add-on to function properly.

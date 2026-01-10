=== MemberPress Gift Reporter ===
Contributors: omaraelhawary
Tags: memberpress, gifting, reports, csv export, reminders
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.6.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Reporting plugin for MemberPress Gifting. Generates detailed reports linking gift givers and recipients with filtering, CSV export, and reminders.

== Description ==

**Note: This is an independent plugin developed by Omar ElHawary. It is not an official MemberPress plugin.**

MemberPress Gift Reporter is a powerful WordPress plugin that extends the functionality of the MemberPress Gifting add-on by providing comprehensive reporting and management tools. Track gift purchases, redemptions, and manage gift communications all from one convenient dashboard.

= Key Features =

* **Complete Gift Tracking**: Track gift purchases, redemptions, and status in real-time
* **Quick Actions**: Built-in action buttons for each gift transaction
  * Resend gift email to the gifter
  * Copy redemption link to clipboard
* **Bulk Operations**: Manage multiple gifts at once
  * Select all unclaimed gifts for bulk operations
  * Bulk resend reminder emails to multiple gifters
  * Batch processing with progress tracking
* **Automatic Reminder System**: Automated email reminders for unclaimed gifts
  * Daily cron schedule for efficient processing
  * Multiple customizable reminder schedules (hours or days)
  * Fully customizable email templates with variable support
  * Test email functionality to preview emails
  * Theme override support for email templates
* **Advanced Filtering System**: 10 powerful filters for precise data analysis
  * Date range filtering (purchase and redemption dates)
  * Gift status filtering (claimed/unclaimed)
  * Product/membership filtering
  * Email filtering (gifter and recipient)
  * Transaction ID filtering (purchase and claim transactions)
* **Smart Data Detection**: Intelligent messaging for no-data scenarios
* **Comprehensive Reports**: View detailed gift transaction data
* **Filtered CSV Export**: Export only filtered data, not all data
* **REST API**: Programmatic access to report data
* **Modern Admin Interface**: Clean, responsive, and user-friendly dashboard with tabbed navigation
* **Mobile Optimized**: Touch-friendly interface for all devices
* **Security**: Admin-only access with proper permissions

= Requirements =

* WordPress 5.0 or higher
* PHP 7.4 or higher
* MemberPress plugin (active)
* MemberPress Gifting add-on (active)
* MySQL 5.7+ or MariaDB 10.2+

= Developer Information =

This plugin is developed and maintained independently by Omar ElHawary. It is not affiliated with, endorsed by, or officially supported by MemberPress. For support, feature requests, or bug reports, please contact the plugin developer directly.

== Installation ==

1. Download the plugin files
2. Upload the `memberpress-gift-reporter` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to **MemberPress** → **Gift Report** to view reports

= Usage =

== Admin Dashboard ==

The plugin has two main tabs: **Gift Report** and **Reminders**.

=== Gift Report Tab ===

1. Go to **WordPress Admin** → **MemberPress** → **Gift Report** (Report tab)
2. Use the advanced filtering system to narrow down your data:
   * **Date Filters**: Filter by purchase date range
   * **Status Filters**: Filter by claimed/unclaimed status
   * **Product Filters**: Filter by specific memberships
   * **Email Filters**: Search by gifter or recipient email
   * **Transaction ID Filters**: Search by purchase or claim transaction ID
   * **Redemption Filters**: Filter by when gifts were claimed
3. View summary statistics and detailed gift data
4. Use action buttons in the **Actions** column:
   * **Resend Email**: Click to resend the gift email to the gifter
   * **Copy Link**: Click to copy the redemption link to your clipboard
5. For bulk operations:
   * Use **Select All Unclaimed** to quickly select all unclaimed gifts
   * Click **Send Reminder Emails to Selected** to send emails to multiple gifters at once
6. Click **Download CSV Report** to export filtered data
7. Use **Clear Filters** to reset all filters quickly

=== Reminders Tab ===

1. Go to **WordPress Admin** → **MemberPress** → **Gift Report** → **Reminders** tab
2. Enable automatic reminders by checking **Enable Automatic Reminders**
3. Configure reminder schedules:
   * Add multiple reminder schedules (hours or days after purchase)
   * Each schedule can have different delays (e.g., 7 days, 14 days, 30 days)
   * Reminders are sent automatically via daily cron job
4. Customize email content:
   * **Email Subject**: Customize the subject line with variables
   * **Email Body**: Use the rich text editor to customize the email content
   * Available variables: `{$product_name}`, `{$redemption_link}`, `{$site_name}`, `{$user_email}`, `{$user_first_name}`, etc.
5. Test your email:
   * Click **Send Test Email** to preview how the email will look
   * Enter a test email address and send a sample email
6. Click **Save Settings** to apply your changes

== REST API ==

Get report data programmatically:

`// Get report data`
`$response = wp_remote_get(home_url('/wp-json/mpgr/v1/report'));`

`// Export CSV`
`$response = wp_remote_post(home_url('/wp-json/mpgr/v1/export'));`

== Report Data ==

The plugin tracks and reports on:

* **Gift Purchase Information**: Transaction ID and number, purchase date and amount, gifter details (user ID, email, name)
* **Product Information**: Product ID and name, gifted membership details
* **Coupon Information**: Generated coupon code, coupon ID and status
* **Redemption Information**: Recipient details (user ID, email, name), redemption date and transaction, gift status (claimed/unclaimed/invalid)
* **Summary Statistics**: Total gifts purchased (filtered), claimed vs unclaimed gifts (filtered), claim rate percentage (filtered), total revenue generated (filtered)

== Frequently Asked Questions ==

= Do I need MemberPress to use this plugin? =

Yes, this plugin requires both MemberPress and the MemberPress Gifting add-on to be installed and activated.

= Is this an official MemberPress plugin? =

No, this is an independent plugin developed by Omar ElHawary. It is not affiliated with, endorsed by, or officially supported by MemberPress.

= Can I customize the reminder emails? =

Yes! You can customize the email content in the Reminders tab, and you can override the email templates by copying them to your theme directory. See the Configuration section for details.

= How do I export gift data? =

Use the filtering system to narrow down the data you want, then click the "Download CSV Report" button. Only the filtered data will be exported.

= Can I use this with a child theme? =

Yes, the plugin fully supports child themes. Template overrides will check the child theme first, then the parent theme, then fall back to the plugin's default templates.

= How do I contact the developer? =

You can contact the developer at omaraelhawary@gmail.com

== Screenshots ==

1. Admin Dashboard - The MemberPress Gift Report dashboard showing advanced filtering options, summary statistics, and detailed gift transaction data with export functionality.

== Changelog ==

= 1.6.2 =
* Updated tested WordPress version to 6.9

= 1.6.1 =
* Fixed Gift Checkout Links: Fixed issue where gift redemption links were using hardcoded `/memberpress-checkout/` path instead of actual product URLs
* Improved Link Generation: Redemption links now use the product's actual URL (matching MemberPress Gifting plugin behavior) with coupon parameter appended
* Better Compatibility: Links now work correctly regardless of custom checkout page configurations or permalink structures
* Added `generate_redemption_url()` helper function to properly generate gift redemption URLs using product URLs
* Updated all redemption link generation to use product URL method instead of hardcoded paths
* Improved fallback handling for cases where MemberPress classes aren't available

= 1.6.0 =
* Weekly Summary Emails: New automated weekly summary email feature that sends administrators a comprehensive overview of gift activity
* Weekly Cron Schedule: Added custom weekly cron schedule support for automated weekly reports
* Gift Activity Overview: Weekly summaries include total gifts, claimed/unclaimed statistics, revenue data, and top products
* Configurable Weekly Reports: Enable/disable weekly summary emails with customizable settings
* Enhanced Cron Management: Improved cron job scheduling and cleanup for both daily reminders and weekly summaries
* Better Plugin Architecture: Added support for multiple scheduled tasks with proper initialization and cleanup
* Added `MPGR_Weekly_Summary` class for weekly email functionality
* Implemented weekly cron schedule registration via `cron_schedules` filter
* Enhanced plugin activation/deactivation hooks to manage weekly summary cron jobs
* Improved cron hook registration and cleanup processes

= 1.5.2 =
* Fixed reminder scheduling issues and improved reliability
* Enhanced reminder email delivery tracking
* Improved cron job management for reminder system
* Fixed edge cases in reminder schedule processing

= 1.5.1 =
* Fixed various bugs and issues from version 1.5.0
* Improved stability and performance

= 1.5.0 =
* Daily Cron Schedule: Changed reminder cron schedule from hourly to daily for better performance
* Orphaned Hook Cleanup: Automatic cleanup of old/orphaned cron hooks on plugin load
* Better UI Layout: Fixed checkbox alignment with description text in reminder settings
* Code Cleanup: Removed debug code and console.log statements from production code
* Updated cron schedule from hourly to daily for reminder processing
* Added automatic cleanup for orphaned cron hooks (`mpgr_send_reminders`, `mpgr_check_reminders`, `mpgr_send_reminder_emails`)
* Improved code quality by removing debug statements and comments
* Enhanced initialization process to clean up old hooks on every load

= 1.4.1 =
* Email Template Override Support: Customers can now override the reminder email template by placing a custom template in their theme directory
* Template System: New template rendering system with theme override capability following WordPress standards
* Customizable Emails: Easy customization of reminder email content, styling, and layout
* Theme Integration: Seamless integration with child themes and parent themes
* Developer Friendly: Template override system with clear documentation and examples
* Refactored email sending methods to use template system
* Added template location methods with theme override support
* Created default email template file in `views/emails/reminder-email.php`
* Enhanced documentation with template override instructions

= 1.4.0 =
* Bulk Resend Gift Emails: New bulk action to send reminder emails to multiple unclaimed gifts at once
* Select All Unclaimed: Quick selection feature to easily select all unclaimed gifts for bulk operations
* Batch Processing: Smart batch processing for bulk email sending with progress tracking
* Enhanced Bulk Operations: Improved UI for managing multiple gift emails simultaneously
* Better User Experience: Streamlined workflow for sending reminder emails to gifters
* Progress Feedback: Clear feedback on bulk operations with success/failure counts
* Added bulk email handler with rate limiting and error handling
* Implemented checkbox selection system for bulk actions
* Enhanced email validation and delivery tracking
* Improved database queries for better performance on bulk operations

= 1.3.0 =
* Resend Gift Email: New action button to resend the gift email to the gifter with redemption link
* Copy Redemption Link: New action button to copy the redemption link directly to clipboard
* Actions Column: Added dedicated actions column in the report table with visual feedback
* Enhanced User Experience: Action buttons with tooltips, loading states, and success animations
* Quick Customer Support: Easily resend gift information when customers lose their original email
* Manual Follow-ups: Copy redemption links for manual outreach via email, chat, or phone
* Added action buttons UI with responsive design and accessibility features
* Implemented clipboard API with fallback support for older browsers
* Enhanced CSS with button animations and visual states (loading, success, hover)

= 1.2.0 =
* Transaction ID Filter: Search and filter by gift purchase transaction ID
* Claim Transaction ID Filter: Search and filter by gift redemption/claim transaction ID
* Enhanced Summary Display: Redesigned summary statistics with improved inline layout for better readability
* Increased Filter Count: Now featuring 10 powerful filters (up from 8) for even more precise data analysis
* Updated filter validation and sanitization for new transaction ID fields
* Enhanced JavaScript filtering logic to support transaction ID searches
* Improved data query system to handle transaction number lookups

= 1.1.0 =
* Advanced filtering system with 8 filters
* Date range filtering for purchases and redemptions
* Email filtering for gifters and recipients
* Product/membership filtering
* Gift status filtering
* Filtered CSV exports
* Modern admin interface

= 1.0.0 =
* Initial release
* Basic gift reporting functionality
* CSV export capability
* REST API endpoints

== Upgrade Notice ==

= 1.6.2 =
Prepared for WordPress.org submission. Update recommended for all users.

= 1.6.1 =
Fixed gift redemption link generation to use actual product URLs instead of hardcoded paths. Update recommended for all users.

= 1.6.0 =
New weekly summary email feature added. Update to get automated weekly reports of gift activity.

= 1.5.0 =
Improved cron job management and performance. Update recommended for all users.

== Configuration ==

= Email Template Overrides =

You can customize the reminder email template by copying it to your theme directory. This allows you to modify the email content, styling, and layout without losing your changes when the plugin updates.

**How to Override the Reminder Email Template:**

1. Copy the template file to your theme directory:
   * Copy from: `wp-content/plugins/memberpress-gift-reporter/views/emails/reminder-email.php`
   * Copy to: `wp-content/themes/your-theme/memberpress-gift-reporter/emails/reminder-email.php`

2. Create the directory structure in your theme:
   * Create a folder: `memberpress-gift-reporter`
   * Inside that, create a folder: `emails`
   * Place the template file: `reminder-email.php`

3. Customize the template to your needs. The template receives these variables (MemberPress style format):
   * `{$product_name}` - The name of the gifted product/membership
   * `{$redemption_link}` - The URL where recipients can redeem the gift
   * `{$site_name}` or `{$blogname}` - The name of your website
   * `{$user_login}` - The gifter's username
   * `{$user_email}` - The gifter's email address
   * `{$user_first_name}` - The gifter's first name
   * `{$user_last_name}` - The gifter's last name

**Child Theme Support:**

If you're using a child theme, the plugin will check in this order:
1. Child theme directory: `your-child-theme/memberpress-gift-reporter/emails/reminder-email.php`
2. Parent theme directory: `your-parent-theme/memberpress-gift-reporter/emails/reminder-email.php`
3. Plugin directory: `memberpress-gift-reporter/views/emails/reminder-email.php` (default)

This ensures your customizations persist even after plugin updates!

You can also override the email header template:
* Copy `views/emails/reminder-email-header.php` to `your-theme/memberpress-gift-reporter/emails/reminder-email-header.php`

== Security ==

* **Admin-only access**: Reports require `manage_options` capability
* **Nonce verification**: All AJAX requests are secured
* **Data sanitization**: All user inputs are sanitized
* **SQL preparation**: All database queries use prepared statements

== Troubleshooting ==

= No Data Appears =

1. Check MemberPress: Ensure MemberPress is active
2. Check Gifting Add-on: Verify MemberPress Gifting is active
3. Check Permissions: Ensure you have admin access
4. Check Database: Verify gift transactions exist

= Export Issues =

1. Check File Permissions: Ensure PHP can write to temp directory
2. Check Memory Limit: Large datasets may require more memory
3. Check Timeout: Long-running exports may timeout

= Reminder Email Issues =

1. Check Cron Jobs: Verify WP-Cron is working (check if scheduled tasks run)
2. Check Email Settings: Ensure WordPress email is configured correctly
3. Check Reminder Settings: Verify reminders are enabled in the Reminders tab
4. Check Reminder Schedules: Ensure at least one reminder schedule is configured
5. Test Email: Use the "Send Test Email" button to verify email delivery
6. Check Email Template: Verify the email template file exists and is readable

= Styling Issues =

1. Clear Cache: Clear any caching plugins
2. Check CSS: Verify CSS files are loading
3. Check Conflicts: Disable other plugins to test

== Support ==

For support, feature requests, or bug reports, please contact:
* **Email**: omaraelhawary@gmail.com

== Credits ==

This plugin is developed and maintained independently by Omar ElHawary. It is not affiliated with, endorsed by, or officially supported by MemberPress.

== License ==

This plugin is licensed under the GPL v2 or later.

<?php
/**
 * Reminder Email Header Template
 * 
 * This template can be overridden by copying it to your theme directory:
 * your-theme/memberpress-gift-reporter/emails/reminder-email-header.php
 * 
 * Available variables (same as MemberPress emails):
 * @var string $product_name The name of the gifted product
 * @var string $redemption_link The URL where the recipient can redeem the gift
 * @var string $site_name The name of the website
 * @var string $user_login The user's login name
 * @var string $user_email The user's email address
 * @var string $user_first_name The user's first name
 * @var string $user_last_name The user's last name
 * @var string $blogname The site name
 * 
 * @package MemberPressGiftReporter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( isset( $product_name ) ? $product_name : '' ); ?></title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .content { background-color: #ffffff; padding: 20px; border-radius: 8px; border: 1px solid #e9ecef; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; color: #6c757d; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; color: #2c3e50;">🎁 Gift Membership Purchase</h1>
    </div>
    <div class="content">


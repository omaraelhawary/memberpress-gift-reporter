<?php
/**
 * Reminder Email Template
 * 
 * This template can be overridden by copying it to your theme directory:
 * your-theme/memberpress-gift-reporter/emails/reminder-email.php
 * 
 * Available variables:
 * @var string $product_name The name of the gifted product
 * @var string $redemption_link The URL where the recipient can redeem the gift
 * @var string $site_name The name of the website
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
    <title><?php echo esc_html( $product_name ); ?></title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .content { background-color: #ffffff; padding: 20px; border-radius: 8px; border: 1px solid #e9ecef; }
        .coupon-code { background-color: #e3f2fd; padding: 15px; border-radius: 6px; border-left: 4px solid #2196f3; margin: 20px 0; font-family: monospace; font-size: 16px; font-weight: bold; }
        .redemption-link { background-color: #f3e5f5; padding: 15px; border-radius: 6px; border-left: 4px solid #9c27b0; margin: 20px 0; }
        .redemption-link a { color: #9c27b0; text-decoration: none; font-weight: bold; }
        .redemption-link a:hover { text-decoration: underline; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; color: #6c757d; font-size: 14px; }
        .greeting { font-size: 18px; font-weight: bold; margin-bottom: 20px; }
        .product-name { font-weight: bold; color: #2c3e50; }
        .thank-you { font-style: italic; color: #27ae60; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; color: #2c3e50;">🎁 Gift Membership Purchase</h1>
    </div>
    
    <div class="content">
        <div class="greeting">Hello!</div>
        
        <p>You have purchased a gift membership for <span class="product-name"><?php echo esc_html( $product_name ); ?></span>.</p>
        
        <div class="redemption-link">
            <strong>The recipient can redeem this gift by visiting:</strong><br>
            <a href="<?php echo esc_url( $redemption_link ); ?>"><?php echo esc_html( $redemption_link ); ?></a>
        </div>
        
        <p class="thank-you">Thank you for your purchase!</p>
        
        <div class="footer">
            <p>Best regards,<br>
            <strong><?php echo esc_html( $site_name ); ?></strong></p>
        </div>
    </div>
</body>
</html>


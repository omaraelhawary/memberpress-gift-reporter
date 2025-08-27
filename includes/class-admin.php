<?php
/**
 * Admin Class
 * 
 * @package MemberPressGiftReporter
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin functionality
 */
class MPGR_Admin {
    
    /**
     * Constructor
     */
	public function __construct() {
		// Add admin menu.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// Add plugin action links.
		add_filter( 'plugin_action_links_' . MPGR_PLUGIN_BASENAME, array( $this, 'add_plugin_links' ) );

		// Add admin notices.
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}
    
    /**
     * Add admin menu
     */
	public function add_admin_menu() {
		add_submenu_page(
			'memberpress',
			__( 'Gift Report', 'memberpress-gift-reporter' ),
			__( 'Gift Report', 'memberpress-gift-reporter' ),
			'manage_options',
			'memberpress-gift-report',
			array( $this, 'admin_page' )
		);
	}
    

    
    /**
     * Add plugin action links
     */
	public function add_plugin_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=memberpress-gift-report' ) . '">' . __( 'View Report', 'memberpress-gift-reporter' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}
    
    /**
     * Admin notices
     */
	public function admin_notices() {
		// Check if MemberPress Gifting is active.
		if ( ! $this->is_gifting_active() ) {
			echo '<div class="notice notice-warning is-dismissible">';
			echo '<p><strong>MemberPress Gift Reporter:</strong> ' . __( 'MemberPress Gifting add-on is not active. This plugin requires the MemberPress Gifting add-on to function properly.', 'memberpress-gift-reporter' ) . '</p>';
			echo '</div>';
		}
	}
    
    /**
     * Check if MemberPress Gifting is active
     */
	private function is_gifting_active() {
		return class_exists( 'memberpress\gifting\models\Gift' );
	}
	

    
    /**
     * Admin page
     */
	public function admin_page() {
		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'memberpress-gift-reporter' ) );
		}

		echo '<div class="wrap">';

		// Check if MemberPress Gifting is active.
		if ( ! $this->is_gifting_active() ) {
			echo '<div class="notice notice-error">';
			echo '<p>' . __( 'MemberPress Gifting add-on is not active. Please activate it to use this report.', 'memberpress-gift-reporter' ) . '</p>';
			echo '</div>';
			echo '</div>';
			return;
		}

		// Verify nonce for filter requests (only when filters are being applied)
		if (!empty($_GET['date_from']) || !empty($_GET['date_to']) || !empty($_GET['gift_status']) || 
			!empty($_GET['product']) || !empty($_GET['gifter_email']) || !empty($_GET['recipient_email']) ||
			!empty($_GET['redemption_from']) || !empty($_GET['redemption_to'])) {
			
			if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'mpgr_filter_nonce')) {
				wp_die(__('Security check failed. Please try again.', 'memberpress-gift-reporter'));
			}
		}

		// Get filter parameters
		$filters = array();
		if (!empty($_GET['date_from'])) {
			$filters['date_from'] = sanitize_text_field($_GET['date_from']);
		}
		if (!empty($_GET['date_to'])) {
			$filters['date_to'] = sanitize_text_field($_GET['date_to']);
		}
		if (!empty($_GET['gift_status'])) {
			$filters['gift_status'] = sanitize_text_field($_GET['gift_status']);
		}
		if (!empty($_GET['product'])) {
			$filters['product'] = intval($_GET['product']);
		}
		if (!empty($_GET['gifter_email'])) {
			$filters['gifter_email'] = sanitize_email($_GET['gifter_email']);
		}
		if (!empty($_GET['recipient_email'])) {
			$filters['recipient_email'] = sanitize_email($_GET['recipient_email']);
		}
		if (!empty($_GET['redemption_from'])) {
			$filters['redemption_from'] = sanitize_text_field($_GET['redemption_from']);
		}
		if (!empty($_GET['redemption_to'])) {
			$filters['redemption_to'] = sanitize_text_field($_GET['redemption_to']);
		}

		// Display report.
		$gift_report = new MPGR_Gift_Report();
		$gift_report->display_report($filters);

		echo '</div>';
	}
    
}

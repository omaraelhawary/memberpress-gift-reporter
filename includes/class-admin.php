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

		// Display report.
		$gift_report = new MPGR_Gift_Report();
		$gift_report->display_report();

		echo '</div>';
	}
    
    
}

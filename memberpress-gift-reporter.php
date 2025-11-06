<?php
/**
 * Plugin Name: MemberPress Gift Reporter
 * Plugin URI: https://github.com/omaraelhawary/memberpress-gift-reporter
 * Description: Generate comprehensive reports for MemberPress Gifting add-on, showing the linkage between gift givers and recipients.
 * Version: 1.5.2
 * Author: Omar ElHawary
 * Author URI: https://www.linkedin.com/in/omaraelhawary/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: memberpress-gift-reporter
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * 
 * @package MemberPressGiftReporter
 * @version 1.5.2
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'MPGR_VERSION', '1.5.2' );
define( 'MPGR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MPGR_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'MPGR_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin class
 */
class MemberPressGiftReporter {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
     */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
    
    /**
     * Constructor
     */
	private function __construct() {
		$this->init();
	}
    
    /**
     * Initialize the plugin
     */
	private function init() {
		// Register activation/deactivation hooks.
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Clean up orphaned cron hooks on every load (for existing installations)
		$old_hooks = array( 'mpgr_check_reminders', 'mpgr_send_reminder_emails', 'mpgr_send_reminders' );
		foreach ( $old_hooks as $hook ) {
			wp_clear_scheduled_hook( $hook );
		}

		// Load reminders class and register hooks immediately
		// This ensures the hooks are always available, even before plugins_loaded
		if ( file_exists( MPGR_PLUGIN_PATH . 'includes/class-reminders.php' ) ) {
			require_once MPGR_PLUGIN_PATH . 'includes/class-reminders.php';
			
			// Register cron hook immediately if class exists
			if ( class_exists( 'MPGR_Reminders' ) ) {
				add_action( 'mpgr_run_gift_reminders', array( 'MPGR_Reminders', 'run_scheduled_reminders' ) );
			}
		}

		// Check dependencies after plugins are loaded.
		add_action( 'plugins_loaded', array( $this, 'check_dependencies' ) );
	}
    
    /**
     * Check plugin dependencies
     */
	public function check_dependencies() {
		// Check if MemberPress is active.
		if ( ! $this->is_memberpress_active() ) {
			add_action( 'admin_notices', array( $this, 'memberpress_notice' ) );
			return;
		}

		// Load plugin.
		add_action( 'init', array( $this, 'load_plugin' ) );
	}
    
    /**
     * Check if MemberPress is active
     */
	private function is_memberpress_active() {
		// Check multiple ways to detect MemberPress.
		$checks = array(
			'MeprTransaction class' => class_exists( 'MeprTransaction' ),
			'MeprProduct class' => class_exists( 'MeprProduct' ),
			'mepr_get_plugin_name function' => function_exists( 'mepr_get_plugin_name' ),
			'MEPR_VERSION constant' => defined( 'MEPR_VERSION' ),
			'MeprOptions class' => class_exists( 'MeprOptions' ),
			'MeprUser class' => class_exists( 'MeprUser' ),
		);

		return in_array( true, $checks, true );
	}
    
    /**
     * Show notice if MemberPress is not active
     */
	public function memberpress_notice() {
		echo '<div class="notice notice-error">';
		echo '<p><strong>' . esc_html__( 'MemberPress Gift Reporter', 'memberpress-gift-reporter' ) . '</strong> ' . esc_html__( 'requires MemberPress to be installed and activated.', 'memberpress-gift-reporter' ) . '</p>';
		echo '<p>' . esc_html__( 'Please ensure that:', 'memberpress-gift-reporter' ) . '</p>';
		echo '<ul style="margin-left: 20px;">';
		echo '<li>' . esc_html__( 'MemberPress plugin is installed and activated', 'memberpress-gift-reporter' ) . '</li>';
		echo '<li>' . esc_html__( 'MemberPress is properly configured', 'memberpress-gift-reporter' ) . '</li>';
		echo '<li>' . esc_html__( 'You have a valid MemberPress license', 'memberpress-gift-reporter' ) . '</li>';
		echo '</ul>';
		echo '<p><a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">' . esc_html__( 'Go to Plugins', 'memberpress-gift-reporter' ) . '</a> | <a href="https://memberpress.com/" target="_blank">' . esc_html__( 'Get MemberPress', 'memberpress-gift-reporter' ) . '</a></p>';
		echo '</div>';
	}
    
    /**
     * Load the plugin
     */
	public function load_plugin() {
		// Text domain is automatically loaded by WordPress for plugins hosted on WordPress.org

		// Load the main report class.
		require_once MPGR_PLUGIN_PATH . 'includes/class-gift-report.php';

		// Initialize the report functionality.
		new MPGR_Gift_Report();

		// Ensure cron job is only scheduled if reminders are enabled
		// This cleans up any orphaned cron jobs from before the fix
		if ( class_exists( 'MPGR_Reminders' ) ) {
			$settings = MPGR_Reminders::get_settings();
			$timestamp = wp_next_scheduled( 'mpgr_run_gift_reminders' );
			
			if ( empty( $settings['enabled'] ) && $timestamp ) {
				// Reminders are disabled but cron is scheduled - remove it
				wp_unschedule_event( $timestamp, 'mpgr_run_gift_reminders' );
				wp_clear_scheduled_hook( 'mpgr_run_gift_reminders' );
			} elseif ( ! empty( $settings['enabled'] ) && ! $timestamp ) {
				// Reminders are enabled but cron is not scheduled - schedule it
				wp_schedule_event( time(), 'daily', 'mpgr_run_gift_reminders' );
			}
		}

		// Load admin functionality.
		if ( is_admin() ) {
			require_once MPGR_PLUGIN_PATH . 'includes/class-admin.php';
			new MPGR_Admin();
		}
	}
    
    /**
     * Plugin activation
     */
	public function activate() {
		// Create any necessary database tables or options.
		add_option( 'mpgr_version', MPGR_VERSION );

		// Load reminders class to ensure class exists
		require_once MPGR_PLUGIN_PATH . 'includes/class-reminders.php';
		
		// Register the cron hook (ensuring it's registered during activation)
		if ( class_exists( 'MPGR_Reminders' ) ) {
			add_action( 'mpgr_run_gift_reminders', array( 'MPGR_Reminders', 'run_scheduled_reminders' ) );
		}

		// Clean up any old/incorrect cron hooks
		$old_hooks = array( 'mpgr_check_reminders', 'mpgr_send_reminder_emails', 'mpgr_send_reminders' );
		foreach ( $old_hooks as $hook ) {
			$timestamp = wp_next_scheduled( $hook );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, $hook );
			}
			// Also unschedule all occurrences if multiple exist
			wp_clear_scheduled_hook( $hook );
		}

		// Unschedule existing event if it exists
		$timestamp = wp_next_scheduled( 'mpgr_run_gift_reminders' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'mpgr_run_gift_reminders' );
		}
		
		// Schedule reminder cron event
		// Use wp_schedule_event which will add the event to the cron array
		wp_schedule_event( time(), 'daily', 'mpgr_run_gift_reminders' );

		// Flush rewrite rules.
		flush_rewrite_rules();
	}
    
    /**
     * Plugin deactivation
     */
	public function deactivate() {
		// Unschedule reminder cron event.
		$timestamp = wp_next_scheduled( 'mpgr_run_gift_reminders' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'mpgr_run_gift_reminders' );
		}

		// Clean up if necessary.
		flush_rewrite_rules();
	}
}

// Initialize the plugin.
MemberPressGiftReporter::get_instance();

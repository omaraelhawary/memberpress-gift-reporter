<?php
/**
 * Plugin Name: MemberPress Gift Reporter
 * Plugin URI: https://github.com/omaraelhawary/memberpress-gift-reporter
 * Description: Generate comprehensive reports for MemberPress Gifting add-on, showing the linkage between gift givers and recipients.
 * Version: 1.1.0
 * Author: Omar ElHawary
 * Author URI: https://www.linkedin.com/in/omaraelhawary/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: memberpress-gift-reporter
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 * 
 * @package MemberPressGiftReporter
 * @version 1.1.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'MPGR_VERSION', '1.1.0' );
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
		echo '<p><strong>MemberPress Gift Reporter</strong> requires MemberPress to be installed and activated.</p>';
		echo '<p>Please ensure that:</p>';
		echo '<ul style="margin-left: 20px;">';
		echo '<li>MemberPress plugin is installed and activated</li>';
		echo '<li>MemberPress is properly configured</li>';
		echo '<li>You have a valid MemberPress license</li>';
		echo '</ul>';
		echo '<p><a href="' . admin_url( 'plugins.php' ) . '">Go to Plugins</a> | <a href="https://memberpress.com/" target="_blank">Get MemberPress</a></p>';
		echo '</div>';
	}
    
    /**
     * Load the plugin
     */
	public function load_plugin() {
		// Load text domain for internationalization.
		load_plugin_textdomain( 'memberpress-gift-reporter', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		// Load the main report class.
		require_once MPGR_PLUGIN_PATH . 'includes/class-gift-report.php';

		// Initialize the report functionality.
		new MPGR_Gift_Report();

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

		// Flush rewrite rules.
		flush_rewrite_rules();
	}
    
    /**
     * Plugin deactivation
     */
	public function deactivate() {
		// Clean up if necessary.
		flush_rewrite_rules();
	}
}

// Initialize the plugin.
MemberPressGiftReporter::get_instance();

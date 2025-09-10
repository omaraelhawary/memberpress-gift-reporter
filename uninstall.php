<?php
/**
 * Uninstall MemberPress Gift Reporter
 * 
 * This file is executed when the plugin is deleted from WordPress.
 * It removes all plugin-specific data but preserves MemberPress transaction data.
 * 
 * @package MemberPressGiftReporter
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Check if user has permissions to delete plugins.
if ( ! current_user_can( 'activate_plugins' ) ) {
	return;
}

// Remove plugin options.
delete_option( 'mpgr_version' );

// Remove any transients created by the plugin.
delete_transient( 'mpgr_report_cache' );
delete_transient( 'mpgr_summary_cache' );

// Clear any scheduled events.
wp_clear_scheduled_hook( 'mpgr_cleanup_cache' );

// Remove any custom capabilities if they were added.
$role = get_role( 'administrator' );
if ( $role ) {
	$role->remove_cap( 'view_memberpress_gift_reports' );
}

<?php
/**
 * Gift Report Class
 * 
 * @package MemberPressGiftReporter
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main gift report functionality
 */
class MPGR_Gift_Report {
    
    /**
     * Report data property
     */
    private $report_data = array();
    
    /**
     * Constructor
     */
	public function __construct() {
		// Handle AJAX requests.
		add_action( 'wp_ajax_mpgr_export_csv', array( $this, 'ajax_export_csv' ) );
		add_action( 'wp_ajax_mpgr_resend_gift_email', array( $this, 'ajax_resend_gift_email' ) );
		add_action( 'wp_ajax_mpgr_copy_redemption_link', array( $this, 'ajax_copy_redemption_link' ) );

		// Add REST API endpoint.
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}
    

    
    /**
     * AJAX export handler
     */
	public function ajax_export_csv() {
		// Verify nonce and permissions.
		if ( ! wp_verify_nonce( $_POST['nonce'], 'mpgr_export_csv' ) || ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Access denied', 'memberpress-gift-reporter' ) );
		}

		// Check rate limiting
		if ($this->is_rate_limited()) {
			wp_die( __( 'Rate limit exceeded. Please wait before trying again.', 'memberpress-gift-reporter' ) );
		}

		// Get and sanitize filter parameters
		$filters = $this->sanitize_ajax_filters($_POST);

		try {
			$this->generate_report(0, 0, $filters);
			$this->export_csv('memberpress_gift_report.csv', $filters);
		} catch (Exception $e) {
			wp_die( __( 'Error generating export. Please try again.', 'memberpress-gift-reporter' ) );
		}
	}
    
    /**
     * AJAX resend gift email handler
     */
	public function ajax_resend_gift_email() {
		// Verify nonce and permissions.
		if ( ! wp_verify_nonce( $_POST['nonce'], 'mpgr_resend_gift_email' ) || ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Access denied', 'memberpress-gift-reporter' ) );
		}

		$gift_transaction_id = intval($_POST['gift_transaction_id']);
		
		if (!$gift_transaction_id) {
			wp_send_json_error('Invalid gift transaction ID');
		}

		// Get gift transaction details
		global $wpdb;
		$gift_transaction = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}mepr_transactions WHERE id = %d",
			$gift_transaction_id
		));

		if (!$gift_transaction) {
			wp_send_json_error('Gift transaction not found');
		}

		// Get gift coupon ID
		$gift_coupon_id = $wpdb->get_var($wpdb->prepare(
			"SELECT meta_value FROM {$wpdb->prefix}mepr_transaction_meta 
			WHERE transaction_id = %d AND meta_key = '_gift_coupon_id'",
			$gift_transaction_id
		));

		if (!$gift_coupon_id) {
			wp_send_json_error('Gift coupon not found');
		}

		// Get coupon code
		$coupon_code = get_post_field('post_title', $gift_coupon_id);
		
		if (!$coupon_code) {
			wp_send_json_error('Coupon code not found');
		}

		// Get gifter email
		$gifter_email = get_userdata($gift_transaction->user_id)->user_email;
		
		if (!$gifter_email) {
			wp_send_json_error('Gifter email not found');
		}

		// Get product name
		$product_name = get_post_field('post_title', $gift_transaction->product_id);

		// Generate redemption link
		$redemption_link = home_url('/memberpress-checkout/?coupon=' . urlencode($coupon_code));

		// Send gift email
		$subject = sprintf(__('Your Gift Purchase - %s', 'memberpress-gift-reporter'), $product_name);
		
		// Create HTML email message with proper formatting
		$message = sprintf(
			'<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>%1$s</title>
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
        
        <p>You have purchased a gift membership for <span class="product-name">%1$s</span>.</p>
        
        <div class="redemption-link">
            <strong>The recipient can redeem this gift by visiting:</strong><br>
            <a href="%2$s">%2$s</a>
        </div>
        
        <p class="thank-you">Thank you for your purchase!</p>
        
        <div class="footer">
            <p>Best regards,<br>
            <strong>%3$s</strong></p>
        </div>
    </div>
</body>
</html>',
			$product_name,
			$redemption_link,
			get_bloginfo('name')
		);

		// Set headers for HTML email
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
		);
		
		$sent = wp_mail($gifter_email, $subject, $message, $headers);

		if ($sent) {
			wp_send_json_success(array(
				'message' => sprintf(__('Gift email resent successfully to %s', 'memberpress-gift-reporter'), $gifter_email)
			));
		} else {
			wp_send_json_error(__('Failed to send gift email. Please check your email configuration.', 'memberpress-gift-reporter'));
		}
	}
    
    /**
     * AJAX copy redemption link handler
     */
	public function ajax_copy_redemption_link() {
		// Verify nonce and permissions.
		if ( ! wp_verify_nonce( $_POST['nonce'], 'mpgr_copy_redemption_link' ) || ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Access denied', 'memberpress-gift-reporter' ) );
		}

		$gift_transaction_id = intval($_POST['gift_transaction_id']);
		
		if (!$gift_transaction_id) {
			wp_send_json_error('Invalid gift transaction ID');
		}

		// Get gift coupon ID
		global $wpdb;
		$gift_coupon_id = $wpdb->get_var($wpdb->prepare(
			"SELECT meta_value FROM {$wpdb->prefix}mepr_transaction_meta 
			WHERE transaction_id = %d AND meta_key = '_gift_coupon_id'",
			$gift_transaction_id
		));

		if (!$gift_coupon_id) {
			wp_send_json_error('Gift coupon not found');
		}

		// Get coupon code
		$coupon_code = get_post_field('post_title', $gift_coupon_id);
		
		if (!$coupon_code) {
			wp_send_json_error('Coupon code not found');
		}

		// Generate redemption link
		$redemption_link = home_url('/memberpress-checkout/?coupon=' . urlencode($coupon_code));

		wp_send_json_success(array(
			'redemption_link' => $redemption_link,
			'message' => __('Redemption link copied to clipboard', 'memberpress-gift-reporter')
		));
	}
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('mpgr/v1', '/report', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_report'),
            'permission_callback' => array($this, 'rest_permission_check'),
            'args' => array(
                'nonce' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));
        
        register_rest_route('mpgr/v1', '/export', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_export_csv'),
            'permission_callback' => array($this, 'rest_permission_check'),
            'args' => array(
                'nonce' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));
    }
    
    /**
     * REST API permission check
     */
    public function rest_permission_check($request) {
        // Check if user is logged in and has proper capabilities
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            return false;
        }
        
        // Verify nonce from header or parameter
        $nonce = $request->get_header('X-WP-Nonce') ?: $request->get_param('nonce');
        if (!$nonce || !wp_verify_nonce($nonce, 'mpgr_rest_nonce')) {
            return false;
        }
        
        // Add rate limiting check
        if ($this->is_rate_limited()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Rate limiting check
     */
    private function is_rate_limited() {
        $user_id = get_current_user_id();
        $rate_limit_key = 'mpgr_rate_limit_' . $user_id;
        $rate_limit_time = 60; // 1 minute
        $max_requests = 10; // Max 10 requests per minute
        
        $current_time = time();
        $requests = get_transient($rate_limit_key);
        
        if (!$requests) {
            $requests = array();
        }
        
        // Remove old requests
        $requests = array_filter($requests, function($time) use ($current_time, $rate_limit_time) {
            return ($current_time - $time) < $rate_limit_time;
        });
        
        // Check if limit exceeded
        if (count($requests) >= $max_requests) {
            return true;
        }
        
        // Add current request
        $requests[] = $current_time;
        set_transient($rate_limit_key, $requests, $rate_limit_time);
        
        return false;
    }
    
    /**
     * REST API get report
     */
    public function rest_get_report($request) {
        try {
            $data = $this->generate_report();
            $summary = $this->get_summary();
            
            return array(
                'success' => true,
                'data' => $data,
                'summary' => $summary
            );
        } catch (Exception $e) {
            return new WP_Error('report_error', 'Unable to generate report', array('status' => 500));
        }
    }
    
    /**
     * REST API export CSV
     */
    public function rest_export_csv($request) {
        try {
            $filters = $this->sanitize_export_filters($request);
            $this->generate_report(0, 0, $filters);
            $this->export_csv('memberpress_gift_report.csv', $filters);
        } catch (Exception $e) {
            return new WP_Error('export_error', 'Unable to export report', array('status' => 500));
        }
    }
    
    /**
     * Sanitize export filters from REST API request
     */
    private function sanitize_export_filters($request) {
        $filters = array();
        
        $filter_fields = array(
            'date_from' => 'sanitize_text_field',
            'date_to' => 'sanitize_text_field',
            'gift_status' => 'sanitize_text_field',
            'product' => 'intval',
            'gifter_email' => 'sanitize_email',
            'recipient_email' => 'sanitize_email',
            'redemption_from' => 'sanitize_text_field',
            'redemption_to' => 'sanitize_text_field',
        );
        
        foreach ($filter_fields as $field => $sanitize_function) {
            $value = $request->get_param($field);
            if (!empty($value)) {
                $filters[$field] = $sanitize_function($value);
            }
        }
        
        return $filters;
    }
    
    /**
     * Sanitize AJAX filter parameters
     */
    private function sanitize_ajax_filters($post_data) {
        $filters = array();
        
        $filter_fields = array(
            'date_from' => 'sanitize_text_field',
            'date_to' => 'sanitize_text_field',
            'gift_status' => 'sanitize_text_field',
            'product' => 'intval',
            'gifter_email' => 'sanitize_email',
            'recipient_email' => 'sanitize_email',
            'redemption_from' => 'sanitize_text_field',
            'redemption_to' => 'sanitize_text_field',
        );
        
        foreach ($filter_fields as $field => $sanitize_function) {
            if (!empty($post_data[$field])) {
                $filters[$field] = $sanitize_function($post_data[$field]);
            }
        }
        
        return $filters;
    }
    
    /**
     * Validate date format
     */
    private function is_valid_date($date_string) {
        if (empty($date_string)) {
            return false;
        }
        
        // Check if it's a valid date format (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_string)) {
            return false;
        }
        
        // Check if the date is actually valid
        $date = DateTime::createFromFormat('Y-m-d', $date_string);
        return $date && $date->format('Y-m-d') === $date_string;
    }
    

    
    /**
     * Generate the gift report
     */
    public function generate_report($limit = 1000, $offset = 0, $filters = array()) {
        global $wpdb;
        
        // Add pagination
        $limit_clause = '';
        if ($limit > 0) {
            $limit_clause = $wpdb->prepare(' LIMIT %d OFFSET %d', $limit, $offset);
        }
        
        // Build WHERE clause for filters
        $where_conditions = array();
        $where_conditions[] = "gifter_txn.status IN ('complete', 'confirmed', 'refunded')";
        $where_conditions[] = "(
            (gift_meta.meta_key = '_gift_status' AND gift_meta.meta_value IN ('unclaimed', 'claimed'))
            OR gift_meta.meta_key IN ('_gifter_id', '_gift_coupon_id')
        )";
        
        // Date From filter
        if (!empty($filters['date_from'])) {
            $date_from = sanitize_text_field($filters['date_from']);
            // Validate date format and convert to proper format
            if ($this->is_valid_date($date_from)) {
                $date_from_formatted = date('Y-m-d 00:00:00', strtotime($date_from));
                $where_conditions[] = $wpdb->prepare("gifter_txn.created_at >= %s", $date_from_formatted);
            }
        }
        
        // Date To filter
        if (!empty($filters['date_to'])) {
            $date_to = sanitize_text_field($filters['date_to']);
            // Validate date format and convert to proper format
            if ($this->is_valid_date($date_to)) {
                $date_to_formatted = date('Y-m-d 23:59:59', strtotime($date_to));
                $where_conditions[] = $wpdb->prepare("gifter_txn.created_at <= %s", $date_to_formatted);
            }
        }
        
        // Gift Status filter
        if (!empty($filters['gift_status'])) {
            $gift_status = sanitize_text_field($filters['gift_status']);
            $where_conditions[] = $wpdb->prepare("COALESCE(gift_status.meta_value, 'unclaimed') = %s", $gift_status);
        }
        
        // Product filter
        if (!empty($filters['product'])) {
            $product_id = intval($filters['product']);
            $where_conditions[] = $wpdb->prepare("gifter_txn.product_id = %d", $product_id);
        }
        
        // Gifter Email filter
        if (!empty($filters['gifter_email'])) {
            $gifter_email = sanitize_email($filters['gifter_email']);
            $where_conditions[] = $wpdb->prepare("gifter.user_email LIKE %s", '%' . $wpdb->esc_like($gifter_email) . '%');
        }
        
        // Recipient Email filter
        if (!empty($filters['recipient_email'])) {
            $recipient_email = sanitize_email($filters['recipient_email']);
            $where_conditions[] = $wpdb->prepare("recipient.user_email LIKE %s", '%' . $wpdb->esc_like($recipient_email) . '%');
        }
        

        
        // Redemption From filter
        if (!empty($filters['redemption_from'])) {
            $redemption_from = sanitize_text_field($filters['redemption_from']);
            // Validate date format and convert to proper format
            if ($this->is_valid_date($redemption_from)) {
                $redemption_from_formatted = date('Y-m-d 00:00:00', strtotime($redemption_from));
                $where_conditions[] = $wpdb->prepare("redemption_txn.created_at >= %s", $redemption_from_formatted);
            }
        }
        
        // Redemption To filter
        if (!empty($filters['redemption_to'])) {
            $redemption_to = sanitize_text_field($filters['redemption_to']);
            // Validate date format and convert to proper format
            if ($this->is_valid_date($redemption_to)) {
                $redemption_to_formatted = date('Y-m-d 23:59:59', strtotime($redemption_to));
                $where_conditions[] = $wpdb->prepare("redemption_txn.created_at <= %s", $redemption_to_formatted);
            }
        }
        
        // Use a more inclusive approach to find all gift transactions
        // This matches the MemberPress dev team's approach but with better structure
        $query = "
        SELECT 
            gifter_txn.id AS gift_transaction_id,
            gifter_txn.created_at AS gift_purchase_date,
            gifter_txn.trans_num AS gift_transaction_number,
            gifter_txn.amount AS gift_amount,
            gifter_txn.total AS gift_total,
            gifter_txn.status AS transaction_status,
            
            gifter.ID AS gifter_user_id,
            gifter.user_login AS gifter_username,
            COALESCE(gifter.user_email, 'Deleted User') AS gifter_email,
            COALESCE(gifter_fname.meta_value, '') AS gifter_first_name,
            COALESCE(gifter_lname.meta_value, '') AS gifter_last_name,
            
            gift_product.ID AS product_id,
            gift_product.post_title AS product_name,
            
            gift_coupon.ID AS coupon_id,
            gift_coupon.post_title AS coupon_code,
            
            COALESCE(gift_status.meta_value, 'unclaimed') AS gift_status,
            
            redemption_txn.id AS redemption_transaction_id,
            redemption_txn.created_at AS redemption_date,
            redemption_txn.trans_num AS redemption_transaction_number,
            
            recipient.ID AS recipient_user_id,
            recipient.user_login AS recipient_username,
            recipient.user_email AS recipient_email,
            recipient_fname.meta_value AS recipient_first_name,
            recipient_lname.meta_value AS recipient_last_name,
            
            CASE 
                WHEN gift_status.meta_value = 'claimed' THEN 'Claimed'
                WHEN gift_status.meta_value = 'unclaimed' THEN 'Unclaimed'
                WHEN gifter_txn.status = 'refunded' THEN 'Invalid (Refunded)'
                ELSE 'Unknown'
            END AS gift_status_display,
            
            CASE 
                WHEN gifter.ID IS NULL THEN 'Deleted'
                ELSE 'Active'
            END AS gifter_status

        FROM 
            {$wpdb->prefix}mepr_transactions AS gifter_txn
            
            -- Find transactions that have gift-related meta keys
            INNER JOIN {$wpdb->prefix}mepr_transaction_meta AS gift_meta 
                ON gifter_txn.id = gift_meta.transaction_id 
                AND gift_meta.meta_key IN ('_gift_status', '_gifter_id', '_gift_coupon_id')
            
            LEFT JOIN {$wpdb->users} AS gifter 
                ON gifter_txn.user_id = gifter.ID
            
            LEFT JOIN {$wpdb->usermeta} AS gifter_fname 
                ON gifter.ID = gifter_fname.user_id 
                AND gifter_fname.meta_key = 'first_name'
            
            LEFT JOIN {$wpdb->usermeta} AS gifter_lname 
                ON gifter.ID = gifter_lname.user_id 
                AND gifter_lname.meta_key = 'last_name'
            
            INNER JOIN {$wpdb->posts} AS gift_product 
                ON gifter_txn.product_id = gift_product.ID
            
            -- Get coupon information
            LEFT JOIN {$wpdb->prefix}mepr_transaction_meta AS coupon_meta 
                ON gifter_txn.id = coupon_meta.transaction_id 
                AND coupon_meta.meta_key = '_gift_coupon_id'
            LEFT JOIN {$wpdb->posts} AS gift_coupon 
                ON coupon_meta.meta_value = gift_coupon.ID
            
            -- Get gift status
            LEFT JOIN {$wpdb->prefix}mepr_transaction_meta AS gift_status 
                ON gifter_txn.id = gift_status.transaction_id 
                AND gift_status.meta_key = '_gift_status'
            
            -- Find redemption transaction for claimed gifts
            LEFT JOIN {$wpdb->prefix}mepr_transactions AS redemption_txn 
                ON gift_coupon.ID = redemption_txn.coupon_id 
                AND redemption_txn.status = 'complete'
            
            LEFT JOIN {$wpdb->users} AS recipient 
                ON redemption_txn.user_id = recipient.ID
            
            LEFT JOIN {$wpdb->usermeta} AS recipient_fname 
                ON recipient.ID = recipient_fname.user_id 
                AND recipient_fname.meta_key = 'first_name'
            
            LEFT JOIN {$wpdb->usermeta} AS recipient_lname 
                ON recipient.ID = recipient_lname.user_id 
                AND recipient_lname.meta_key = 'last_name'

        WHERE 
            " . implode(' AND ', $where_conditions) . "

        GROUP BY 
            gifter_txn.id, gifter_txn.product_id

        ORDER BY 
            gifter_txn.created_at DESC
            {$limit_clause}
        ";
        
        $this->report_data = $wpdb->get_results($query, ARRAY_A);
        
        return $this->report_data;
    }
    
    /**
     * Export report to CSV with streaming for large datasets
     */
    public function export_csv($filename = 'memberpress_gift_report.csv', $filters = array()) {
        global $wpdb;
        
        // Sanitize filename to prevent directory traversal and ensure it's a CSV
        $filename = sanitize_file_name($filename);
        if (empty($filename) || !preg_match('/\.csv$/i', $filename)) {
            $filename = 'memberpress_gift_report.csv';
        }
        
        // Ensure filename doesn't contain path traversal attempts
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            $filename = 'memberpress_gift_report.csv';
        }
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Get headers from first row
        $headers = array(
            __( 'Gift ID', 'memberpress-gift-reporter' ),
            __( 'Purchase Date', 'memberpress-gift-reporter' ), 
            __( 'Transaction Number', 'memberpress-gift-reporter' ),
            __( 'Amount', 'memberpress-gift-reporter' ),
            __( 'Total', 'memberpress-gift-reporter' ),
            __( 'Transaction Status', 'memberpress-gift-reporter' ),
            __( 'Gifter User ID', 'memberpress-gift-reporter' ),
            __( 'Gifter Username', 'memberpress-gift-reporter' ),
            __( 'Gifter Email', 'memberpress-gift-reporter' ),
            __( 'Gifter First Name', 'memberpress-gift-reporter' ),
            __( 'Gifter Last Name', 'memberpress-gift-reporter' ),
            __( 'Product ID', 'memberpress-gift-reporter' ),
            __( 'Product Name', 'memberpress-gift-reporter' ),
            __( 'Coupon ID', 'memberpress-gift-reporter' ),
            __( 'Coupon Code', 'memberpress-gift-reporter' ),
            __( 'Gift Status', 'memberpress-gift-reporter' ),
            __( 'Redemption Transaction ID', 'memberpress-gift-reporter' ),
            __( 'Redemption Date', 'memberpress-gift-reporter' ),
            __( 'Redemption Transaction Number', 'memberpress-gift-reporter' ),
            __( 'Recipient User ID', 'memberpress-gift-reporter' ),
            __( 'Recipient Username', 'memberpress-gift-reporter' ),
            __( 'Recipient Email', 'memberpress-gift-reporter' ),
            __( 'Recipient First Name', 'memberpress-gift-reporter' ),
            __( 'Recipient Last Name', 'memberpress-gift-reporter' ),
            __( 'Gift Status Display', 'memberpress-gift-reporter' ),
            __( 'Gifter Status', 'memberpress-gift-reporter' )
        );
        
        // Write headers
        fputcsv($output, $headers, ',', '"', '\\');
        
        // Stream data in chunks to avoid memory issues
        $chunk_size = 1000;
        $offset = 0;
        
        do {
            $data = $this->generate_report($chunk_size, $offset, $filters);
            
            if (!empty($data)) {
                foreach ($data as $row) {
                    // Translate status values for CSV export
                    $translated_row = $row;
                    if (isset($translated_row['gift_status_display'])) {
                        switch ($translated_row['gift_status_display']) {
                            case 'Claimed':
                                $translated_row['gift_status_display'] = __( 'Claimed', 'memberpress-gift-reporter' );
                                break;
                            case 'Unclaimed':
                                $translated_row['gift_status_display'] = __( 'Unclaimed', 'memberpress-gift-reporter' );
                                break;
                            case 'Invalid (Refunded)':
                                $translated_row['gift_status_display'] = __( 'Invalid (Refunded)', 'memberpress-gift-reporter' );
                                break;
                            case 'Unknown':
                                $translated_row['gift_status_display'] = __( 'Unknown', 'memberpress-gift-reporter' );
                                break;
                        }
                    }
                    if (isset($translated_row['gifter_status'])) {
                        switch ($translated_row['gifter_status']) {
                            case 'Deleted':
                                $translated_row['gifter_status'] = __( 'Deleted', 'memberpress-gift-reporter' );
                                break;
                            case 'Active':
                                $translated_row['gifter_status'] = __( 'Active', 'memberpress-gift-reporter' );
                                break;
                        }
                    }
                    fputcsv($output, $translated_row, ',', '"', '\\');
                }
            }
            
            $offset += $chunk_size;
        } while (count($data) === $chunk_size);
        
        fclose($output);
        exit;
    }
    
    /**
     * Get summary statistics
     */
    public function get_summary($filters = array()) {
        if (empty($this->report_data)) {
            $this->generate_report(0, 0, $filters);
        }
        
        $total_gifts = count($this->report_data);
        $claimed_gifts = 0;
        $unclaimed_gifts = 0;
        $total_revenue = 0;
        
        foreach ($this->report_data as $row) {
            if ($row['gift_status'] === 'claimed') {
                $claimed_gifts++;
            } else {
                $unclaimed_gifts++;
            }
            $total_revenue += floatval($row['gift_total']);
        }
        
        return array(
            'total_gifts' => $total_gifts,
            'claimed_gifts' => $claimed_gifts,
            'unclaimed_gifts' => $unclaimed_gifts,
            'claim_rate' => $total_gifts > 0 ? round(($claimed_gifts / $total_gifts) * 100, 2) : 0,
            'total_revenue' => $total_revenue
        );
    }
    
    /**
     * Get all available products for filtering
     */
    private function get_available_products() {
        global $wpdb;
        
        $query = "
        SELECT DISTINCT 
            p.ID,
            p.post_title
        FROM 
            {$wpdb->posts} AS p
            INNER JOIN {$wpdb->prefix}mepr_transactions AS t ON p.ID = t.product_id
            INNER JOIN {$wpdb->prefix}mepr_transaction_meta AS tm ON t.id = tm.transaction_id
        WHERE 
            p.post_type = 'memberpressproduct'
            AND p.post_status = 'publish'
            AND tm.meta_key IN ('_gift_status', '_gifter_id', '_gift_coupon_id')
        ORDER BY 
            p.post_title ASC
        ";
        
        return $wpdb->get_results($query, ARRAY_A);
    }
    
    /**
     * Display the report
     */
    public function display_report($filters = array()) {
        if (empty($this->report_data)) {
            $this->generate_report(0, 0, $filters);
        }
        
        $summary = $this->get_summary($filters);
        
        // Enqueue styles only on admin pages
        if (is_admin()) {
            wp_enqueue_style('mpgr-styles', MPGR_PLUGIN_URL . 'assets/css/style.min.css', array(), MPGR_VERSION);
        }
        
        		echo '<div class="mpgr-gift-report">';
		echo '<h2>🎁 ' . __( 'MemberPress Gift Report', 'memberpress-gift-reporter' ) . '</h2>';
		
		// Filter form
		echo '<div class="mpgr-filters">';
		echo '<h3>🔍 ' . __( 'Filters', 'memberpress-gift-reporter' ) . '</h3>';
        
        // Show active filters
        $active_filters = array();
        if (!empty($filters['date_from'])) {
            $active_filters[] = __( 'Date From:', 'memberpress-gift-reporter' ) . ' ' . esc_html($filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $active_filters[] = __( 'Date To:', 'memberpress-gift-reporter' ) . ' ' . esc_html($filters['date_to']);
        }
        if (!empty($filters['gift_status'])) {
            $status_display = ucfirst($filters['gift_status']);
            $active_filters[] = __( 'Gift Status:', 'memberpress-gift-reporter' ) . ' ' . esc_html($status_display);
        }
        if (!empty($filters['product'])) {
            $products = $this->get_available_products();
            $product_name = __( 'Unknown Product', 'memberpress-gift-reporter' );
            foreach ($products as $product) {
                if ($product['ID'] == $filters['product']) {
                    $product_name = $product['post_title'];
                    break;
                }
            }
            $active_filters[] = __( 'Membership:', 'memberpress-gift-reporter' ) . ' ' . esc_html($product_name);
        }
        if (!empty($filters['gifter_email'])) {
            $active_filters[] = __( 'Gifter Email:', 'memberpress-gift-reporter' ) . ' ' . esc_html($filters['gifter_email']);
        }
        if (!empty($filters['recipient_email'])) {
            $active_filters[] = __( 'Recipient Email:', 'memberpress-gift-reporter' ) . ' ' . esc_html($filters['recipient_email']);
        }

        if (!empty($filters['redemption_from'])) {
            $active_filters[] = __( 'Redemption From:', 'memberpress-gift-reporter' ) . ' ' . esc_html($filters['redemption_from']);
        }
        if (!empty($filters['redemption_to'])) {
            $active_filters[] = __( 'Redemption To:', 'memberpress-gift-reporter' ) . ' ' . esc_html($filters['redemption_to']);
        }
        
        		if (!empty($active_filters)) {
			echo '<div class="mpgr-active-filters">';
			echo '<strong>' . __( 'Active Filters:', 'memberpress-gift-reporter' ) . '</strong> ' . implode(', ', $active_filters);
			echo '</div>';
		}
        

        
        echo '<form method="GET" action="">';
        echo '<input type="hidden" name="page" value="memberpress-gift-report">';
        echo '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce('mpgr_filter_nonce') . '">';
        
        echo '<div class="mpgr-filter-grid">';
        
        		// Date From filter
		echo '<div class="mpgr-filter-group">';
		echo '<label for="date_from">' . __( 'Date From', 'memberpress-gift-reporter' ) . '</label>';
		echo '<input type="date" id="date_from" name="date_from" value="' . esc_attr($filters['date_from'] ?? '') . '">';
		echo '</div>';
		
		// Date To filter
		echo '<div class="mpgr-filter-group">';
		echo '<label for="date_to">' . __( 'Date To', 'memberpress-gift-reporter' ) . '</label>';
		echo '<input type="date" id="date_to" name="date_to" value="' . esc_attr($filters['date_to'] ?? '') . '">';
		echo '</div>';
        
        		// Gift Status filter
		echo '<div class="mpgr-filter-group">';
		echo '<label for="gift_status">' . __( 'Gift Status', 'memberpress-gift-reporter' ) . '</label>';
		echo '<select id="gift_status" name="gift_status">';
		echo '<option value="">' . __( 'All Statuses', 'memberpress-gift-reporter' ) . '</option>';
		echo '<option value="claimed"' . selected($filters['gift_status'] ?? '', 'claimed', false) . '>' . __( 'Claimed', 'memberpress-gift-reporter' ) . '</option>';
		echo '<option value="unclaimed"' . selected($filters['gift_status'] ?? '', 'unclaimed', false) . '>' . __( 'Unclaimed', 'memberpress-gift-reporter' ) . '</option>';
		echo '</select>';
		echo '</div>';
        
        		// Product/Membership filter
		echo '<div class="mpgr-filter-group">';
		echo '<label for="product">' . __( 'Membership', 'memberpress-gift-reporter' ) . '</label>';
		echo '<select id="product" name="product">';
		echo '<option value="">' . __( 'All Memberships', 'memberpress-gift-reporter' ) . '</option>';
        
        $products = $this->get_available_products();
        foreach ($products as $product) {
            $selected = selected($filters['product'] ?? '', $product['ID'], false);
            echo '<option value="' . esc_attr($product['ID']) . '"' . $selected . '>' . esc_html($product['post_title']) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        		// Gifter Email filter
		echo '<div class="mpgr-filter-group">';
		echo '<label for="gifter_email">' . __( 'Gifter Email', 'memberpress-gift-reporter' ) . '</label>';
		echo '<input type="email" id="gifter_email" name="gifter_email" value="' . esc_attr($filters['gifter_email'] ?? '') . '" placeholder="' . __( 'Enter gifter email', 'memberpress-gift-reporter' ) . '">';
		echo '</div>';
		
		// Recipient Email filter
		echo '<div class="mpgr-filter-group">';
		echo '<label for="recipient_email">' . __( 'Recipient Email', 'memberpress-gift-reporter' ) . '</label>';
		echo '<input type="email" id="recipient_email" name="recipient_email" value="' . esc_attr($filters['recipient_email'] ?? '') . '" placeholder="' . __( 'Enter recipient email', 'memberpress-gift-reporter' ) . '">';
		echo '</div>';
        

        
        		// Redemption From filter
		echo '<div class="mpgr-filter-group">';
		echo '<label for="redemption_from">' . __( 'Redemption From', 'memberpress-gift-reporter' ) . '</label>';
		echo '<input type="date" id="redemption_from" name="redemption_from" value="' . esc_attr($filters['redemption_from'] ?? '') . '">';
		echo '</div>';
		
		// Redemption To filter
		echo '<div class="mpgr-filter-group">';
		echo '<label for="redemption_to">' . __( 'Redemption To', 'memberpress-gift-reporter' ) . '</label>';
		echo '<input type="date" id="redemption_to" name="redemption_to" value="' . esc_attr($filters['redemption_to'] ?? '') . '">';
		echo '</div>';
        
        echo '</div>';
        
        		echo '<div class="mpgr-filter-actions">';
		echo '<button type="submit" class="button button-primary">' . __( 'Apply Filters', 'memberpress-gift-reporter' ) . '</button>';
		echo '<a href="' . admin_url('admin.php?page=memberpress-gift-report') . '" class="button">' . __( 'Clear Filters', 'memberpress-gift-reporter' ) . '</a>';
		echo '</div>';
        echo '</form>';
        echo '</div>';
        
        echo '<div class="mpgr-summary">';
        
        // Determine if filters are applied
        $has_filters = !empty($filters['date_from']) || 
                      !empty($filters['date_to']) || 
                      !empty($filters['gift_status']) || 
                      !empty($filters['product']) || 
                      !empty($filters['gifter_email']) || 
                      !empty($filters['recipient_email']) || 
                      !empty($filters['redemption_from']) || 
                      !empty($filters['redemption_to']);
        
        		if ($has_filters) {
			echo '<h3>📊 ' . __( 'Summary (Filtered)', 'memberpress-gift-reporter' ) . '</h3>';
		} else {
			echo '<h3>📊 ' . __( 'All-time Summary', 'memberpress-gift-reporter' ) . '</h3>';
		}
		echo '<p><strong>' . __( 'Total Gifts:', 'memberpress-gift-reporter' ) . '</strong> ' . $summary['total_gifts'] . '</p>';
		echo '<p><strong>' . __( 'Claimed:', 'memberpress-gift-reporter' ) . '</strong> ' . $summary['claimed_gifts'] . '</p>';
		echo '<p><strong>' . __( 'Unclaimed:', 'memberpress-gift-reporter' ) . '</strong> ' . $summary['unclaimed_gifts'] . '</p>';
		echo '<p><strong>' . __( 'Claim Rate:', 'memberpress-gift-reporter' ) . '</strong> ' . $summary['claim_rate'] . '%</p>';
		echo '<p><strong>' . __( 'Total Revenue:', 'memberpress-gift-reporter' ) . '</strong> $' . number_format($summary['total_revenue'], 2) . '</p>';
        echo '</div>';
        
        		// Export button
		echo '<a href="#" class="mpgr-export-btn" onclick="mpgrExportCSV()">📥 ' . __( 'Download CSV Report', 'memberpress-gift-reporter' ) . '</a>';
        
        if (!empty($this->report_data)) {
            			echo '<table class="mpgr-table">';
			echo '<thead>';
			echo '<tr>';
			echo '<th>' . __( 'Gift ID', 'memberpress-gift-reporter' ) . '</th>';
			echo '<th>' . __( 'Purchase Date', 'memberpress-gift-reporter' ) . '</th>';
			echo '<th>' . __( 'Gifter Email', 'memberpress-gift-reporter' ) . '</th>';
			echo '<th>' . __( 'Product', 'memberpress-gift-reporter' ) . '</th>';
			echo '<th>' . __( 'Coupon Code', 'memberpress-gift-reporter' ) . '</th>';
			echo '<th>' . __( 'Status', 'memberpress-gift-reporter' ) . '</th>';
			echo '<th>' . __( 'Recipient Email', 'memberpress-gift-reporter' ) . '</th>';
			echo '<th>' . __( 'Redemption Date', 'memberpress-gift-reporter' ) . '</th>';
			echo '<th>' . __( 'Amount', 'memberpress-gift-reporter' ) . '</th>';
			echo '</tr>';
			echo '</thead>';
            echo '<tbody>';
            
            foreach ($this->report_data as $row) {
                $status_class = '';
                switch ($row['gift_status']) {
                    case 'claimed':
                        $status_class = 'mpgr-claimed';
                        break;
                    case 'unclaimed':
                        $status_class = 'mpgr-unclaimed';
                        break;
                    default:
                        $status_class = 'mpgr-refunded';
                }
                
                echo '<tr>';
                echo '<td>' . esc_html($row['gift_transaction_id']) . '</td>';
                echo '<td>' . esc_html($row['gift_purchase_date']) . '</td>';
                if ($row['gifter_email'] === 'Deleted User') {
                    echo '<td><span class="mpgr-deleted-user">' . __( 'Deleted User', 'memberpress-gift-reporter' ) . '</span></td>';
                } else {
                    echo '<td>' . esc_html($row['gifter_email']) . '</td>';
                }
                echo '<td>' . esc_html($row['product_name']) . '</td>';
                echo '<td>' . esc_html($row['coupon_code']) . '</td>';
                // Translate status display
                $status_display = $row['gift_status_display'];
                switch ($status_display) {
                    case 'Claimed':
                        $status_display = __( 'Claimed', 'memberpress-gift-reporter' );
                        break;
                    case 'Unclaimed':
                        $status_display = __( 'Unclaimed', 'memberpress-gift-reporter' );
                        break;
                    case 'Invalid (Refunded)':
                        $status_display = __( 'Invalid (Refunded)', 'memberpress-gift-reporter' );
                        break;
                    case 'Unknown':
                        $status_display = __( 'Unknown', 'memberpress-gift-reporter' );
                        break;
                }
                echo '<td class="' . esc_attr($status_class) . '">' . esc_html($status_display) . '</td>';
                echo '<td>' . esc_html($row['recipient_email'] ?: __( 'N/A', 'memberpress-gift-reporter' )) . '</td>';
                echo '<td>' . esc_html($row['redemption_date'] ?: __( 'N/A', 'memberpress-gift-reporter' )) . '</td>';
                echo '<td>$' . number_format($row['gift_total'], 2) . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
        } else {
            // Check if there are any gift transactions at all (without filters)
            $all_gifts = $this->generate_report(0, 0, array());
            
            if (!empty($all_gifts)) {
                // There are gift transactions, but filters are too restrictive
                echo '<div class="mpgr-no-data mpgr-filtered-no-data">';
                echo '<h3>' . __( 'No Results Match Your Filters', 'memberpress-gift-reporter' ) . '</h3>';
                echo '<p>' . __( 'We found gift transactions in your database, but none match your current filter criteria. Try:', 'memberpress-gift-reporter' ) . '</p>';
                echo '<ul>';
                echo '<li>' . __( 'Broadening your date range', 'memberpress-gift-reporter' ) . '</li>';
                echo '<li>' . __( 'Selecting "All Statuses" instead of a specific status', 'memberpress-gift-reporter' ) . '</li>';
                echo '<li>' . __( 'Choosing "All Memberships" instead of a specific product', 'memberpress-gift-reporter' ) . '</li>';
                echo '<li>' . __( 'Clearing email filters if they\'re too specific', 'memberpress-gift-reporter' ) . '</li>';
                echo '<li>' . __( 'Adjusting redemption date filters', 'memberpress-gift-reporter' ) . '</li>';
                echo '</ul>';
                echo '<div class="mpgr-help-links">';
                echo '<a href="#" onclick="clearAllFilters()" class="mpgr-clear-filters-btn">' . __( 'Clear All Filters', 'memberpress-gift-reporter' ) . '</a>';
                echo '<a href="' . admin_url('admin.php?page=memberpress-trans') . '">' . __( 'View All Transactions', 'memberpress-gift-reporter' ) . '</a>';
                echo '</div>';
                echo '</div>';
            			} else {
				// No gift transactions exist at all
				echo '<div class="mpgr-no-data">';
				echo '<h3>' . __( 'No Gift Transactions Found', 'memberpress-gift-reporter' ) . '</h3>';
				echo '<p>' . __( 'We couldn\'t find any gift transactions in your database. This could be because:', 'memberpress-gift-reporter' ) . '</p>';
				echo '<ul>';
				echo '<li>' . __( 'MemberPress Gifting add-on is not activated', 'memberpress-gift-reporter' ) . '</li>';
				echo '<li>' . __( 'No gift purchases have been completed yet', 'memberpress-gift-reporter' ) . '</li>';
				echo '<li>' . __( 'Database permissions need to be configured', 'memberpress-gift-reporter' ) . '</li>';
				echo '<li>' . __( 'Gift transactions are in a different status', 'memberpress-gift-reporter' ) . '</li>';
				echo '</ul>';
				echo '<div class="mpgr-help-links">';
				echo '<a href="https://memberpress.com/gifting/" target="_blank">' . __( 'Learn About Gifting', 'memberpress-gift-reporter' ) . '</a>';
				echo '<a href="' . admin_url('admin.php?page=memberpress-addons') . '">' . __( 'Check Add-ons', 'memberpress-gift-reporter' ) . '</a>';
				echo '<a href="' . admin_url('admin.php?page=memberpress-trans') . '">' . __( 'View All Transactions', 'memberpress-gift-reporter' ) . '</a>';
				echo '</div>';
				echo '</div>';
			}
        }
        
        echo '</div>';
        
        // Add JavaScript for export only on admin pages
        if (is_admin()) {
            wp_enqueue_script('mpgr-script', MPGR_PLUGIN_URL . 'assets/js/script.min.js', array('jquery'), MPGR_VERSION, true);
            wp_localize_script('mpgr-script', 'mpgr_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mpgr_export_csv'),
                'resend_email_nonce' => wp_create_nonce('mpgr_resend_gift_email'),
                'copy_link_nonce' => wp_create_nonce('mpgr_copy_redemption_link')
            ));
        }
    }
}

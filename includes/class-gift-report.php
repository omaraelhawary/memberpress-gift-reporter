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

		// Get filter parameters
		$filters = array();
		if (!empty($_POST['date_from'])) {
			$filters['date_from'] = sanitize_text_field($_POST['date_from']);
		}
		if (!empty($_POST['date_to'])) {
			$filters['date_to'] = sanitize_text_field($_POST['date_to']);
		}
		if (!empty($_POST['gift_status'])) {
			$filters['gift_status'] = sanitize_text_field($_POST['gift_status']);
		}
		if (!empty($_POST['product'])) {
			$filters['product'] = intval($_POST['product']);
		}
		if (!empty($_POST['gifter_email'])) {
			$filters['gifter_email'] = sanitize_email($_POST['gifter_email']);
		}
		if (!empty($_POST['recipient_email'])) {
			$filters['recipient_email'] = sanitize_email($_POST['recipient_email']);
		}

		if (!empty($_POST['redemption_from'])) {
			$filters['redemption_from'] = sanitize_text_field($_POST['redemption_from']);
		}
		if (!empty($_POST['redemption_to'])) {
			$filters['redemption_to'] = sanitize_text_field($_POST['redemption_to']);
		}

		$this->generate_report(0, 0, $filters);
		$this->export_csv('memberpress_gift_report.csv', $filters);
	}
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('mpgr/v1', '/report', array(
            'methods' => 'GET',
            'callback' => array($this, 'rest_get_report'),
            'permission_callback' => array($this, 'rest_permission_check'),
        ));
        
        register_rest_route('mpgr/v1', '/export', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_export_csv'),
            'permission_callback' => array($this, 'rest_permission_check'),
        ));
    }
    
    /**
     * REST API permission check
     */
    public function rest_permission_check($request) {
        return current_user_can('manage_options') && wp_verify_nonce($request->get_param('nonce'), 'mpgr_rest_nonce');
    }
    
    /**
     * REST API get report
     */
    public function rest_get_report($request) {
        // Verify nonce
        if (!wp_verify_nonce($request->get_param('nonce'), 'mpgr_rest_nonce')) {
            return new WP_Error('invalid_nonce', 'Invalid nonce', array('status' => 403));
        }
        
        $data = $this->generate_report();
        $summary = $this->get_summary();
        
        return array(
            'success' => true,
            'data' => $data,
            'summary' => $summary
        );
    }
    
    /**
     * REST API export CSV
     */
    public function rest_export_csv($request) {
        // Verify nonce
        if (!wp_verify_nonce($request->get_param('nonce'), 'mpgr_rest_nonce')) {
            return new WP_Error('invalid_nonce', 'Invalid nonce', array('status' => 403));
        }
        
        $this->generate_report();
        $this->export_csv();
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
            // Convert date to proper format and add time to make it start of day
            $date_from_formatted = date('Y-m-d 00:00:00', strtotime($date_from));
            $where_conditions[] = $wpdb->prepare("gifter_txn.created_at >= %s", $date_from_formatted);
        }
        
        // Date To filter
        if (!empty($filters['date_to'])) {
            $date_to = sanitize_text_field($filters['date_to']);
            // Convert date to proper format and add time to make it end of day
            $date_to_formatted = date('Y-m-d 23:59:59', strtotime($date_to));
            $where_conditions[] = $wpdb->prepare("gifter_txn.created_at <= %s", $date_to_formatted);
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
            // Convert date to proper format and add time to make it start of day
            $redemption_from_formatted = date('Y-m-d 00:00:00', strtotime($redemption_from));
            $where_conditions[] = $wpdb->prepare("redemption_txn.created_at >= %s", $redemption_from_formatted);
        }
        
        // Redemption To filter
        if (!empty($filters['redemption_to'])) {
            $redemption_to = sanitize_text_field($filters['redemption_to']);
            // Convert date to proper format and add time to make it end of day
            $redemption_to_formatted = date('Y-m-d 23:59:59', strtotime($redemption_to));
            $where_conditions[] = $wpdb->prepare("redemption_txn.created_at <= %s", $redemption_to_formatted);
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
        
        // Sanitize filename to prevent directory traversal
        $filename = sanitize_file_name($filename);
        if (empty($filename) || strpos($filename, '.csv') === false) {
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
            'Gift ID',
            'Purchase Date', 
            'Transaction Number',
            'Amount',
            'Total',
            'Transaction Status',
            'Gifter User ID',
            'Gifter Username',
            'Gifter Email',
            'Gifter First Name',
            'Gifter Last Name',
            'Product ID',
            'Product Name',
            'Coupon ID',
            'Coupon Code',
            'Gift Status',
            'Redemption Transaction ID',
            'Redemption Date',
            'Redemption Transaction Number',
            'Recipient User ID',
            'Recipient Username',
            'Recipient Email',
            'Recipient First Name',
            'Recipient Last Name',
            'Gift Status Display',
            'Gifter Status'
        );
        
        // Write headers
        fputcsv($output, $headers);
        
        // Stream data in chunks to avoid memory issues
        $chunk_size = 1000;
        $offset = 0;
        
        do {
            $data = $this->generate_report($chunk_size, $offset, $filters);
            
            if (!empty($data)) {
                foreach ($data as $row) {
                    fputcsv($output, $row);
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
        echo '<h2>🎁 MemberPress Gift Report</h2>';
        
        // Filter form
        echo '<div class="mpgr-filters">';
        echo '<h3>🔍 Filters</h3>';
        
        // Show active filters
        $active_filters = array();
        if (!empty($filters['date_from'])) {
            $active_filters[] = 'Date From: ' . esc_html($filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $active_filters[] = 'Date To: ' . esc_html($filters['date_to']);
        }
        if (!empty($filters['gift_status'])) {
            $status_display = ucfirst($filters['gift_status']);
            $active_filters[] = 'Gift Status: ' . esc_html($status_display);
        }
        if (!empty($filters['product'])) {
            $products = $this->get_available_products();
            $product_name = 'Unknown Product';
            foreach ($products as $product) {
                if ($product['ID'] == $filters['product']) {
                    $product_name = $product['post_title'];
                    break;
                }
            }
            $active_filters[] = 'Membership: ' . esc_html($product_name);
        }
        if (!empty($filters['gifter_email'])) {
            $active_filters[] = 'Gifter Email: ' . esc_html($filters['gifter_email']);
        }
        if (!empty($filters['recipient_email'])) {
            $active_filters[] = 'Recipient Email: ' . esc_html($filters['recipient_email']);
        }

        if (!empty($filters['redemption_from'])) {
            $active_filters[] = 'Redemption From: ' . esc_html($filters['redemption_from']);
        }
        if (!empty($filters['redemption_to'])) {
            $active_filters[] = 'Redemption To: ' . esc_html($filters['redemption_to']);
        }
        
        if (!empty($active_filters)) {
            echo '<div class="mpgr-active-filters">';
            echo '<strong>Active Filters:</strong> ' . implode(', ', $active_filters);
            echo '</div>';
        }
        

        
        echo '<form method="GET" action="">';
        echo '<input type="hidden" name="page" value="memberpress-gift-report">';
        
        echo '<div class="mpgr-filter-grid">';
        
        // Date From filter
        echo '<div class="mpgr-filter-group">';
        echo '<label for="date_from">Date From</label>';
        echo '<input type="date" id="date_from" name="date_from" value="' . esc_attr($filters['date_from'] ?? '') . '">';
        echo '</div>';
        
        // Date To filter
        echo '<div class="mpgr-filter-group">';
        echo '<label for="date_to">Date To</label>';
        echo '<input type="date" id="date_to" name="date_to" value="' . esc_attr($filters['date_to'] ?? '') . '">';
        echo '</div>';
        
        // Gift Status filter
        echo '<div class="mpgr-filter-group">';
        echo '<label for="gift_status">Gift Status</label>';
        echo '<select id="gift_status" name="gift_status">';
        echo '<option value="">All Statuses</option>';
        echo '<option value="claimed"' . selected($filters['gift_status'] ?? '', 'claimed', false) . '>Claimed</option>';
        echo '<option value="unclaimed"' . selected($filters['gift_status'] ?? '', 'unclaimed', false) . '>Unclaimed</option>';
        echo '</select>';
        echo '</div>';
        
        // Product/Membership filter
        echo '<div class="mpgr-filter-group">';
        echo '<label for="product">Membership</label>';
        echo '<select id="product" name="product">';
        echo '<option value="">All Memberships</option>';
        
        $products = $this->get_available_products();
        foreach ($products as $product) {
            $selected = selected($filters['product'] ?? '', $product['ID'], false);
            echo '<option value="' . esc_attr($product['ID']) . '"' . $selected . '>' . esc_html($product['post_title']) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        // Gifter Email filter
        echo '<div class="mpgr-filter-group">';
        echo '<label for="gifter_email">Gifter Email</label>';
        echo '<input type="email" id="gifter_email" name="gifter_email" value="' . esc_attr($filters['gifter_email'] ?? '') . '" placeholder="Enter gifter email">';
        echo '</div>';
        
        // Recipient Email filter
        echo '<div class="mpgr-filter-group">';
        echo '<label for="recipient_email">Recipient Email</label>';
        echo '<input type="email" id="recipient_email" name="recipient_email" value="' . esc_attr($filters['recipient_email'] ?? '') . '" placeholder="Enter recipient email">';
        echo '</div>';
        

        
        // Redemption From filter
        echo '<div class="mpgr-filter-group">';
        echo '<label for="redemption_from">Redemption From</label>';
        echo '<input type="date" id="redemption_from" name="redemption_from" value="' . esc_attr($filters['redemption_from'] ?? '') . '">';
        echo '</div>';
        
        // Redemption To filter
        echo '<div class="mpgr-filter-group">';
        echo '<label for="redemption_to">Redemption To</label>';
        echo '<input type="date" id="redemption_to" name="redemption_to" value="' . esc_attr($filters['redemption_to'] ?? '') . '">';
        echo '</div>';
        
        echo '</div>';
        
        echo '<div class="mpgr-filter-actions">';
        echo '<button type="submit" class="button button-primary">Apply Filters</button>';
        echo '<a href="' . admin_url('admin.php?page=memberpress-gift-report') . '" class="button">Clear Filters</a>';
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
            echo '<h3>📊 Summary (Filtered)</h3>';
        } else {
            echo '<h3>📊 All-time Summary</h3>';
        }
        echo '<p><strong>Total Gifts:</strong> ' . $summary['total_gifts'] . '</p>';
        echo '<p><strong>Claimed:</strong> ' . $summary['claimed_gifts'] . '</p>';
        echo '<p><strong>Unclaimed:</strong> ' . $summary['unclaimed_gifts'] . '</p>';
        echo '<p><strong>Claim Rate:</strong> ' . $summary['claim_rate'] . '%</p>';
        echo '<p><strong>Total Revenue:</strong> $' . number_format($summary['total_revenue'], 2) . '</p>';
        echo '</div>';
        
        // Export button
        echo '<a href="#" class="mpgr-export-btn" onclick="mpgrExportCSV()">📥 Download CSV Report</a>';
        
        if (!empty($this->report_data)) {
            echo '<table class="mpgr-table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Gift ID</th>';
            echo '<th>Purchase Date</th>';
            echo '<th>Gifter Email</th>';
            echo '<th>Product</th>';
            echo '<th>Coupon Code</th>';
            echo '<th>Status</th>';
            echo '<th>Recipient Email</th>';
            echo '<th>Redemption Date</th>';
            echo '<th>Amount</th>';
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
                    echo '<td><span class="mpgr-deleted-user">' . esc_html($row['gifter_email']) . '</span></td>';
                } else {
                    echo '<td>' . esc_html($row['gifter_email']) . '</td>';
                }
                echo '<td>' . esc_html($row['product_name']) . '</td>';
                echo '<td>' . esc_html($row['coupon_code']) . '</td>';
                echo '<td class="' . esc_attr($status_class) . '">' . esc_html($row['gift_status_display']) . '</td>';
                echo '<td>' . esc_html($row['recipient_email'] ?: 'N/A') . '</td>';
                echo '<td>' . esc_html($row['redemption_date'] ?: 'N/A') . '</td>';
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
                echo '<h3>No Results Match Your Filters</h3>';
                echo '<p>We found gift transactions in your database, but none match your current filter criteria. Try:</p>';
                echo '<ul>';
                echo '<li>Broadening your date range</li>';
                echo '<li>Selecting "All Statuses" instead of a specific status</li>';
                echo '<li>Choosing "All Memberships" instead of a specific product</li>';
                echo '<li>Clearing email filters if they\'re too specific</li>';
                echo '<li>Adjusting redemption date filters</li>';
                echo '</ul>';
                echo '<div class="mpgr-help-links">';
                echo '<a href="#" onclick="clearAllFilters()" class="mpgr-clear-filters-btn">Clear All Filters</a>';
                echo '<a href="' . admin_url('admin.php?page=memberpress-trans') . '">View All Transactions</a>';
                echo '</div>';
                echo '</div>';
            } else {
                // No gift transactions exist at all
                echo '<div class="mpgr-no-data">';
                echo '<h3>No Gift Transactions Found</h3>';
                echo '<p>We couldn\'t find any gift transactions in your database. This could be because:</p>';
                echo '<ul>';
                echo '<li>MemberPress Gifting add-on is not activated</li>';
                echo '<li>No gift purchases have been completed yet</li>';
                echo '<li>Database permissions need to be configured</li>';
                echo '<li>Gift transactions are in a different status</li>';
                echo '</ul>';
                echo '<div class="mpgr-help-links">';
                echo '<a href="https://memberpress.com/gifting/" target="_blank">Learn About Gifting</a>';
                echo '<a href="' . admin_url('admin.php?page=memberpress-addons') . '">Check Add-ons</a>';
                echo '<a href="' . admin_url('admin.php?page=memberpress-trans') . '">View All Transactions</a>';
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
                'nonce' => wp_create_nonce('mpgr_export_csv')
            ));
        }
    }
}

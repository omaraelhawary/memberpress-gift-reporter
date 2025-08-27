/**
 * MemberPress Gift Reporter - Frontend JavaScript
 * 
 * @package MemberPressGiftReporter
 */

(function($) {
    'use strict';

    /**
     * Export CSV function
     */
    window.mpgrExportCSV = function() {
        // Validate inputs before sending
        if (!validateExportInputs()) {
            return;
        }

        // Show loading state
        var $btn = $('.mpgr-export-btn');
        var originalText = $btn.text();
        $btn.text('📥 Exporting...').prop('disabled', true);

        // Prepare form data
        var formData = new FormData();
        formData.append('action', 'mpgr_export_csv');
        formData.append('nonce', mpgr_ajax.nonce);

        // Add filter parameters
        var dateFrom = $('#date_from').val();
        if (dateFrom) {
            formData.append('date_from', dateFrom);
        }
        
        var dateTo = $('#date_to').val();
        if (dateTo) {
            formData.append('date_to', dateTo);
        }
        
        var giftStatus = $('#gift_status').val();
        if (giftStatus) {
            formData.append('gift_status', giftStatus);
        }
        
        var product = $('#product').val();
        if (product) {
            formData.append('product', product);
        }
        
        var gifterEmail = $('#gifter_email').val();
        if (gifterEmail) {
            formData.append('gifter_email', gifterEmail);
        }
        
        var recipientEmail = $('#recipient_email').val();
        if (recipientEmail) {
            formData.append('recipient_email', recipientEmail);
        }
        

        
        var redemptionFrom = $('#redemption_from').val();
        if (redemptionFrom) {
            formData.append('redemption_from', redemptionFrom);
        }
        
        var redemptionTo = $('#redemption_to').val();
        if (redemptionTo) {
            formData.append('redemption_to', redemptionTo);
        }

        // Make AJAX request
        $.ajax({
            url: mpgr_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhrFields: {
                responseType: 'blob'
            },
            success: function(response, status, xhr) {
                // Create download link
                var blob = new Blob([response], { type: 'text/csv' });
                var url = window.URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = 'memberpress_gift_report.csv';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

                // Reset button
                $btn.text(originalText).prop('disabled', false);

                // Show success message
                showMessage('CSV exported successfully!', 'success');
            },
            error: function(xhr, status, error) {
                // Reset button
                $btn.text(originalText).prop('disabled', false);

                // Show error message
                showMessage('Error exporting CSV. Please try again.', 'error');
                console.error('Export error:', error);
            }
        });
    };

    /**
     * Validate export inputs
     */
    function validateExportInputs() {
        var dateFrom = $('#date_from').val();
        var dateTo = $('#date_to').val();
        var gifterEmail = $('#gifter_email').val();
        var recipientEmail = $('#recipient_email').val();
        var redemptionFrom = $('#redemption_from').val();
        var redemptionTo = $('#redemption_to').val();

        // Validate date format (YYYY-MM-DD)
        var dateRegex = /^\d{4}-\d{2}-\d{2}$/;
        
        if (dateFrom && !dateRegex.test(dateFrom)) {
            showMessage('Invalid date format for Date From. Use YYYY-MM-DD format.', 'error');
            return false;
        }
        
        if (dateTo && !dateRegex.test(dateTo)) {
            showMessage('Invalid date format for Date To. Use YYYY-MM-DD format.', 'error');
            return false;
        }
        
        if (redemptionFrom && !dateRegex.test(redemptionFrom)) {
            showMessage('Invalid date format for Redemption From. Use YYYY-MM-DD format.', 'error');
            return false;
        }
        
        if (redemptionTo && !dateRegex.test(redemptionTo)) {
            showMessage('Invalid date format for Redemption To. Use YYYY-MM-DD format.', 'error');
            return false;
        }

        // Validate email format
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (gifterEmail && !emailRegex.test(gifterEmail)) {
            showMessage('Invalid email format for Gifter Email.', 'error');
            return false;
        }
        
        if (recipientEmail && !emailRegex.test(recipientEmail)) {
            showMessage('Invalid email format for Recipient Email.', 'error');
            return false;
        }

        return true;
    }

    /**
     * Show message function
     */
    function showMessage(message, type) {
        var $message = $('<div class="mpgr-message mpgr-' + type + '">' + message + '</div>');
        $('.mpgr-gift-report').prepend($message);
        
        // Auto remove after 5 seconds
        setTimeout(function() {
            $message.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }

    /**
     * Clear all filters function
     */
    window.clearAllFilters = function() {
        // Clear all filter inputs
        $('#date_from').val('');
        $('#date_to').val('');
        $('#gift_status').val('');
        $('#product').val('');
        $('#gifter_email').val('');
        $('#recipient_email').val('');

        $('#redemption_from').val('');
        $('#redemption_to').val('');
        
        // Submit the form to refresh the page
        $('form').submit();
    };

    /**
     * Resend gift email function
     */
    window.mpgrResendGiftEmail = function(giftId) {
        var $btn = $('.mpgr-resend-email[data-gift-id="' + giftId + '"]');
        var originalText = '📧'; // Hardcode the original text to ensure consistency
        
        // Prevent multiple clicks
        if ($btn.prop('disabled')) {
            return;
        }
        
        // Show loading state
        $btn.text('⏳').prop('disabled', true).addClass('mpgr-loading');

        $.ajax({
            url: mpgr_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'mpgr_resend_gift_email',
                nonce: mpgr_ajax.resend_email_nonce,
                gift_transaction_id: giftId
            },
            success: function(response) {
                if (response.success) {
                    // Show success state briefly
                    $btn.text('✅').removeClass('mpgr-loading').addClass('mpgr-success');
                    showMessage(response.data.message, 'success');
                    
                    // Reset button after 2 seconds
                    setTimeout(function() {
                        $btn.text(originalText).removeClass('mpgr-success').prop('disabled', false);
                    }, 2000);
                } else {
                    showMessage(response.data || 'Error resending gift email', 'error');
                    $btn.text(originalText).removeClass('mpgr-loading').prop('disabled', false);
                }
            },
            error: function() {
                showMessage('Error resending gift email. Please try again.', 'error');
                $btn.text(originalText).removeClass('mpgr-loading').prop('disabled', false);
            }
        });
    };

    /**
     * Copy redemption link function
     */
    window.mpgrCopyRedemptionLink = function(giftId) {
        var $btn = $('.mpgr-copy-link[data-gift-id="' + giftId + '"]');
        var originalText = '🔗'; // Hardcode the original text to ensure consistency
        
        // Prevent multiple clicks
        if ($btn.prop('disabled')) {
            return;
        }
        
        // Show loading state
        $btn.text('⏳').prop('disabled', true).addClass('mpgr-loading');

        $.ajax({
            url: mpgr_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'mpgr_copy_redemption_link',
                nonce: mpgr_ajax.copy_link_nonce,
                gift_transaction_id: giftId
            },
            success: function(response) {
                if (response.success) {
                    // Copy to clipboard
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(response.data.redemption_link).then(function() {
                            // Show success state briefly
                            $btn.text('✅').removeClass('mpgr-loading').addClass('mpgr-success');
                            showMessage(response.data.message, 'success');
                            
                            // Reset button after 2 seconds
                            setTimeout(function() {
                                $btn.text(originalText).removeClass('mpgr-success').prop('disabled', false);
                            }, 2000);
                        }).catch(function() {
                            // Fallback for older browsers
                            copyToClipboardFallback(response.data.redemption_link);
                            $btn.text('✅').removeClass('mpgr-loading').addClass('mpgr-success');
                            showMessage(response.data.message, 'success');
                            
                            // Reset button after 2 seconds
                            setTimeout(function() {
                                $btn.text(originalText).removeClass('mpgr-success').prop('disabled', false);
                            }, 2000);
                        });
                    } else {
                        // Fallback for older browsers
                        copyToClipboardFallback(response.data.redemption_link);
                        $btn.text('✅').removeClass('mpgr-loading').addClass('mpgr-success');
                        showMessage(response.data.message, 'success');
                        
                        // Reset button after 2 seconds
                        setTimeout(function() {
                            $btn.text(originalText).removeClass('mpgr-success').prop('disabled', false);
                        }, 2000);
                    }
                } else {
                    showMessage(response.data || 'Error copying redemption link', 'error');
                    $btn.text(originalText).removeClass('mpgr-loading').prop('disabled', false);
                }
            },
            error: function() {
                showMessage('Error copying redemption link. Please try again.', 'error');
                $btn.text(originalText).removeClass('mpgr-loading').prop('disabled', false);
            }
        });
    };

    /**
     * Fallback copy to clipboard function for older browsers
     */
    function copyToClipboardFallback(text) {
        var textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
        } catch (err) {
            console.error('Fallback: Oops, unable to copy', err);
        }
        
        document.body.removeChild(textArea);
    }

    /**
     * Document ready
     */
    $(document).ready(function() {
        // Add message styles
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .mpgr-message {
                    padding: 10px 15px;
                    margin: 10px 0;
                    border-radius: 4px;
                    font-weight: bold;
                }
                .mpgr-success {
                    background-color: #d4edda;
                    color: #155724;
                    border: 1px solid #c3e6cb;
                }
                .mpgr-error {
                    background-color: #f8d7da;
                    color: #721c24;
                    border: 1px solid #f5c6cb;
                }
            `)
            .appendTo('head');

        // Handle export button clicks
        $(document).on('click', '.mpgr-export-btn', function(e) {
            e.preventDefault();
            mpgrExportCSV();
        });

        // Handle action button clicks
        $(document).on('click', '.mpgr-resend-email', function(e) {
            e.preventDefault();
            var giftId = $(this).data('gift-id');
            mpgrResendGiftEmail(giftId);
        });

        $(document).on('click', '.mpgr-copy-link', function(e) {
            e.preventDefault();
            var giftId = $(this).data('gift-id');
            mpgrCopyRedemptionLink(giftId);
        });

        // Add loading indicator for table
        $('.mpgr-table').on('load', function() {
            $(this).addClass('mpgr-loaded');
        });

        // Responsive table handling
        if ($(window).width() < 768) {
            $('.mpgr-table').addClass('mpgr-mobile');
        }

        $(window).resize(function() {
            if ($(window).width() < 768) {
                $('.mpgr-table').addClass('mpgr-mobile');
            } else {
                $('.mpgr-table').removeClass('mpgr-mobile');
            }
        });

        // Enhanced tooltip functionality
        $('.mpgr-action-btn').each(function() {
            var $btn = $(this);
            var title = $btn.attr('title');
            
            // Set proper tooltip text based on button class
            var tooltipText = '';
            if ($btn.hasClass('mpgr-resend-email')) {
                tooltipText = '📧 Resend gift email to gifter';
            } else if ($btn.hasClass('mpgr-copy-link')) {
                tooltipText = '🔗 Copy redemption link to clipboard';
            } else {
                tooltipText = title; // Fallback to original title
            }
            
            // Remove the title attribute to prevent default browser tooltip
            $btn.removeAttr('title');
            
            // Add custom tooltip on hover (only when not in loading/success state)
            $btn.hover(
                function() {
                    // Don't show tooltip if button is in loading or success state
                    if ($(this).hasClass('mpgr-loading') || $(this).hasClass('mpgr-success')) {
                        return;
                    }
                    
                    var $tooltip = $('<div class="mpgr-custom-tooltip">' + tooltipText + '</div>');
                    $tooltip.css({
                        position: 'absolute',
                        bottom: '100%',
                        left: '50%',
                        transform: 'translateX(-50%)',
                        background: 'rgba(0, 0, 0, 0.9)',
                        color: 'white',
                        padding: '6px 10px',
                        borderRadius: '4px',
                        fontSize: '12px',
                        whiteSpace: 'nowrap',
                        zIndex: '1000',
                        marginBottom: '5px',
                        boxShadow: '0 2px 8px rgba(0, 0, 0, 0.3)',
                        pointerEvents: 'none'
                    });
                    
                    $btn.append($tooltip);
                },
                function() {
                    $btn.find('.mpgr-custom-tooltip').remove();
                }
            );
        });
    });

})(jQuery);

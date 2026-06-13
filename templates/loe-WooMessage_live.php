<?php
/**
 * Template: Simple Enquiry Form (textarea version, below Add to Cart)
 * Plugin:   LOE Woo Messages
 * Author:   Loeion (https://www.loeion.com)
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

global $product, $post;

// Safety checks
if ( ! isset( $post->post_author ) || ! $product instanceof WC_Product ) {
    return;
}

$author_id  = absint( $post->post_author );
$product_id = absint( $product->get_id() );
$unique_id  = 'loe' . wp_generate_password( 10, false, false );
$nonce      = wp_create_nonce( 'loe_woo_msg_nonce' );
?>

<!-- Trigger Button -->
<input
  type="button"
  onclick="loeShowEnquiry()"
  class="loe-send-enquiry"
  value="<?php esc_attr_e( 'Make an Enquiry', 'loe-woo-messages' ); ?>"
>

<!-- Enquiry Modal -->
<div id="loeEnquirySimple" role="dialog" aria-modal="true" aria-labelledby="loe-enquiry-simple-title" style="display:none;">

  <h2 id="loe-enquiry-simple-title"><?php esc_html_e( 'Make an Enquiry', 'loe-woo-messages' ); ?></h2>

  <span id="loe-thank-you-message" style="display:none;"></span>
  <div id="loe-error-message"></div>

  <input
    type="text"
    name="user_id"
    id="loe-user-id"
    placeholder="<?php esc_attr_e( 'Your Name', 'loe-woo-messages' ); ?>"
    autocomplete="name"
  >
  <input
    type="email"
    name="user_email"
    id="loe-user-email"
    placeholder="<?php esc_attr_e( 'Your Email', 'loe-woo-messages' ); ?>"
    autocomplete="email"
  >
  <input
    type="text"
    name="title"
    id="loe-title"
    placeholder="<?php esc_attr_e( 'Enquiry Title', 'loe-woo-messages' ); ?>"
  >
  <textarea
    name="message"
    id="loe-message"
    placeholder="<?php esc_attr_e( 'Your Message', 'loe-woo-messages' ); ?>"
    rows="5"
  ></textarea>

  <!-- Hidden Fields -->
  <input type="hidden" id="loe-vendor-id"      value="<?php echo esc_attr( $author_id ); ?>">
  <input type="hidden" id="loe-product-id"     value="<?php echo esc_attr( $product_id ); ?>">
  <input type="hidden" id="loe-chat-unique-id" value="<?php echo esc_attr( $unique_id ); ?>">
  <input type="hidden" id="loe-nonce"          value="<?php echo esc_attr( $nonce ); ?>">

  <input
    type="button"
    onclick="loeSendSimpleEnquiry()"
    class="loe-button-enquiry"
    value="<?php esc_attr_e( 'Send Enquiry', 'loe-woo-messages' ); ?>"
  >

  <!-- Close Button -->
  <a
    class="loe-cancel"
    href="javascript:;"
    onclick="loeHideEnquiry()"
    aria-label="<?php esc_attr_e( 'Close', 'loe-woo-messages' ); ?>"
  >&times;</a>

</div><!-- /#loeEnquirySimple -->


<script type="text/javascript">
(function($) {
  'use strict';

  var ajaxurl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';

  // ── Show / Hide Modal ─────────────────────────────────────────────
  window.loeShowEnquiry = function() {
    $('#loeEnquirySimple').fadeIn( 200 );
    $('#loe-user-id').focus();
  };

  window.loeHideEnquiry = function() {
    $('#loeEnquirySimple').fadeOut( 200 );
    loeResetForm();
  };

  // Close on Escape key
  $(document).on('keydown', function(e) {
    if ( e.key === 'Escape' && $('#loeEnquirySimple').is(':visible') ) {
      loeHideEnquiry();
    }
  });

  // ── Send Enquiry ──────────────────────────────────────────────────
  window.loeSendSimpleEnquiry = function() {

    var user_id        = $('#loe-user-id').val().trim();
    var user_email     = $('#loe-user-email').val().trim();
    var title          = $('#loe-title').val().trim();
    var message        = $('#loe-message').val().trim();
    var vendor_id      = $('#loe-vendor-id').val();
    var product_id     = $('#loe-product-id').val();
    var chat_unique_id = $('#loe-chat-unique-id').val();
    var nonce          = $('#loe-nonce').val();

    // ── Validation ────────────────────────────────────────────────
    if ( ! user_id ) {
      $('#loe-user-id').focus();
      $('#loe-error-message').html('<?php esc_html_e( 'Name is required.', 'loe-woo-messages' ); ?>');
      return;
    }
    if ( ! user_email ) {
      $('#loe-user-email').focus();
      $('#loe-error-message').html('<?php esc_html_e( 'Email is required.', 'loe-woo-messages' ); ?>');
      return;
    }
    if ( ! loeValidateEmail( user_email ) ) {
      $('#loe-user-email').focus();
      $('#loe-error-message').html('<?php esc_html_e( 'Invalid email address.', 'loe-woo-messages' ); ?>');
      return;
    }
    if ( ! title ) {
      $('#loe-title').focus();
      $('#loe-error-message').html('<?php esc_html_e( 'Title is required.', 'loe-woo-messages' ); ?>');
      return;
    }
    if ( ! message ) {
      $('#loe-message').focus();
      $('#loe-error-message').html('<?php esc_html_e( 'Please enter a message.', 'loe-woo-messages' ); ?>');
      return;
    }

    $('#loe-error-message').html('');

    // ── Submit ────────────────────────────────────────────────────
    $.post(
      ajaxurl,
      {
        action:         'loe_send_woo_message',
        nonce:          nonce,
        userId:         user_id,
        title:          title,
        vendor_id:      vendor_id,
        product_id:     product_id,
        chat_unique_id: chat_unique_id,
        message:        message,
        user_email:     user_email
      },
      function( response ) {
        if ( response.success ) {
          $('#loe-thank-you-message')
            .text('<?php esc_html_e( 'Thank you for your enquiry — we will get back to you shortly.', 'loe-woo-messages' ); ?>')
            .show();

          loeResetForm();

          setTimeout(function() {
            $('#loe-thank-you-message').hide();
            loeHideEnquiry();
          }, 2000 );

        } else {
          $('#loe-error-message').html(
            response.data || '<?php esc_html_e( 'Failed to send. Please try again.', 'loe-woo-messages' ); ?>'
          );
        }
      }
    ).fail(function() {
      $('#loe-error-message').html('<?php esc_html_e( 'Connection error. Please try again.', 'loe-woo-messages' ); ?>');
    });
  };

  // ── Helpers ───────────────────────────────────────────────────────
  function loeResetForm() {
    $('#loe-user-id').val('');
    $('#loe-user-email').val('');
    $('#loe-title').val('');
    $('#loe-message').val('');
    $('#loe-error-message').html('');
  }

  function loeValidateEmail( email ) {
    var expr = /^([\w\-.]+)@([\w-]+\.)+[a-zA-Z]{2,}$/;
    return expr.test( email );
  }

})(jQuery);
</script>


<style type="text/css">

/* ── Trigger Button ────────────────────────────── */
.loe-send-enquiry {
  background-color: #ff7550;
  border: none;
  font-size: 20px;
  line-height: 28px;
  font-weight: 300;
  letter-spacing: 1px;
  color: #fff;
  padding: 12px 24px;
  cursor: pointer;
  outline: none;
  text-transform: uppercase;
  transition: background 0.3s linear;
  border-radius: 3px;
  margin: 10px 0 0;
  display: block;
  width: 100%;
  text-align: center;
  box-sizing: border-box;
}
.loe-send-enquiry:hover {
  background-color: #e05e38;
}

/* ── Modal ─────────────────────────────────────── */
#loeEnquirySimple {
  transform: translate(-50%, -50%);
  -webkit-transform: translate(-50%, -50%);
  -ms-transform: translate(-50%, -50%);
  position: fixed;
  left: 50%;
  top: 50%;
  z-index: 99999;
  background: #096685;
  width: 320px;
  max-width: 95vw;
  padding: 24px 5% 20px;
  border-radius: 6px;
  box-sizing: border-box;
  overflow: hidden;
}

/* ── Modal Title ───────────────────────────────── */
#loeEnquirySimple h2 {
  font-size: 20px;
  margin: 0 0 16px;
  color: #fff;
  padding-right: 24px;
}

/* ── Inputs ────────────────────────────────────── */
#loeEnquirySimple input[type="text"],
#loeEnquirySimple input[type="email"] {
  width: 100%;
  float: left;
  border: 1px solid rgba(255,255,255,0.4);
  background: rgba(255,255,255,0.1);
  color: #fff;
  height: 36px;
  font-size: 13px;
  padding: 0 10px;
  margin: 0 0 10px;
  border-radius: 3px;
  box-sizing: border-box;
  transition: border-color 0.2s ease;
}
#loeEnquirySimple input[type="text"]:focus,
#loeEnquirySimple input[type="email"]:focus {
  border-color: #f39b00;
  outline: none;
  background: rgba(255,255,255,0.15);
}
#loeEnquirySimple input::placeholder {
  color: rgba(255,255,255,0.7);
  opacity: 1;
}

/* ── Textarea ──────────────────────────────────── */
#loeEnquirySimple textarea {
  width: 100%;
  float: left;
  border: 1px solid rgba(255,255,255,0.4);
  background: rgba(255,255,255,0.1);
  color: #fff;
  padding: 10px;
  margin: 0 0 10px;
  resize: vertical;
  min-height: 90px;
  border-radius: 3px;
  font-size: 13px;
  box-sizing: border-box;
  transition: border-color 0.2s ease;
}
#loeEnquirySimple textarea:focus {
  border-color: #f39b00;
  outline: none;
  background: rgba(255,255,255,0.15);
}
#loeEnquirySimple textarea::placeholder {
  color: rgba(255,255,255,0.7);
  opacity: 1;
}

/* ── Send Button ───────────────────────────────── */
.loe-button-enquiry {
  background: #f39b00;
  border: none;
  color: #fff;
  font-size: 14px;
  height: 36px;
  padding: 0 20px;
  font-weight: 700;
  text-transform: uppercase;
  border-radius: 3px;
  cursor: pointer;
  float: right;
  margin: 4px 0 0;
  transition: background 0.2s ease;
}
.loe-button-enquiry:hover {
  background: #5c5e62;
}

/* ── Error / Success Messages ──────────────────── */
#loe-error-message {
  color: #ffcccc;
  margin-bottom: 8px;
  font-size: 12px;
  float: left;
  width: 100%;
  min-height: 16px;
}
#loe-thank-you-message {
  display: none;
  background: #27ae60;
  color: #fff;
  padding: 8px 12px;
  font-size: 13px;
  border-radius: 3px;
  float: left;
  width: 100%;
  margin-bottom: 10px;
  box-sizing: border-box;
  line-height: 1.4;
}

/* ── Close Button ──────────────────────────────── */
.loe-cancel {
  position: absolute;
  top: 8px;
  right: 12px;
  color: #fff;
  font-size: 24px;
  line-height: 1;
  text-decoration: none;
  opacity: 0.8;
  transition: opacity 0.2s ease;
}
.loe-cancel:hover {
  opacity: 1;
  color: #f39b00;
}

/* ── Responsive ────────────────────────────────── */
@media ( max-width: 480px ) {
  #loeEnquirySimple {
    width: 95vw;
    padding: 20px 4% 16px;
  }
}

</style>

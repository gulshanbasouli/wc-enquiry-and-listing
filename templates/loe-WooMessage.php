<?php
/**
 * Template: Enquiry Form (below Add to Cart button)
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
$unique_id  = 'loe' . wp_generate_password( 10, false, false ); // Cryptographically safer than rand()
$nonce      = wp_create_nonce( 'loe_woo_msg_nonce' );
?>

<!-- Trigger Button -->
<input
  type="button"
  onclick="loeShowEnquiry()"
  class="loe-send-enquiry"
  value="<?php esc_attr_e( 'Ask More Info Now', 'loe-woo-messages' ); ?>"
>

<!-- Enquiry Modal -->
<div id="loeEnquiry" role="dialog" aria-modal="true" aria-labelledby="loe-enquiry-title" style="display:none;">
  <div class="loe-enquiry-inner">

    <h2 id="loe-enquiry-title"><?php esc_html_e( 'Ask More Info Now', 'loe-woo-messages' ); ?></h2>

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

    <div
      class="loe-editable"
      id="loe-message"
      contenteditable="true"
      aria-label="<?php esc_attr_e( 'Your message', 'loe-woo-messages' ); ?>"
    ></div>
    <span class="loe-alert-message" style="display:none;">
      <?php esc_html_e( 'Your message contains restricted content (phone, email or URL).', 'loe-woo-messages' ); ?>
    </span>

    <!-- Hidden Fields -->
    <input type="hidden" id="loe-vendor-id"     value="<?php echo esc_attr( $author_id ); ?>">
    <input type="hidden" id="loe-product-id"    value="<?php echo esc_attr( $product_id ); ?>">
    <input type="hidden" id="loe-chat-unique-id" value="<?php echo esc_attr( $unique_id ); ?>">
    <input type="hidden" id="loe-nonce"         value="<?php echo esc_attr( $nonce ); ?>">

    <input
      type="button"
      onclick="loeSendEnquiry()"
      class="loe-button-enquiry"
      value="<?php esc_attr_e( 'Send', 'loe-woo-messages' ); ?>"
    >

    <!-- Close Button -->
    <a class="loe-cancel" href="javascript:;" onclick="loeHideEnquiry()" aria-label="<?php esc_attr_e( 'Close', 'loe-woo-messages' ); ?>">
      <span aria-hidden="true">&times;</span>
    </a>

  </div><!-- /.loe-enquiry-inner -->
</div><!-- /#loeEnquiry -->


<script type="text/javascript">
(function($) {
  'use strict';

  var ajaxurl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';

  // ── Show / Hide Modal ─────────────────────────────────────────────
  window.loeShowEnquiry = function() {
    $('#loeEnquiry').fadeIn( 200 );
    $('#loe-user-id').focus();
  };

  window.loeHideEnquiry = function() {
    $('#loeEnquiry').fadeOut( 200 );
    loeResetForm();
  };

  // Close on overlay click
  $('#loeEnquiry').on('click', function(e) {
    if ( $(e.target).is('#loeEnquiry') ) {
      loeHideEnquiry();
    }
  });

  // Close on Escape key
  $(document).on('keydown', function(e) {
    if ( e.key === 'Escape' && $('#loeEnquiry').is(':visible') ) {
      loeHideEnquiry();
    }
  });

  // ── Send Enquiry ──────────────────────────────────────────────────
  window.loeSendEnquiry = function() {

    var user_id        = $('#loe-user-id').val().trim();
    var user_email     = $('#loe-user-email').val().trim();
    var title          = $('#loe-title').val().trim();
    var message        = $('#loe-message').text().trim();
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

    // Spam check — block phone / email / URL in message body
    if ( loeMessageHasRestrictedContent( message ) ) {
      $('.loe-alert-message').show();
      return;
    }

    $('.loe-alert-message').hide();
    $('#loe-error-message').html('');

    // ── Send ──────────────────────────────────────────────────────
    $.post(
      ajaxurl,
      {
        action:        'loe_send_woo_message',
        nonce:         nonce,
        userId:        user_id,
        title:         title,
        vendor_id:     vendor_id,
        product_id:    product_id,
        chat_unique_id: chat_unique_id,
        message:       message,
        user_email:    user_email
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
    $('#loe-message').text('');
    $('#loe-error-message').html('');
    $('.loe-alert-message').hide();
  }

  function loeValidateEmail( email ) {
    var expr = /^([\w\-.]+)@([\w-]+\.)+[a-zA-Z]{2,}$/;
    return expr.test( email );
  }

  function loeMessageHasRestrictedContent( message ) {
    var words    = message.match(/\S+/g) || [];
    var emailPat = /^([\w\-.]+)@([\w-]+\.)+[a-zA-Z]{2,}$/;
    var phonePat = /^\d{10}$/;
    var urlPat   = /^[A-Za-z]+:\/\/[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_%&?/.=]+$/;
    var found    = false;

    $.each( words, function( i, word ) {
      if ( emailPat.test(word) || phonePat.test(word) || urlPat.test(word) ) {
        found = true;
        return false; // break
      }
    });

    return found;
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

/* ── Modal Overlay ─────────────────────────────── */
#loeEnquiry {
  position: fixed;
  top: 0;
  left: 0;
  background: rgba(0, 0, 0, 0.7);
  height: 100%;
  width: 100%;
  z-index: 99999;
}

/* ── Modal Box ─────────────────────────────────── */
.loe-enquiry-inner {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  background: #fff;
  width: 320px;
  max-width: 95vw;
  padding: 24px 25px 20px;
  border-radius: 6px;
  box-sizing: border-box;
}
.loe-enquiry-inner h2 {
  font-size: 18px;
  margin: 0 0 16px;
  color: #444;
  padding-right: 20px;
}

/* ── Inputs ────────────────────────────────────── */
.loe-enquiry-inner input[type="text"],
.loe-enquiry-inner input[type="email"] {
  float: left;
  width: 100%;
  border: 1px solid #ccc;
  color: #444;
  height: 36px;
  font-size: 13px;
  padding: 0 10px;
  margin: 0 0 10px;
  border-radius: 3px;
  box-sizing: border-box;
  background: #fafafa;
}
.loe-enquiry-inner input[type="text"]:focus,
.loe-enquiry-inner input[type="email"]:focus {
  border-color: #f39b00;
  outline: none;
  background: #fff;
}

/* ── Editable Message Area ─────────────────────── */
.loe-editable {
  width: 100%;
  min-height: 80px;
  border: 1px solid #ccc;
  display: block;
  padding: 10px;
  color: #444;
  font-size: 13px;
  margin: 0 0 6px;
  background: #fafafa;
  border-radius: 3px;
  box-sizing: border-box;
  text-align: left;
}
.loe-editable:focus {
  border-color: #f39b00;
  outline: none;
  background: #fff;
}
.loe-editable span {
  background: yellow;
  color: #333;
}

/* ── Alert / Error Messages ────────────────────── */
.loe-alert-message {
  display: none;
  background: #c0392b;
  color: #fff;
  padding: 4px 10px;
  font-size: 12px;
  border-radius: 3px;
  margin-bottom: 8px;
  float: left;
  width: 100%;
  box-sizing: border-box;
}
#loe-error-message {
  color: #c0392b;
  margin-bottom: 6px;
  font-size: 12px;
  float: left;
  width: 100%;
  min-height: 16px;
}

/* ── Thank You Message ─────────────────────────── */
#loe-thank-you-message {
  display: none;
  background: #27ae60;
  color: #fff;
  padding: 8px 12px;
  font-size: 13px;
  border-radius: 3px;
  float: left;
  width: 100%;
  margin-bottom: 12px;
  box-sizing: border-box;
  line-height: 1.4;
}

/* ── Send Button ───────────────────────────────── */
.loe-button-enquiry {
  float: right;
  background: #f39b00;
  border: none;
  color: #fff;
  font-size: 14px;
  padding: 8px 20px;
  font-weight: 700;
  text-transform: uppercase;
  border-radius: 3px;
  cursor: pointer;
  margin: 8px 0 0;
  transition: background 0.2s ease;
}
.loe-button-enquiry:hover {
  background: #5c5e62;
}

/* ── Close Button ──────────────────────────────── */
.loe-cancel {
  position: absolute;
  top: 8px;
  right: 12px;
  color: #444;
  font-size: 22px;
  line-height: 1;
  text-decoration: none;
}
.loe-cancel:hover {
  color: #c0392b;
}

/* ── Responsive ────────────────────────────────── */
@media ( max-width: 480px ) {
  .loe-enquiry-inner {
    width: 95vw;
    padding: 20px 16px 16px;
  }
}

</style>

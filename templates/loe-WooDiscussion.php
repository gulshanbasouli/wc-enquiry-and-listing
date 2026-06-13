<?php
/**
 * Template: Frontend Discussion Thread
 * Plugin:   LOE Woo Messages
 * Author:   Loeion (https://www.loeion.com)
 *
 * Shortcode: [loe-frontend-discussions]
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?>

<div class="loe-discussion-main">
  <div class="loe-discussion-inner">

    <!-- Enquiry ID Lookup Form -->
    <form action="" method="post" name="enquiry-listing" class="loe-lookup-form">
      <input
        type="text"
        name="enquiry-loe-id"
        id="enquiry-loe-id"
        placeholder="<?php esc_attr_e( 'Enquiry ID e.g. loe4c5xtd4hf', 'loe-woo-messages' ); ?>"
        autocomplete="off"
      >
      <input
        type="button"
        onclick="loeCheckEnquiry()"
        id="enquiry-loe-btn-id"
        value="<?php esc_attr_e( 'GO', 'loe-woo-messages' ); ?>"
      >
    </form>

    <!-- States -->
    <div class="loe-thread-not-found" style="display:none;">
      <h3><?php esc_html_e( 'No enquiry found!', 'loe-woo-messages' ); ?></h3>
    </div>

    <div class="loe-thread-loading" style="display:none;">
      <p><?php esc_html_e( 'Loading…', 'loe-woo-messages' ); ?></p>
    </div>

    <!-- Thread Header (product / vendor / title info) -->
    <ul class="loe-reply-thread-head" style="display:none;"></ul>

    <!-- Message Thread -->
    <ul class="loe-reply-thread" style="display:none;"></ul>

    <!-- Reply Box -->
    <div class="loe-thread-reply" style="display:none;">
      <form action="" method="post" name="enquiry-thread">
        <span id="loe-thank-you-message" style="display:none;"></span>
        <div id="loe-error-message"></div>
        <div
          class="loe-editable"
          id="loe-message"
          contenteditable="true"
          aria-label="<?php esc_attr_e( 'Your reply', 'loe-woo-messages' ); ?>"
        ></div>
        <span class="loe-alert-message" style="display:none;">
          <?php esc_html_e( 'Your message contains restricted content (phone, email, or URL).', 'loe-woo-messages' ); ?>
        </span>
        <input
          type="button"
          onclick="loeWooDiscussion()"
          class="loe-button-enquiry"
          value="<?php esc_attr_e( 'Reply', 'loe-woo-messages' ); ?>"
        >
      </form>
    </div>

  </div><!-- /.loe-discussion-inner -->
</div><!-- /.loe-discussion-main -->


<script type="text/javascript">
(function($) {
  'use strict';

  var ajaxurl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
  var nonce   = '<?php echo esc_js( wp_create_nonce( 'loe_woo_msg_nonce' ) ); ?>';

  // ── Check Enquiry Thread ──────────────────────────────────────────
  window.loeCheckEnquiry = function() {
    var enquiryId = $('#enquiry-loe-id').val().trim();

    if ( ! enquiryId ) {
      $('#loe-error-message').html('<?php esc_html_e( 'Please enter an enquiry ID.', 'loe-woo-messages' ); ?>');
      return;
    }

    $('#loe-error-message').html('');
    $('.loe-thread-loading').show();
    $('.loe-thread-not-found').hide();
    $('.loe-reply-thread').hide().html('');
    $('.loe-reply-thread-head').hide().html('');
    $('.loe-thread-reply').hide();

    $.post(
      ajaxurl,
      {
        action:    'loe_check_enquiry',
        nonce:     nonce,
        enquiryId: enquiryId
      },
      function( response ) {
        $('.loe-thread-loading').hide();

        if ( response.success && response.data && response.data.length > 0 ) {
          loeMainThreadHead( enquiryId );
          loeRenderMessages( response.data );
          $('.loe-thread-reply').show();
          $('.loe-thread-not-found').hide();
        } else {
          $('.loe-thread-reply').hide();
          $('.loe-thread-not-found').show();
          $('.loe-reply-thread').hide();
          $('.loe-reply-thread-head').hide();
        }
      }
    ).fail(function() {
      $('.loe-thread-loading').hide();
      $('#loe-error-message').html('<?php esc_html_e( 'Connection error. Please try again.', 'loe-woo-messages' ); ?>');
    });
  };

  // ── Render Messages ───────────────────────────────────────────────
  function loeRenderMessages( data ) {
    var $thread = $('.loe-reply-thread').empty();

    $.each( data, function( i, msg ) {
      if ( ! msg ) return; // skip empty/undefined entries

      var safeMessage = $('<div>').text( msg.message ).html(); // XSS-safe
      var safeTime    = $('<div>').text( msg.last_update ).html();
      var cssClass    = ( msg.user_type === 'admin' ) ? 'loe-admin-message' : 'loe-you-message';

      $thread.append(
        '<li class="' + cssClass + '">' +
          '<div class="loe-message-body">' + safeMessage + '</div>' +
          '<div class="loe-message-time">' + safeTime + '</div>' +
        '</li>'
      );
    });

    $thread.show();
  }

  // ── Load Thread Header Info ───────────────────────────────────────
  function loeMainThreadHead( enquiryId ) {
    $.post(
      ajaxurl,
      {
        action:    'loe_get_thread_main',
        nonce:     nonce,
        enquiryId: enquiryId
      },
      function( response ) {
        if ( response.success && response.data ) {
          var d    = response.data;
          var safe = function( str ) { return $('<div>').text( str ).html(); };

          var today = loeFormattedDate();

          $('.loe-reply-thread-head')
            .html('')
            .append(
              '<li>' +
                '<h3>' + safe( d.enquiry_title )  + '</h3>' +
                '<h4>' + safe( d.product_title )  + '</h4>' +
                '<span>' + safe( d.vendor_name )  + '</span>' +
              '</li>' +
              '<li class="loe-thread-date">' + today + '</li>'
            )
            .show();
        }
      }
    );
  }

  // ── Submit User Reply ─────────────────────────────────────────────
  window.loeWooDiscussion = function() {
    var threadId = $('#enquiry-loe-id').val().trim();
    var message  = $('#loe-message').text().trim();

    if ( ! message ) {
      $('#loe-message').focus();
      $('#loe-error-message').html('<?php esc_html_e( 'Please enter a message.', 'loe-woo-messages' ); ?>');
      return;
    }

    // Basic spam check — block phone/email/URL in message
    if ( loeMessageHasRestrictedContent( message ) ) {
      $('.loe-alert-message').show();
      return;
    }

    $('.loe-alert-message').hide();
    $('#loe-error-message').html('');

    $.post(
      ajaxurl,
      {
        action:    'loe_users_reply',
        nonce:     nonce,
        message:   message,
        thread_id: threadId
      },
      function( response ) {
        if ( response.success ) {
          $('#loe-thank-you-message')
            .text('<?php esc_html_e( 'Thank you! Your reply has been sent.', 'loe-woo-messages' ); ?>')
            .show();
          $('#loe-message').text('');

          setTimeout(function() {
            $('#loe-thank-you-message').hide();
            loeCheckEnquiry();
          }, 1500 );
        } else {
          $('#loe-error-message').html(
            response.data || '<?php esc_html_e( 'Failed to send reply. Please try again.', 'loe-woo-messages' ); ?>'
          );
        }
      }
    ).fail(function() {
      $('#loe-error-message').html('<?php esc_html_e( 'Connection error. Please try again.', 'loe-woo-messages' ); ?>');
    });
  };

  // ── Helpers ───────────────────────────────────────────────────────
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

  function loeFormattedDate() {
    var monthNames = [
      'January','February','March','April','May','June',
      'July','August','September','October','November','December'
    ];
    var dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    var now = new Date();
    return dayNames[now.getDay()] + ' ' + now.getDate() + ' ' + monthNames[now.getMonth()] + ' ' + now.getFullYear();
  }

})(jQuery);
</script>


<style type="text/css">

/* ── Layout ────────────────────────────────────── */
.loe-discussion-main {
  margin: 0 auto;
  max-width: 680px;
  width: 100%;
}
.loe-discussion-inner {
  float: left;
  width: 100%;
  background: #e1f5fe;
  padding: 20px;
  border-radius: 10px;
  box-sizing: border-box;
}

/* ── Lookup Form ───────────────────────────────── */
.loe-lookup-form {
  float: left;
  width: 100%;
  margin-bottom: 16px;
}
#enquiry-loe-id {
  float: left;
  width: 74%;
  border: 1px solid #5c5e62;
  background: #fff;
  padding: 14px 10px;
  color: #5c5e62;
  font-size: 18px;
  line-height: 24px;
  border-radius: 3px;
  margin: 0 2% 0 0;
  box-sizing: border-box;
}
#enquiry-loe-btn-id {
  float: left;
  width: 24%;
  background: #f39b00;
  border: none;
  color: #fff;
  font-size: 18px;
  line-height: 24px;
  padding: 14px 0;
  border-radius: 3px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s ease;
  box-sizing: border-box;
}
#enquiry-loe-btn-id:hover {
  background: #5c5e62;
}

/* ── States ────────────────────────────────────── */
.loe-thread-not-found {
  float: left;
  width: 100%;
  margin: 30px 0 0;
  text-align: center;
}
.loe-thread-not-found h3 {
  color: #096685;
  font-size: 32px;
  line-height: 1.2;
  text-transform: uppercase;
  font-weight: 700;
}
.loe-thread-loading {
  float: left;
  width: 100%;
  padding: 20px 0;
  text-align: center;
  color: #096685;
  font-weight: 600;
}

/* ── Thread Header ─────────────────────────────── */
.loe-reply-thread-head {
  float: left;
  width: 100%;
  padding: 10px 10px 2px;
  margin: 16px 0 22px;
  list-style: none;
}
.loe-reply-thread-head li {
  display: inline-block;
  font-weight: 300;
  width: 100%;
}
.loe-reply-thread-head h3 {
  color: #4b4b4b;
  font-weight: 700;
  margin-bottom: 5px;
}
.loe-reply-thread-head h4 {
  color: #4b4b4b;
  font-weight: 600;
  margin-bottom: 5px;
}
.loe-thread-date {
  text-align: right;
  float: right;
  font-weight: 400;
  margin-top: -17px;
}

/* ── Messages ──────────────────────────────────── */
.loe-reply-thread {
  float: left;
  width: 100%;
  padding: 0;
  list-style: none;
}
.loe-you-message,
.loe-admin-message {
  float: left;
  position: relative;
  width: 100%;
  padding: 16px 20px 36px;
  border-radius: 6px;
  margin-bottom: 8px;
  box-sizing: border-box;
  word-wrap: break-word;
}
.loe-you-message {
  background: #fff;
}
.loe-admin-message {
  background: #dfdfdf;
  color: #202140;
}
.loe-message-body {
  font-size: 14px;
  line-height: 1.5;
}
.loe-message-time {
  font-size: 11px;
  position: absolute;
  right: 16px;
  bottom: 6px;
  color: #777;
}

/* ── Reply Box ─────────────────────────────────── */
.loe-thread-reply {
  float: left;
  width: 100%;
  margin-top: 16px;
}
.loe-editable {
  width: 100%;
  min-height: 80px;
  border: 1px solid #ccc;
  display: inline-block;
  padding: 10px;
  color: #444;
  font-size: 13px;
  margin: 0 0 8px;
  background: #fff;
  border-radius: 3px;
  box-sizing: border-box;
}
.loe-button-enquiry {
  float: right;
  background: #5c5e62;
  border: none;
  color: #fff;
  font-size: 15px;
  padding: 8px 24px;
  font-weight: 600;
  text-transform: uppercase;
  border-radius: 3px;
  cursor: pointer;
  transition: background 0.2s ease;
}
.loe-button-enquiry:hover {
  background: #f39b00;
}
.loe-alert-message {
  display: none;
  background: #c0392b;
  color: #fff;
  padding: 4px 10px;
  font-size: 12px;
  border-radius: 3px;
  margin-bottom: 6px;
  float: left;
  width: 100%;
  box-sizing: border-box;
}

/* ── Feedback Messages ─────────────────────────── */
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
}
#loe-error-message {
  color: #c0392b;
  font-size: 12px;
  margin-bottom: 6px;
  float: left;
  width: 100%;
}

/* ── Links ─────────────────────────────────────── */
.loe-discussion-main a {
  color: #5874bf;
  text-decoration: none;
}
.loe-discussion-main a:hover {
  color: #112763;
}

/* ── Responsive ────────────────────────────────── */
@media ( max-width: 600px ) {
  .loe-discussion-main {
    width: 100%;
  }
  #enquiry-loe-id {
    width: 68%;
  }
  #enquiry-loe-btn-id {
    width: 30%;
  }
  .loe-thread-not-found h3 {
    font-size: 22px;
  }
}

</style>

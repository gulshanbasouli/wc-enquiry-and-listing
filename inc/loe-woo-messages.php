<?php
/**
 * Admin Panel: All Enquiry Messages
 * Plugin:      LOE Woo Messages
 * Author:      Loeion (https://www.loeion.com)
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Only allow admins and editors
if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_posts' ) ) {
    wp_die( esc_html__( 'You do not have permission to view this page.', 'loe-woo-messages' ) );
}

global $wpdb, $current_user;

$user_roles    = $current_user->roles;
$user_role     = trim( array_shift( $user_roles ) );
$is_admin      = ( $user_role === 'administrator' );
$table_name    = $wpdb->prefix . 'loe_woo_messages';
$admin_nonce   = wp_create_nonce( 'loe_woo_msg_nonce' );

// ── Fetch Enquiries ───────────────────────────────────────────────────
if ( $is_admin ) {
    $reviews = $wpdb->get_results(
        "SELECT * FROM $table_name WHERE replyto = '' ORDER BY id DESC",
        OBJECT
    );
} else {
    $reviews = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE replyto = '' AND vendor_id = %d ORDER BY id DESC",
            $current_user->ID
        ),
        OBJECT
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php esc_html_e( 'All Enquiries', 'loe-woo-messages' ); ?></title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>

<div class="wrap">
  <h2><?php esc_html_e( 'LOE Woo Enquiry Messages', 'loe-woo-messages' ); ?></h2>

  <table id="loeAllEnquiries" class="display" width="100%" cellspacing="0">
    <thead>
      <tr>
        <th style="display:none;">#</th>
        <th style="text-align:left;"><?php esc_html_e( 'Product Name', 'loe-woo-messages' ); ?></th>
        <th><?php esc_html_e( 'Vendor', 'loe-woo-messages' ); ?></th>
        <th><?php esc_html_e( 'Name', 'loe-woo-messages' ); ?></th>
        <?php if ( $is_admin ) : ?>
          <th><?php esc_html_e( 'Email', 'loe-woo-messages' ); ?></th>
        <?php endif; ?>
        <th><?php esc_html_e( 'Title', 'loe-woo-messages' ); ?></th>
        <th><?php esc_html_e( 'Date / Time', 'loe-woo-messages' ); ?></th>
        <th><?php esc_html_e( 'Action', 'loe-woo-messages' ); ?></th>
      </tr>
    </thead>

    <tfoot>
      <tr>
        <th style="display:none;">#</th>
        <th style="text-align:left;"><?php esc_html_e( 'Product Name', 'loe-woo-messages' ); ?></th>
        <th><?php esc_html_e( 'Vendor', 'loe-woo-messages' ); ?></th>
        <th><?php esc_html_e( 'Name', 'loe-woo-messages' ); ?></th>
        <?php if ( $is_admin ) : ?>
          <th><?php esc_html_e( 'Email', 'loe-woo-messages' ); ?></th>
        <?php endif; ?>
        <th style="text-align:center;"><?php esc_html_e( 'Title', 'loe-woo-messages' ); ?></th>
        <th><?php esc_html_e( 'Date / Time', 'loe-woo-messages' ); ?></th>
        <th><?php esc_html_e( 'Action', 'loe-woo-messages' ); ?></th>
      </tr>
    </tfoot>

    <tbody>
      <?php
      $i = 1;
      foreach ( $reviews as $value ) :

        $id             = absint( $value->id );
        $user_id        = esc_html( $value->userid );
        $title          = esc_html( $value->title );
        $vendor_id      = absint( $value->vendor_id );
        $product_id     = absint( $value->product_id );
        $message        = esc_html( $value->message );
        $user_email     = esc_html( $value->user_email );
        $last_update    = esc_html( $value->last_update );
        $chat_unique_id = esc_attr( $value->chat_unique_id );
        $product_name   = esc_html( get_the_title( $product_id ) );

        $vendor         = get_user_by( 'ID', $vendor_id );
        $vendor_name    = $vendor ? esc_html( $vendor->display_name ) : '—';
        $vendor_real_id = $vendor ? absint( $vendor->ID ) : 0;
      ?>

      <tr>
        <td style="display:none;"></td>
        <td style="text-align:left;"><?php echo $product_name; ?></td>
        <td><?php echo $vendor_name; ?></td>
        <td><?php echo $user_id; ?></td>
        <?php if ( $is_admin ) : ?>
          <td><?php echo $user_email; ?></td>
        <?php endif; ?>
        <td><?php echo $title; ?></td>
        <td><?php echo $last_update; ?></td>
        <td>
          <span class="loe-actions">
            <!-- View -->
            <span
              class="dashicons dashicons-visibility"
              title="<?php esc_attr_e( 'View', 'loe-woo-messages' ); ?>"
              style="color:#0080FF; cursor:pointer;"
              data-toggle="modal"
              data-target="#loeViewMsg<?php echo $i; ?>"
            ></span>
            &nbsp;|&nbsp;
            <!-- Reply -->
            <span
              class="dashicons dashicons-admin-comments loe-reply-btn"
              title="<?php esc_attr_e( 'Reply', 'loe-woo-messages' ); ?>"
              style="color:#393318; cursor:pointer;"
              data-toggle="modal"
              data-target="#loeViewReply<?php echo $i; ?>"
              id="rep<?php echo $id; ?>"
            ></span>
            &nbsp;|&nbsp;
            <!-- Delete -->
            <span
              class="dashicons dashicons-trash loe-delete-btn"
              title="<?php esc_attr_e( 'Delete', 'loe-woo-messages' ); ?>"
              style="color:#f82800; cursor:pointer;"
              data-id="<?php echo $id; ?>"
            ></span>
          </span>
        </td>
      </tr>

      <!-- ── View Message Modal ───────────────────────────────────── -->
      <div id="loeViewMsg<?php echo $i; ?>" class="modal fade" role="dialog" aria-labelledby="loeViewMsgTitle<?php echo $i; ?>">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="loeViewMsgTitle<?php echo $i; ?>"><?php echo $product_name; ?></h4>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
              <div class="loe-msg-field"><strong><?php esc_html_e( 'Product:', 'loe-woo-messages' ); ?></strong> <?php echo $product_name; ?></div>
              <div class="loe-msg-field"><strong><?php esc_html_e( 'Vendor:', 'loe-woo-messages' ); ?></strong> <?php echo $vendor_name; ?></div>
              <div class="loe-msg-field"><strong><?php esc_html_e( 'User:', 'loe-woo-messages' ); ?></strong> <?php echo $user_id; ?></div>
              <?php if ( $is_admin ) : ?>
                <div class="loe-msg-field"><strong><?php esc_html_e( 'Email:', 'loe-woo-messages' ); ?></strong> <?php echo $user_email; ?></div>
              <?php endif; ?>
              <div class="loe-msg-field"><strong><?php esc_html_e( 'Title:', 'loe-woo-messages' ); ?></strong> <?php echo $title; ?></div>
              <div class="loe-msg-field"><strong><?php esc_html_e( 'Message:', 'loe-woo-messages' ); ?></strong> <?php echo $message; ?></div>
              <div class="loe-msg-field"><strong><?php esc_html_e( 'Date / Time:', 'loe-woo-messages' ); ?></strong> <?php echo $last_update; ?></div>

              <?php
              // Load admin replies for this thread
              $replies = $wpdb->get_results(
                  $wpdb->prepare(
                      "SELECT message FROM $table_name WHERE replyto = %s",
                      $chat_unique_id
                  )
              );
              foreach ( $replies as $reply ) :
              ?>
                <div class="loe-msg-field loe-admin-reply">
                  <strong><?php esc_html_e( 'Admin Reply:', 'loe-woo-messages' ); ?></strong>
                  <?php echo esc_html( $reply->message ); ?>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">
                <?php esc_html_e( 'Close', 'loe-woo-messages' ); ?>
              </button>
            </div>
          </div>
        </div>
      </div>
      <!-- ── /View Message Modal ──────────────────────────────────── -->


      <!-- ── Reply Modal ──────────────────────────────────────────── -->
      <div id="loeViewReply<?php echo $i; ?>" class="modal loe-reply-modal fade" role="dialog" aria-labelledby="loeReplyTitle<?php echo $i; ?>">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="loeReplyTitle<?php echo $i; ?>"><?php esc_html_e( 'Reply to Enquiry', 'loe-woo-messages' ); ?></h4>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">

              <span class="loe-reply-thanks" id="loeThanks_<?php echo $id; ?>" style="display:none;"></span>
              <div class="loe-reply-error" id="loeReplyError_<?php echo $id; ?>"></div>

              <?php if ( $is_admin ) : ?>
                <input
                  type="email"
                  id="loeToEmail_<?php echo $id; ?>"
                  placeholder="<?php esc_attr_e( 'To Email', 'loe-woo-messages' ); ?>"
                  class="regular-text"
                  value="<?php echo $user_email; ?>"
                >
              <?php endif; ?>

              <textarea
                id="loeReplyMessage_<?php echo $id; ?>"
                placeholder="<?php esc_attr_e( 'Reply Message', 'loe-woo-messages' ); ?>"
                class="regular-text"
                rows="5"
              ></textarea>

              <!-- Hidden fields -->
              <input type="hidden" id="loeReplyTitle_<?php echo $id; ?>"      value="<?php echo $title; ?>">
              <input type="hidden" id="loeReplyUserName_<?php echo $id; ?>"   value="<?php echo $user_id; ?>">
              <input type="hidden" id="loeReplyVendor_<?php echo $id; ?>"     value="<?php echo $vendor_real_id; ?>">
              <input type="hidden" id="loeReplyProductId_<?php echo $id; ?>"  value="<?php echo $product_id; ?>">
              <input type="hidden" id="loeChatUniqueId_<?php echo $id; ?>"    value="<?php echo $chat_unique_id; ?>">
              <input type="hidden" id="loeReplyNonce_<?php echo $id; ?>"      value="<?php echo esc_attr( $admin_nonce ); ?>">

              <input
                type="button"
                class="button button-primary loe-send-reply-btn"
                data-id="<?php echo $id; ?>"
                value="<?php esc_attr_e( 'Send Reply', 'loe-woo-messages' ); ?>"
              >

            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">
                <?php esc_html_e( 'Close', 'loe-woo-messages' ); ?>
              </button>
            </div>
          </div>
        </div>
      </div>
      <!-- ── /Reply Modal ─────────────────────────────────────────── -->

      <?php
        $i++;
      endforeach;
      ?>
    </tbody>
  </table>
</div><!-- /.wrap -->


<script type="text/javascript">
(function($) {
  'use strict';

  var ajaxurl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';

  // ── DataTable Init ────────────────────────────────────────────────
  $(document).ready(function() {
    $('#loeAllEnquiries').DataTable({
      pagingType: 'full_numbers',
      order: []
    });
  });

  // ── Delete Enquiry Thread ─────────────────────────────────────────
  $(document).on('click', '.loe-delete-btn', function() {
    var delId  = $(this).data('id');
    var nonce  = '<?php echo esc_js( $admin_nonce ); ?>';
    var $row   = $(this).closest('tr');

    if ( ! confirm('<?php esc_html_e( 'Are you sure you want to delete this enquiry? This cannot be undone.', 'loe-woo-messages' ); ?>') ) {
      return;
    }

    $.post(
      ajaxurl,
      {
        action: 'loe_woo_message_delete',
        nonce:  nonce,
        delId:  delId
      },
      function( response ) {
        if ( response.success ) {
          window.location.reload();
        } else {
          alert( response.data || '<?php esc_html_e( 'Delete failed. Please try again.', 'loe-woo-messages' ); ?>' );
        }
      }
    ).fail(function() {
      alert('<?php esc_html_e( 'Connection error. Please try again.', 'loe-woo-messages' ); ?>');
    });
  });

  // ── Send Reply ────────────────────────────────────────────────────
  $(document).on('click', '.loe-send-reply-btn', function() {
    var id             = $(this).data('id');
    var nonce          = $('#loeReplyNonce_'    + id).val();
    var toEmail        = $('#loeToEmail_'       + id).val() || '';
    var chatUniqueId   = $('#loeChatUniqueId_'  + id).val();
    var replyMessage   = $('#loeReplyMessage_'  + id).val().trim();
    var replyTitle     = $('#loeReplyTitle_'    + id).val();
    var replyUserName  = $('#loeReplyUserName_' + id).val();
    var replyVendor    = $('#loeReplyVendor_'   + id).val();
    var replyProductId = $('#loeReplyProductId_'+ id).val();
    var $thanks        = $('#loeThanks_'        + id);
    var $error         = $('#loeReplyError_'    + id);

    $error.html('');

    if ( ! replyMessage ) {
      $error.html('<?php esc_html_e( 'Please enter a reply message.', 'loe-woo-messages' ); ?>');
      return;
    }

    $.post(
      ajaxurl,
      {
        action:         'loe_woo_send_reply',
        nonce:          nonce,
        toEmail:        toEmail,
        chatUniqueId:   chatUniqueId,
        replyMessage:   replyMessage,
        replyTitle:     replyTitle,
        replyUserName:  replyUserName,
        replyVendor:    replyVendor,
        replyProductId: replyProductId
      },
      function( response ) {
        if ( response.success ) {
          $thanks
            .text('<?php esc_html_e( 'Reply sent successfully.', 'loe-woo-messages' ); ?>')
            .show();
          $('#loeReplyMessage_' + id).val('');

          setTimeout(function() {
            $thanks.hide();
            $('.loe-reply-modal').modal('hide');
          }, 1500 );

        } else {
          $error.html(
            response.data || '<?php esc_html_e( 'Failed to send reply. Please try again.', 'loe-woo-messages' ); ?>'
          );
        }
      }
    ).fail(function() {
      $error.html('<?php esc_html_e( 'Connection error. Please try again.', 'loe-woo-messages' ); ?>');
    });
  });

})(jQuery);
</script>


<style type="text/css">

/* ── Page Header ───────────────────────────────── */
.wrap h2 {
  margin-bottom: 20px;
  font-size: 22px;
}

/* ── Action Icons ──────────────────────────────── */
.loe-actions .dashicons {
  font-size: 18px;
  vertical-align: middle;
}
.loe-actions .dashicons:hover {
  opacity: 0.75;
}

/* ── View Message Fields ───────────────────────── */
.loe-msg-field {
  padding: 6px 0;
  border-bottom: 1px solid #f0f0f0;
  font-size: 13px;
  line-height: 1.5;
}
.loe-msg-field:last-child {
  border-bottom: none;
}
.loe-admin-reply {
  background: #f0f7ff;
  padding: 8px 10px;
  border-radius: 3px;
  margin-top: 6px;
  border-bottom: none;
}

/* ── Reply Modal ───────────────────────────────── */
.loe-reply-modal .regular-text {
  width: 100%;
  margin-bottom: 10px;
  box-sizing: border-box;
}
.loe-reply-modal textarea.regular-text {
  height: 100px;
  resize: vertical;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 3px;
  font-size: 13px;
}
.loe-reply-modal input[type="email"].regular-text {
  height: 36px;
  padding: 0 10px;
  border: 1px solid #ccc;
  border-radius: 3px;
  font-size: 13px;
}

/* ── Reply Feedback ────────────────────────────── */
.loe-reply-thanks {
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
.loe-reply-error {
  color: #c0392b;
  font-size: 12px;
  margin-bottom: 8px;
  min-height: 16px;
}

/* ── Send Reply Button ─────────────────────────── */
.loe-send-reply-btn {
  float: right;
  margin-top: 4px;
}

</style>

</body>
</html>

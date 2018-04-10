<!DOCTYPE html>
<html lang="en">
<head>
  <title>All Enquiries</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>

<?php 

	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
	global $wpdb;
	global $wp_query;
	global $current_user;
	$user_roles = $current_user->roles;
	//echo $current_user->ID;
	/* echo '<pre>';
	print_r($current_user);
	echo '</pre>'; */
	$user_role = array_shift($user_roles);
	$loggedin_user = trim($user_role);
?>

<div class="wrap">
    <h2>WBC Woo Enquiry Messages</h2>


<?php

	$table_name = $wpdb->prefix . 'wbc_woo_messages';
	if($loggedin_user == 'administrator'){
	$querystr 	= "SELECT * FROM $table_name where replyto= '' ORDER BY id DESC ";
	}
	else{
		$querystr 	= "SELECT * FROM $table_name where replyto= '' and vendor_id = ".$current_user->ID." ORDER BY id DESC ";
	}
	//$querystr 	= "SELECT * FROM $table_name ORDER BY id DESC";
	$reviews 	= $wpdb->get_results($querystr, OBJECT);
?>


<table id="allEnquiryMessages" class="display" width="100%" cellspacing="0">
        <thead>
            <tr>
              	<th style="display: none;">#</th>
               	<!-- <th>Id</th> -->
                <th style="text-align:left">Product Name</th>
                <th>Vendor</th>
                <th>Name</th>
					<?php 
					if($loggedin_user == 'administrator'){
						echo "<th>Email</th>";
					}
					?>
				
				<th>Title</th>							
				<th>Date Time</th>				
				<th>Action</th>
            </tr>
        </thead>
        <tfoot>
            <tr>               
	          <th style="display: none;">#</th>
               	<!-- <th>Id</th> -->
                <th style="text-align:left">Product Name</th>
                <th>Vendor</th>
                <th>Name</th>	              
				<?php 
					if($loggedin_user == 'administrator'){
						echo "<th>Email</th>";
					}
					?>		
				<th style="text-align:center">Title</th>
				<th>Date Time</th>
				<th>Action</th>
            </tr>
        </tfoot>

        <tbody>
            <?php                

            	$i = 1;
				foreach ( $reviews as $key => $value ) {			
				
					$id 			= $value->id;
					$user_id 		= $value->userid;
					$title 			= $value->title;
					$vendor_id 		= $value->vendor_id;
					$product_id		= $value->product_id;
					$message 		= $value->message;
					$user_email 	= $value->user_email;					
					$last_update 	= $value->last_update;
					$status 		= $value->status;
					$chat_unique_id = $value->chat_unique_id;
				
				
					// Get user object
					$recent_author = get_user_by( 'ID', $vendor_id );
					// Get user display name
					$author_display_name = $recent_author->display_name;


					
					if($status == 0){

						$classDelivery = "";
						$statusCheck = "";
						$text = '<span style="color:#4BB543" class="dashicons dashicons-yes"  title="Approve"></span>';
						$datastatus = 1;

					} else if($status == 1) {

						$classDelivery = "Delivery-Active";
						$statusCheck = "checked";
						$text = '<span style="color:#f82800" class="dashicons dashicons-no-alt" title="Unapprove"></span>';
						$datastatus = 0;

					}
				

					echo "<tr>";
					echo '<td style="display:none;"></td>';				
				    echo '<td style="text-align:left">' . get_the_title( $product_id )  . '</td>';
				    echo '<td>' . $author_display_name  . '</td>';
				    echo '<td>' . $user_id  . '</td>';
					
					if($loggedin_user == 'administrator'){
						echo '<td>' . $user_email  . '</td>';
					}
					
				    echo '<td>' . $title  . '</td>';
				    echo '<td>' . $last_update  . '</td>';				   
				  
				    echo '<td><span class="changeStatus">
					   <span style="color:#0080FF; cursor:pointer" class="dashicons dashicons-visibility" title="View" data-toggle="modal" data-target="#WBC_viewMessage'.$i.'"></span> | <a  title="Reply Message" href="javascript:;" class="replyRecord" data-toggle="modal" data-target="#WBC_viewReply'.$i.'" id="rep'.$id.'"><span class="dashicons dashicons-admin-comments" style="color:#393318"></span></a> | <a  title="Delete Message"  href="javascript:;" class="deleterecord" id="'.$id.'"><span class="dashicons dashicons-trash" style="color:#f82800"></span></a> 
					</td>';
					?>


<!-- Code to view full message STARTS -->
<div id="WBC_viewMessage<?php echo $i;?>" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
      	<?php echo get_the_title( $product_id ); ?>
        <button type="button" class="close" data-dismiss="modal">&times;</button>      
      </div>
      <div class="modal-body">


      	<?php 
				
       			echo '<div class="wbc-rev-value"> Product: ' . get_the_title( $product_id )  . '</div>';
       			echo '<div class="wbc-rev-value"> Vendor Name: ' . $author_display_name  . '</div>';
       			echo '<div class="wbc-rev-value"> User Name: ' . $user_id  . '</div>';
				if($loggedin_user == 'administrator'){
       			echo '<div class="wbc-rev-value"> User Email: ' . $user_email  . '</div>';
				}
       			echo '<div class="wbc-rev-value"> Title: ' . $title  . '</div>';       			
				echo '<div class="wbc-rev-value"> User Message: ' . $message  . '</div>';				
				echo '<div class="wbc-rev-value"> Date Time: ' . $last_update  . '</div>';
				$table_name = $wpdb->prefix . 'wbc_woo_messages';
				$replystr 	= "SELECT message FROM $table_name where replyto = ".$chat_unique_id."";
				$replymsg = $wpdb->get_results($replystr);
				foreach ( $replymsg as $keys => $values ) {
					echo '<div class="wbc-rev-value"> Admin Reply : ' . $values->message  . '</div>';	
				}				
			  
			   $upload = wp_upload_dir();			  
			   if(!empty($images)){
				   echo  '<p>Photos</p>';
				     foreach($images as $img) 
					 
					 {
						 echo '<img style=" width: 150px; height: 150px;" src="'.$upload['baseurl'].'/wbcreviews/'.$img.'"/>  ';
					 }
			   }
			   ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<!-- Code to view full messages ENDS -->


<!-- Code to view full reply STARTS -->
<div id="WBC_viewReply<?php echo $i;?>" class="modal wbc_viewreply fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
      	
        <button type="button" class="close" data-dismiss="modal">&times;</button>      
      </div>
      <div class="modal-body">
      	<h3>Add Reply for Enquiry</h3>
		
      	<form>
			<span id="Thankyoumessage"></span>
			<!--<input type="hidden" name="reply_id" id="replyId_<?php echo $id; ?>" class="regular-text" value="<?php echo $id; ?>">-->
			<?php 
			if($loggedin_user == 'administrator'){ 
			?>
			<input type="text" name="to_email" id="toEmail_<?php echo $id; ?>" placeholder="To email" class="regular-text" value="<?php echo $user_email; ?>">
			<?php }?>
      		<textarea name="reply_message" id="replyMessage_<?php echo $id; ?>" placeholder="Reply Message" class="regular-text"></textarea>
			<input type="hidden" name="reply_title" id="replyTitle_<?php echo $id; ?>" class="regular-text" value="<?php echo $title; ?>">
			<input type="hidden" name="reply_user_name" id="replyUserName_<?php echo $id; ?>" class="regular-text" value="<?php echo $user_id; ?>">
			<input type="hidden" name="reply_vendor" id="replyVendor_<?php echo $id; ?>" placeholder="reply_vendor" value="<?php echo $recent_author->ID; ?>">
			<input type="hidden" name="reply_product_id" id="replyProductId_<?php echo $id; ?>" placeholder="reply_product_id" value="<?php echo $product_id;?>">
			<input type="hidden" id="chatUniqueId_<?php echo $id; ?>" value="<?php echo $chat_unique_id;?>">
			<input type="button" name="send-reply" class="send-reply" id="formId_<?php echo $id; ?>" value="Send Reply">
      	</form>
		
	
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<!-- Code to view full review ENDS -->

<?php 
					echo "</tr>";
					$i++;

				}
			?></tbody>
    </table>
<script type="text/javascript">
    	$(document).ready(function() {
		    $('#allEnquiryMessages').DataTable( {
		    	"pagingType": "full_numbers"
		    });


		    // Delete the review 
		    $('.deleterecord').click(function(){
		    	var delId = this.id;
		    	var txt;
			    var r = confirm("Are you sure you want to delete? This action cannot be undone");
			    if (r == true) {
			        jQuery.post(
						ajaxurl, {
							'action': 'WBC_WooMessageDelete',
							'data':   'foobarid',
							'delId': this.id							
						}, 
						function(response){
							// return false;
							window.location.reload();
						});
			    } else {

			    	return false;
			        //txt = "You pressed Cancel!";
			    }			   
		    });
			
		$('.send-reply').click(function(){
			var val = this.id;
			var Id = val.replace("formId_", "");
			
            var ajaxurl       		= '<?php echo admin_url( 'admin-ajax.php' );?>';
			//var repId               = $('#replyId_'+Id).val();
            var toEmail     		= $('#toEmail_'+Id).val();
            var chatUniqueId     	= $('#chatUniqueId_'+Id).val();
            var replyMessage     	= $('#replyMessage_'+Id).val();
            var replyTitle     		= $('#replyTitle_'+Id).val();
            var replyUserName     	= $('#replyUserName_'+Id).val();
			var replyVendor        = $('#replyVendor_'+Id).val();
			var replyProductId   = $('#replyProductId_'+Id).val();
            // Form Values ENDS
			
             jQuery.post(
              ajaxurl, {
               'action': 'wbcWooSendReply',              
                'type': 'post',
                'dataType': "JSON",
				//'repId': repId,                
                'toEmail': toEmail,
                'replyMessage': replyMessage,
                'replyTitle': replyTitle,
                'chatUniqueId': chatUniqueId,
                'replyUserName': replyUserName,
                'replyVendor': replyVendor,
                'replyProductId': replyProductId,
				
              },
              function(response){
                console.log(response);
				jQuery('#Thankyoumessage').show();
				jQuery('#Thankyoumessage').text("Thanks for your response. Your reply has been sent to user.");
				setTimeout(function(){
                  jQuery('#Thankyoumessage').hide();
                  jQuery('.wbc_viewreply').hide();
                },1500);
				});
		});
			
		});
</script>
<style type="text/css">
#Thankyoumessage{
  display: none;
}

#Thankyoumessage {
  background: green;
  padding: 8px 10px;
  line-height: 15px;
  color: #fff;
  font-size: 12px;
  float: left;
  margin: 0 0 15px;
}
</style>
<?php
/**
*Plugin Name: WBC Woo Messages 
*Author: Webchefz.com
*Author URI: https://www.webchefz.com
*Description: WBC  Woo Messages intraction with user and vendors
*Plugin URI:  https://www.webchefz.com/wbc-advanced-review
*Version: 0.1
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
require_once(ABSPATH.'wp-admin/includes/plugin.php'); 
$plugin_data = get_plugin_data( __FILE__ );




// Admin hooks

function WBC_WooMessageAdminAction() {

   add_menu_page('Woo Messages', 'Woo Messages', 'read', __FILE__, 'WBC_wooMessages', "dashicons-chart-pie",7);

}
 
add_action('admin_menu', 'WBC_WooMessageAdminAction');

function WBC_wooMessages() { 
   include 'inc/wbc-woo-messages.php';

  // echo "Welcome to asset pie chart";
}


 
// function to create the Table / Options / Defaults         
function WBC_WooMessageInstall() {
 global $wpdb;
 
  // create the ECPT metabox database table

    $table_name = $wpdb->prefix . 'wbc_woo_messages';
  if($wpdb->get_var("show tables like $table_name") != $table_name) 
  {

  $sql = "CREATE TABLE $table_name (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` varchar(56) NOT NULL,
  `userid` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` longtext NOT NULL,
  `product_id` varchar(11) NOT NULL,
  `status` varchar(255) NOT NULL,
  `chat_unique_id` varchar(255) NOT NULL,
  `replyto` varchar(255) NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";
 
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }
  
   
 
}
// run the install scripts upon plugin activation
register_activation_hook(__FILE__,'WBC_WooMessageInstall');


/*
 * Content below "Add to cart" Button.
 */
function WBC_WooMessageGeneration(){
  include('templates/wbc-WooMessage.php');
}
// add_shortcode( 'wbc-woo-enquiry-message', 'WBC_WooMessageGeneration' );

add_action( 'woocommerce_after_add_to_cart_button', 'WBC_WooMessageGeneration' );





    add_action( 'wp_ajax_nopriv_prefix_ajax_WBC_SendWooMessageData', 'prefix_ajax_WBC_SendWooMessageData' );
    add_action( 'wp_ajax_prefix_ajax_WBC_SendWooMessageData', 'prefix_ajax_WBC_SendWooMessageData' );
    function prefix_ajax_WBC_SendWooMessageData($requestedData) {

        //header('Content-type: application/json');

        $user_ID    = $_REQUEST['userId'];
        $title      = $_REQUEST['title'];
        $vendor_id  = $_REQUEST['vendor_id'];
        $product_id = $_REQUEST['product_id'];
        $message    = $_REQUEST['message'];
        $user_email = $_REQUEST['user_email'];
		    $chat_unique_id = $_REQUEST['chat_unique_id'];

        $message    = strip_tags($message);
        
		//Sending email to user with unique chat id//
		
		$to = $user_email;
    $subject = "Discussion Unique Id for Enquiry";
    $msg = "We have received your enquiry. ".$message."<br>Here is the Unique discussion Id : ".$chat_unique_id." to start conversation with us. <a href='".get_permalink( 4795 )."' target='_blank'>Start conversation</a>";
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
    $headers .= 'From: TravelQuest <no-reply@aimmath.com>';
    $sent_message = wp_mail( $to, $subject, $msg, $headers);



    // Admin Email 

    $recent_author = get_user_by( 'ID', $vendor_id );
    // Get user display name
    $author_display_name = $recent_author->display_name;

    $to           =  get_administrator_email();
    $subject      = 'Enquiry Request';
    $msg = "We have received enquiry. <br> For vendor: ".$author_display_name." And Product: ".get_the_title($product_id)."<br> Enquiry By :  ".$user_ID ."<br> Email: ".$user_email. "<br> Enquiry Title : ".$title." <br> Enquiry Message: ".$message." <br> Here is the Unique discussion Id : ".$chat_unique_id." to start conversation with us. <a href='".get_permalink( 4795 )."' target='_blank'>Start conversation</a>";
    $headers      = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
    $headers .= 'From: TravelQuest <no-reply@aimmath.com>';    
    $sent_message = wp_mail( $to, $subject, $msg, $headers);



  
       if ( $sent_message ) {  
        echo 'successfully sent';
      } else {
        echo "failed!";
      }	

        global $wpdb;
        $table_name = $wpdb->prefix . 'wbc_woo_messages';
        $InsertQuery = $wpdb->insert( $table_name, array( 
            'userid' => $user_ID,
            'title' => $title,
            'vendor_id' =>  $vendor_id,
            'product_id' => $product_id,
            'message' => $message,
             'chat_unique_id' => $chat_unique_id,
            'user_email' => $user_email,
            'user_type' => 'user',          
            'last_update' => date('Y-m-d H:i:s')
        ) );

        if($InsertQuery){
        
            $data = "Message is inserted";
        } else {
            $data = "Message is not inserted";
        }
        echo json_encode($data, true);
        die();
    }


    // Delete Specific messages
      add_action( 'wp_ajax_WBC_WooMessageDelete', 'prefix_ajax_WBC_WooMessageDelete' );
    function prefix_ajax_WBC_WooMessageDelete() {
      global $wpdb;
        $table_name = $wpdb->prefix . 'wbc_woo_messages';

        $querystr   = "SELECT chat_unique_id FROM $table_name WHERE id = '".$delId."'";    
        $resultChatIds  = $wpdb->get_results($querystr, OBJECT);
        foreach ( $resultChatIds as $key => $value ) { 
          $chat_unique_id   = $value->chat_unique_id;
        }

    
      $delId = $_REQUEST['delId'];

      if(!empty($delId)){
        $querystr   = "DELETE FROM $table_name WHERE id = '".$delId."' OR chat_unique_id = '".$chat_unique_id."'";
        $pageposts = $wpdb->get_results($querystr, OBJECT);
        exit();        
      }    
     
          exit();
    }
  
  add_action( 'wp_ajax_nopriv_wbcWooSendReply', 'wbcWooSendReply' );
    add_action( 'wp_ajax_wbcWooSendReply', 'wbcWooSendReply' );
  function wbcWooSendReply(){
    
    $chatUniqueId = $_REQUEST['chatUniqueId'];
    global $wpdb;
    $table_name = $wpdb->prefix . 'wbc_woo_messages';
    $querystr   = "SELECT user_email FROM $table_name where chat_unique_id = ".$chatUniqueId;
	//echo "SELECT user_email FROM $table_name where chat_unique_id = ".$chatUniqueId;
    
    $result  = $wpdb->get_results($querystr, OBJECT);
	foreach ( $result as $key => $value ) {	
			$user_email   = $value->user_email;
        }
    
		$to = $user_email;
        $subject = 'Test Enquiry';
        $msg = $_REQUEST['replyMessage'];
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
    $headers .= 'From: TravelQuest <no-reply@aimmath.com>';     
		$sent_message = wp_mail( $to, $subject, $msg, $headers); 
  
       if ( $sent_message ) {  
        echo 'successfully sent';
      } else {
        echo "failed!";
      } 
      
  global $wpdb;
    $table_name = $wpdb->prefix . 'wbc_woo_messages';
    
      //$repId        = $_REQUEST['repId'];
      $chatUniqueId  = $_REQUEST['chatUniqueId'];	  
      $toEmail        = $user_email;
      $replyMessage   = $_REQUEST['replyMessage'];
      $replyTitle       = $_REQUEST['replyTitle'];
      $replyUserName    = $_REQUEST['replyUserName'];
      $replyVendor      = $_REQUEST['replyVendor'];
      $replyProductId   = $_REQUEST['replyProductId'];
    

      if(!empty($chatUniqueId)){
       //$wpdb->query("UPDATE $table_name SET replyto='".$repId."' WHERE id = '".$repId."'");   
     
     $wpdb->insert( $table_name, array( 
            'userid' => $replyUserName,
            'title' => $replyTitle,
            'vendor_id' =>  $replyVendor,
            'product_id' => $replyProductId,
            'message' => $replyMessage,
            'replyto' => $chatUniqueId,
            'user_email' => $toEmail,
            'user_type' => 'admin',             
            'last_update' => date('Y-m-d H:i:s')
        ) );
      }
    
  }

  // Front end users enquiry threads

    function WBC_frontendDiscussion(){
      include('templates/wbc-WooDiscussion.php');
    }

    add_shortcode('wbc-frontend-discussions', 'WBC_frontendDiscussion');


    add_action( 'wp_ajax_nopriv_prefix_ajax_WBC_checkEnquiry', 'prefix_ajax_WBC_checkEnquiry' );
    add_action( 'wp_ajax_prefix_ajax_WBC_checkEnquiry', 'prefix_ajax_WBC_checkEnquiry' );
    function prefix_ajax_WBC_checkEnquiry($requestedData) {

        header('Content-type: application/json');
        $enquiryId    = $_REQUEST['enquiryId'];

        global $wpdb;
        $table_name = $wpdb->prefix . 'wbc_woo_messages';

        $querystr   = "SELECT * FROM $table_name WHERE chat_unique_id = $enquiryId UNION SELECT * FROM $table_name WHERE replyto = $enquiryId";
        $data  = $wpdb->get_results($querystr, OBJECT);
        echo json_encode($data, true);
        die();
    }


    add_action( 'wp_ajax_nopriv_prefix_ajax_wbcUsersReply', 'prefix_ajax_wbcUsersReply' );
    add_action( 'wp_ajax_prefix_ajax_wbcUsersReply', 'prefix_ajax_wbcUsersReply' );


      function prefix_ajax_wbcUsersReply(){

        $enquiryId      = $_REQUEST['thread_id'];
        $replyMessage   = $_REQUEST['message'];

        global $wpdb;
        $table_name = $wpdb->prefix . 'wbc_woo_messages';

        $querystr       = "SELECT * FROM $table_name WHERE chat_unique_id = $enquiryId";
        $enquiryDetail  = $wpdb->get_results($querystr, OBJECT);

        if(!empty($enquiryDetail)){

          foreach ( $enquiryDetail as $key => $value ) {
            $user_id      = $value->userid;
            $title        = $value->title;
            $vendor_id    = $value->vendor_id;
            $product_id   = $value->product_id;
            $user_email   = $value->user_email;
          }            

          $wpdb->insert( $table_name, array(
            'userid' => $user_id,
            'title' => $title,
            'vendor_id' =>  $vendor_id,
            'product_id' => $product_id,
            'message' => $replyMessage,
            'replyto' => $enquiryId,
            'user_email' => $user_email,
            'user_type' => 'user',
            'last_update' => date('Y-m-d H:i:s')
          ));

        
          $user         = get_user_by( 'id', $vendor_id );
          $vendoremail  = $user->email;

          

          // Vendor Email

          $Vendor_to           = $vendoremail;
          $Vendor_subject      = 'Reply From '.$user_id;
          $Vendor_msg          = $replyMessage;
          $Vendor_headers      = "MIME-Version: 1.0" . "\r\n";
          $Vendor_headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
          $Vendor_headers .= 'From: TravelQuest <no-reply@aimmath.com>';    
          $Vendor_sent_message = wp_mail( $Vendor_to, $Vendor_subject, $Vendor_msg, $Vendor_headers);

          // Administrator Email

          $recent_author = get_user_by( 'ID', $vendor_id );
          // Get user display name
          $author_display_name = $recent_author->display_name;

          $to           =  get_administrator_email();
          $subject      = 'Enquiry Thread';
          $msg = "We have received enquiry. <br> For vendor: ".$author_display_name." And Product: ".get_the_title($product_id)."<br> Enquiry By :  ".$user_id ."<br> Email: ".$user_email. "<br> Enquiry Title : ".$title." <br> Enquiry Message: ".$replyMessage." <br> Here is the Unique discussion Id : ".$enquiryId." to start conversation with us. <a href='".get_permalink( 4795 )."' target='_blank'>Start conversation</a>";
          $headers      = "MIME-Version: 1.0" . "\r\n";
          $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
          $headers .= 'From: TravelQuest <no-reply@aimmath.com>';    
          $sent_message = wp_mail( $to, $subject, $msg, $headers);



        }
        die();    
      }

    add_action( 'wp_ajax_nopriv_prefix_ajax_wbcGetThreadMain', 'prefix_ajax_wbcGetThreadMain' );
    add_action( 'wp_ajax_prefix_ajax_wbcGetThreadMain', 'prefix_ajax_wbcGetThreadMain' );


      function prefix_ajax_wbcGetThreadMain() {
         header('Content-type: application/json');
        $enquiryId      = $_REQUEST['enquiryId'];
        global $wpdb;
        $table_name = $wpdb->prefix . 'wbc_woo_messages';

        $querystr       = "SELECT * FROM $table_name WHERE chat_unique_id = $enquiryId";
		    // echo "SELECT * FROM $table_name WHERE chat_unique_id = $enquiryId";
        $enquiryDetail  = $wpdb->get_results($querystr, OBJECT);

        if(!empty($enquiryDetail)){

          foreach ( $enquiryDetail as $key => $value ) {
          
            $title        = $value->title;
            $vendor_id    = $value->vendor_id;
            $productName  = get_the_title( $value->product_id );

            // Get user object
            $recent_author = get_user_by( 'ID', $vendor_id );
            // Get user display name
            $author_display_name = $recent_author->display_name;
            
          }

        $data = array( 
          'product_title' => $productName,
          'vendor_name' => $author_display_name,
          'enquiry_title' => $title 
        );
        echo json_encode($data, true);
        die();
      }
    }

    function get_administrator_email(){
      $blogusers = get_users('role=Administrator');
      //print_r($blogusers);
      foreach ($blogusers as $user) {
        return $user->user_email;
      }  
  }
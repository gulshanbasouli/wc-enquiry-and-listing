<?php 
global $product;
global $post;
$author_id = $post->post_author;
?>

<input type="button" onclick="show('wbcEnquiry')" class="wbc-send-enquiry" value="Make an enquiry">
<div id="wbcEnquiry">
  <h1>Make an enquiry</h1>
  <form>
    <h2 id='result'></h2>
    <div id="errorMessage"></div>
    <input type="text" name="user_id" id="user_id" placeholder="Name">
    <input type="text" name="user_email" id="user_email" placeholder="Enter email">
    <input type="text" name="title" id="title" placeholder="Enter title">
    <textarea name="message" id="message" placeholder="Enter message"></textarea>
    <input type="hidden" name="vendor_id" id="vendor_id" placeholder="vendor_id" value="<?php echo $author_id; ?>">
    <input type="hidden" name="product_id" id="product_id" placeholder="product_id" value="<?php echo $product->get_id(); ?>">
	<input type="hidden" name="chat_unique_id" id="chat_unique_id" placeholder="chat_unique_id" value="<?php echo rand(); ?>">
    <input type="button" onclick="wbcWooSendEnquiryData()" name="submitEnquriy" class="button-wbc-enquiry" value="Make an enquiry">
  </form>

  <a class="cancle" href="javascript:;" onclick="hide('wbcEnquiry')"><i class="fa fa-times"></i></a>
</div>
  
<!-- <script src="//code.jquery.com/jquery-1.11.1.min.js"></script> -->
    <script type="text/javascript">


      // function wbcWooSendEnquiryData() {
  
  
  
       
      // }

      

      function wbcWooSendEnquiryData() {


        var user_id       = jQuery('#user_id').val();
        var title         = jQuery('#title').val();
        var vendor_id     = jQuery('#vendor_id').val();
        var product_id    = jQuery('#product_id').val();
        var message       = jQuery('#message').val();
        var user_email    = jQuery('#user_email').val(); 
		
        if(user_id == "") {
          jQuery('#user_id').focus();
          jQuery('#errorMessage').html("Name is required");
          return false;
        }
        if(user_email == "") {
          jQuery('#user_email').focus();
          jQuery('#errorMessage').html("Email is required");
          return false;
        }  
      
        if (!ValidateEmail(jQuery("#user_email").val())) {
           jQuery('#errorMessage').html("Invalid email address.");
           return false;
        }
                
        if(title == "") {
          jQuery('#title').focus();
          jQuery('#errorMessage').html("Title is required");
          return false;
        }
        
        if(message == "") {
          jQuery('#message').focus();
          jQuery('#errorMessage').html("Please enter message");
          return false;
        }
        jQuery('#errorMessage').html("");
          
          var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' );?>';        
          var user_id       = jQuery('#user_id').val();
          var title         = jQuery('#title').val();
          var vendor_id     = jQuery('#vendor_id').val();
          var product_id    = jQuery('#product_id').val();
          var message       = jQuery('#message').val();
          var user_email    = jQuery('#user_email').val();
			var chat_unique_id  	= $('#chat_unique_id').val();
			
            jQuery.post(
              ajaxurl, {
                'action': 'prefix_ajax_WBC_SendWooMessageData',              
                'type': 'post',
                'dataType': "JSON",
                'userId': user_id,
                'title': title,
                'vendor_id': vendor_id,
                'product_id': product_id,
				'chat_unique_id': chat_unique_id,
                'message': message,
                'user_email': user_email,            
              },
            function(response) {              
                // Getting response from the form submission percentage
           
            });
        }

      function ValidateEmail(user_email) {
        var expr = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
        return expr.test(user_email);
       
      }


    </script>
<script type="text/javascript">
function show(target) {
    document.getElementById(target).style.display = 'block';
}

function hide(target) {
    document.getElementById(target).style.display = 'none';
}
</script> 

<style type="text/css">

#wbcEnquiry h1 {
  font-size: 20px;
  margin: 25px 0 0;
}
#wbcEnquiry #result {
  margin: 15px 0;
}
#wbcEnquiry textarea
{

}
#errorMessage {
  color: #f00;
  margin-bottom: 10px;
}
#wbcEnquiry .cancle {
  position: absolute;
  top: 4px;
  right: 6px;
  color: #fff;
}
#wbcEnquiry .cancle i {
  color: #fff;
  font-size: 20px;
}

#wbcEnquiry textarea {
  width: 90%;
  margin: 0 5% 5px;
  float: left;
  border: 1px solid #ccc;
  background: transparent;
  color: #fff;
  padding: 10px;
  resize: none;
}
#wbcEnquiry textarea::placeholder {
  opacity: 1;
}

#wbcEnquiry {
  transform: translate(-50%,-50%);
  -webkit-transform: translate(-50%,-50%);
  -ms-transform: translate(-50%,-50%);
  overflow: hidden;
  left: 50%;
  top: 50%;
  position: absolute;
  opacity: 1;
  z-index: 200;
  display: none;
  background: #096685;
}
#wbcEnquiry .button-wbc-enquiry {
  background: #f39c00;
  text-align: center !important;
  border: none;
  height: 30px;
  font-weight: 700;
}
.trip-info h1 {
  margin-bottom: 35px;
  font-size: 31px;
  margin-top: 20px;
}
#wbcEnquiry input {
  width: 90% !important;
  margin: 0 5% 10px !important;
  float: left;
  border: 1px solid #ccc;
  color: #fff;
  height: 25px;
  font-size: 12px;
  padding: 0 10px;
}
#wbcEnquiry textarea
{
  width: 90%;
  margin: 0 5% 5px;
  float: left;
  border:1px solid #ccc; 
}

a {
  color: #5874BF;
  text-decoration: none;
}
a:hover {
  color: #112763;
}

.wbc-send-enquiry {
  background-color: #ff7550;
  border: none;
  font-size: 30px;
  line-height: 38px;
  font-weight: 300;
  letter-spacing: 1px;
  color: #fff;
  padding: 10px;
  -webkit-transition: all .3s linear;
  transition: all .3s linear;
  outline: none;
  text-transform: uppercase;
}
.product-tour-booking-form input.wbc-send-enquiry {
  margin: 10px 0 0 !important;
  text-align: center !important;
}
</style>
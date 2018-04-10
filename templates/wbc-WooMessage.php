<?php 
global $product;
global $post;
$author_id = $post->post_author;
?>

<input type="button" onclick="show('wbcEnquiry')" class="wbc-send-enquiry" value="Ask More info now">
<div id="wbcEnquiry">
  <div class="wbcEnquiryInnerArea">
  <h1>Ask More info now</h1>
  <form>
    <h2 id='result'></h2>

    <span id="Thankyoumessage"></span>
    <div id="errorMessage"></div>
    <input type="text" name="user_id" id="user_id" placeholder="Name">
    <input type="text" name="user_email" id="user_email" placeholder="Enter email">
    <input type="text" name="title" id="title" placeholder="Enter title">
    <!-- <textarea name="message" id="message" placeholder="Enter message"></textarea> -->

    <div class="editabel" id="message" contenteditable=""></div><span class="alertMessage">This is not a valid message</span>


    <input type="hidden" name="vendor_id" id="vendor_id" placeholder="vendor_id" value="<?php echo $author_id; ?>">
    <input type="hidden" name="product_id" id="product_id" placeholder="product_id" value="<?php echo $product->get_id(); ?>">
	<input type="hidden" name="chat_unique_id" id="chat_unique_id" placeholder="chat_unique_id" value="<?php echo rand(); ?>">
    <input type="button" onclick="wbcWooSendEnquiryData()" name="submitEnquriy" class="button-wbc-enquiry" value="Send">
  </form>

  <a class="cancle" href="javascript:;" onclick="hide('wbcEnquiry')"><i class="fa fa-times"></i></a>
</div>
</div>

    <style type="text/css">
      .someclass {
        background-color: yellow;
      }
      .editabel
      {
        width: 100%;
        height: 200px;
        border:1px solid #ccc;
      }
      .alertMessage {
        display: none;
      }
    </style>
<!-- STARTS HTML -->


<!-- <form name="myForm" onsubmit="return(validate());">
  <div class="editabel" id="send" contenteditable=""></div><span class="alertMessage">This is not a valid message</span>
  <input type="button" value="submit" onclick="return(validate());"/>
</form> -->



<!-- ENDS HTML -->





  
<!-- <script src="//code.jquery.com/jquery-1.11.1.min.js"></script> -->
    <script type="text/javascript">      

      function wbcWooSendEnquiryData() {
        
        var user_id       = jQuery('#user_id').val();
        var title         = jQuery('#title').val();
        var vendor_id     = jQuery('#vendor_id').val();
        var product_id    = jQuery('#product_id').val();
        // var message       = jQuery('#message').val();

        var message = jQuery("#message").text();

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
          } else {

            var matches = message.match(/\d+/g);
            var emailPat = /^(\".*\"|[A-Za-z]\w*)@(\[\d{1,3}(\.\d{1,3}){3}]|[A-Za-z]\w*(\.[A-Za-z]\w*)+)$/
            var phoneno = /^\d{10}$/;
            var Url = "^[A-Za-z]+://[A-Za-z0-9-_]+\\.[A-Za-z0-9-_%&\?\/.=]+$";
            var checkQuery = 5; 
            var v = wordCount( message );
            jQuery.each( v.words , function( i, l ) {
              if(l.match(phoneno) || l.match(emailPat) || l.match(Url)) {
                  jQuery(".alertMessage").show();
                  jQuery('#message').html(message.replace(
                    new RegExp(l, 'g'), '<span class=someclass>'+l+'</span>'
                  ));

                  checkQuery = 6;
                  // setTimeout(function(){
                  //   jQuery(".alertMessage").hide();
                  // },10000);
                  //return false;

              }


            });

            if(checkQuery == 6){
              return false;
            }
            jQuery(".alertMessage").hide();
             
        }

        jQuery('#errorMessage').html("");
          
          var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' );?>';
          var user_id    = jQuery('#user_id').val();
          var title      = jQuery('#title').val();
          var vendor_id  = jQuery('#vendor_id').val();
          var product_id = jQuery('#product_id').val();
          //var message    = jQuery('#message').val();
		  var chat_unique_id = jQuery('#chat_unique_id').val();
          var user_email = jQuery('#user_email').val();
          var message    = jQuery("#message").text();

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
                jQuery('#Thankyoumessage').show();
                jQuery('#user_id').val("");
                jQuery('#title').val("");
                jQuery('#message').val("");
                jQuery('#user_email').val("");
                jQuery('#Thankyoumessage').text("Thank you for your enquiry we will get back to you shortly.");


                setTimeout(function(){
                  jQuery('#Thankyoumessage').hide();
                  hide('wbcEnquiry');
                },1500);
                
           
            });
        }

      function ValidateEmail(user_email) {
        var expr = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
        return expr.test(user_email);
       
      }

  function wordCount( val ){
    var wom = val.match(/\S+/g);
    return {           
      words : wom ? wom : 0              
    };
  }

//  function validate() {
//       var message = jQuery("#message").html();
//       var matches = message.match(/\d+/g);

//       var emailPat = /^(\".*\"|[A-Za-z]\w*)@(\[\d{1,3}(\.\d{1,3}){3}]|[A-Za-z]\w*(\.[A-Za-z]\w*)+)$/
//       var phoneno = /^\d{10}$/;  
//       var Url = "^[A-Za-z]+://[A-Za-z0-9-_]+\\.[A-Za-z0-9-_%&\?\/.=]+$";      
//       var EmailmatchArray = message.match(emailPat);      
//       var isURL = message.match(Url);

//        var v = wordCount( message );   
//       jQuery.each( v.words , function( i, l ){
//         if(l.match(phoneno) || l.match(emailPat) || l.match(Url)) {
//               jQuery(".alertMessage").show();

//             jQuery('#message').html(message.replace(
//               new RegExp(l, 'g'), '<span class=someclass>'+l+'</span>'

//             ));

//             setTimeout(function(){
//               jQuery(".alertMessage").hide();
//             },10000);

//             return false;

//         }
//       });

// }


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
#Thankyoumessage{
  display: none;
}
.someclass {
    background-color: yellow;
}
.alertMessage {
  display: none;
}

#wbcEnquiry {
    position: fixed;
    top: 0;
    left: 0;
    background: rgba(0,0,0,0.7);
    height: 100%;
    width: 100%;
    z-index: 999;
    display: none;
}
.wbcEnquiryInnerArea {
    transform: translate(-50%,-50%);
    -webkit-transform: translate(-50%,-50%);
    -ms-transform: translate(-50%,-50%);
    opacity: 1;
    background: #fff;
    position: absolute;
    left: 50%;
    top: 50%;
    width: 280px;
    padding: 0 25px 20px;
}
#wbcEnquiry h1 {
  font-size: 20px;
  margin: 25px 0 0;
  color: #444;
}
.trip-info h1 {
  margin-bottom: 35px;
  font-size: 31px;
  margin-top: 20px;
}
#wbcEnquiry #result {
  margin: 15px 0;
}
#errorMessage {
    color: #f00;
    margin-bottom: 2px;
    font-size: 11px;
}
#wbcEnquiry .cancle {
  position: absolute;
  top: 4px;
  right: 6px;
  color: #444;
}
#wbcEnquiry .cancle i {
  color: #444;
  font-size: 20px;
}

#wbcEnquiry input {
  float: left;
  border-color: #ccc;
  color: #444;
  height: 25px;
  font-size: 12px;
  padding: 0 10px;
  border-width: 1px;
  margin: 0 0 10px !important;
}
#wbcEnquiry textarea {
  width: 100%;
  float: left;
  border: 1px solid #ccc;
  background: transparent;
  color: #444;
  padding: 10px;
  resize: none;
}
#wbcEnquiry textarea::placeholder {
  opacity: 1;
}
.editabel {
  width: 100%;
  height: 80px;
  border: 1px solid #ccc;
  display: inline-block;
  vertical-align: middle;
  padding: 10px;
  text-align: left;
  color: #444;
  font-size: 12px;
  margin: 0 0 5px;
}
.editabel span {
  background: yellow;
  color: #333;
}
.alertMessage {
    background: #f00;
    color: #fff;
    padding: 2px 10px;
    font-size: 12px;
}
#wbcEnquiry .button-wbc-enquiry {
  background: #f39c00;
  text-align: center !important;
  border: none;
  height: 30px;
  font-weight: 700;
  margin: 10px 0 0 !important;
  color: #fff;
  text-transform: uppercase;
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
<?php 
global $product;
global $post;
//$author_id = $post->post_author;
?>


<div class="wbc-discussion-main">
  <div class="wbc-discussion-inner">


<!-- <h1>Enquiry discussion</h1> -->
<form action="" method="post" name="enquiry-listing">
  <input type="text" name="enquiry-wbc-id" value="" id="enquiry-wbc-id" placeholder="Enquiry Id eg. wbc4c5xtd4hf">
  <input type="button" onclick="wbcCheckEnquiry()" name="check-wbc-enquiry" id="enquiry-wbc-btn-id" value="GO">
</form>

<div class="threadnotFound">
  <h3>No enquiry found!</h3>
</div>
<ul class="replyThreadHead"></ul>

<ul class="replyThread"></ul>


<div class="threadReply">
  <form action="" method="post" name="enquiry-thread">
    <h2 id='result'></h2>
    <span id="Thankyoumessage"></span>
    <div id="errorMessage"></div>
    <div class="editabel" id="message" contenteditable=""></div>
    <span class="alertMessage">This is not a valid message</span>
    <input type="button" onclick="wbcWooDiscussion()" name="submitEnquriy" class="button-wbc-enquiry" value="Reply">
  </form>
</div>

</div>
</div>


<?php /* ?>
<div id="wbcEnquiry">
  <div class="wbcEnquiryInnerArea">
  <h1>Discussion</h1>
  <form>
    <h2 id='result'></h2>

    <span id="Thankyoumessage"></span>
    <div id="errorMessage"></div>
    <input type="text" name="user_id" id="user_id" placeholder="Name">
    <input type="text" name="user_email" id="user_email" placeholder="Enter email">
    <input type="text" name="title" id="title" placeholder="Enter title">

    <div class="editabel" id="message" contenteditable=""></div>
    <span class="alertMessage">This is not a valid message</span>


    <input type="hidden" name="vendor_id" id="vendor_id" placeholder="vendor_id" value="<?php //echo $author_id; ?>">
    <input type="hidden" name="product_id" id="product_id" placeholder="product_id" value="<?php //echo $product->get_id(); ?>">
   
    <input type="button" onclick="wbcWooDiscussion()" name="submitEnquriy" class="button-wbc-enquiry" value="Send">
  </form>

  <a class="cancle" href="javascript:;" onclick="hide('wbcEnquiry')"><i class="fa fa-times"></i></a>
</div>
</div>

<?php */ ?>

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
      .replyThreadHead {
        float: left;
        width: 100%;
        padding: 10px 10px 2px 10px;
        margin: 16px 0 22px 0;
      }
      .replyThreadHead li {
        display: inline-block;
        margin: 0 18px 0 0;
        font-weight: 300;
      }
.replyThread {
    float: left;
    width: 100%;
    padding: 0;
}
.replyThread .you-message {
  border: none;
  width: auto;
  display: table;
  padding: 4px 13px;
  margin: 0 0 8px;
  border-radius: 3px;
  background-color: #eee;
}
    </style>
  
    <script type="text/javascript">   


      function wbcCheckEnquiry(){
        var enquiryId = jQuery('#enquiry-wbc-id').val();

        var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' );?>';       
          jQuery.post(
            ajaxurl, {
            'action': 'prefix_ajax_WBC_checkEnquiry',              
            'type': 'post',
            'dataType': "JSON",
            'enquiryId': enquiryId
            },
          function(response) {
            var count = Object.keys(response).length;
            

            if(count > 0){
               wbcMainThreadHead();
              jQuery('.threadReply').show();
              jQuery('.threadnotFound').hide();
              
             

              var data = response;
              var len = data.length;             
              jQuery('.replyThread').text("");
              jQuery('.replyThread').html("");
              jQuery('.replyThread').show();
              jQuery('.replyThreadHead').show();
              var user_type = "";
              for (var i = 0; i <= len; i++) {

                if(data[i]['user_type'] == "admin"){

                  jQuery('.replyThread').append('<li class="admin-message"> ' + '&nbsp' + data[i]['message']  + '<div class="message-time"> '+data[i]['last_update']+ '</div></li>');

                } else {
                  jQuery('.replyThread').append('<li class="you-message"> ' + '&nbsp' + data[i]['message']  + '<div class="message-time"> '+data[i]['last_update']+ '</div></li>');
                }

               
                         
               
              }

            } else {
              jQuery('.threadReply').hide();
              jQuery('.threadnotFound').show();

              jQuery('.replyThread').hide();
              jQuery('.replyThreadHead').hide();


            }
          });
      }


       function wbcMainThreadHead(){
        var enquiryId = jQuery('#enquiry-wbc-id').val();
        
        var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' );?>';       
          jQuery.post(
            ajaxurl, {
            'action': 'prefix_ajax_wbcGetThreadMain',              
            'type': 'post',
            'dataType': "JSON",
            'enquiryId': enquiryId
            },
          function(response) {
            var count = Object.keys(response).length;
            if(count > 0){
              jQuery('.threadReply').show();
              jQuery('.threadnotFound').hide();
              
             
              jQuery('.replyThreadHead').text("");
              jQuery('.replyThreadHead').html("");
             
               jQuery('.replyThreadHead').append('<li><h3>'+response.enquiry_title+'</h3><h4>'+response.product_title+'</h4>Provider Name: '+response.vendor_name+'</li>');              
                jQuery('.replyThreadHead').append('<li id="Date"></li>');


                setTimeout(function(){
                  var monthNames = [ "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December" ];
              var dayNames= ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"]

              var newDate = new Date();
              newDate.setDate(newDate.getDate() + 1);    
              jQuery('#Date').html(dayNames[newDate.getDay()] + " " + newDate.getDate() + ' ' + monthNames[newDate.getMonth()] + ' ' + newDate.getFullYear());
            },500);


            } else {
              jQuery('.threadReply').hide();
              jQuery('.threadnotFound').show();

            }
          });
      }







      function wbcWooDiscussion() {
        
        var user_id       = jQuery('#user_id').val();    
        var thread_id    = jQuery('#thread_id').val();
        var message = jQuery("#message").text();
        
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

              }


            });

            if(checkQuery == 6){
              return false;
            }
            jQuery(".alertMessage").hide();
             
        }

        jQuery('#errorMessage').html("");
          
          var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' );?>';       
          var thread_id = jQuery('#enquiry-wbc-id').val();
          var message    = jQuery("#message").text();

            jQuery.post(
              ajaxurl, {
                'action': 'prefix_ajax_wbcUsersReply',
                'type': 'post',
                'dataType': "JSON",                
                'message': message,
                'thread_id': thread_id,            
              },
            function(response) {              
                // Getting response from the form submission percentage
                jQuery('#Thankyoumessage').show();               
                jQuery('#message').val("");            
                jQuery('#Thankyoumessage').text("Thank you for your enquiry we will get back to you shortly.");
                jQuery("#message").text("");

                setTimeout(function(){
                  jQuery('#Thankyoumessage').hide();
                  wbcCheckEnquiry();              
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
  background:#fff;
  border-radius: 2px;
  -webkit-border-radius: 2px;
  -moz-border-radius: 2px;
}
.editabel span {
  background: yellow;
  color: #333;
}
.button-wbc-enquiry {
  float: right;
  background: #5c5e62;
  border: none;
  color: #fff;
  font-size: 16px;
  padding: 4px 20px;
  font-weight: 600;
  text-transform: uppercase;
  border-radius: 2px;
  -webkit-border-radius: 2px;
  -moz-border-radius: 2px;
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

.threadReply{
  display: none;
}
.threadnotFound{
  display: none;
}

.wbc-discussion-main {
  margin: auto;
  width: 600px;
}
.wbc-discussion-inner {
  float: left;
  width: 100%;
  background: #e1f5fe;
  padding: 20px;
  border-radius: 10px;
}
.wbc-discussion-main form {
  float: left;
  width: 100%;
}
#enquiry-wbc-id {
  float: left;
  width: 75%;
  border: 1px solid #5c5e62;
  background: #fff;
  padding: 14px 10px;
  color: #5c5e62;
  font-size: 20px;
  line-height: 26px;
  border-radius: 3px;
  margin: 0 5px 0 0;
}
#enquiry-wbc-btn-id {
  float: left;
  background: #f39b00;
  border: none;
  color: #fff;
  font-size: 20px;
  line-height: 26px;
  padding: 15px 0;
  border-radius: 3px;
  font-weight: 700;
  width: 24%;
}
#enquiry-wbc-btn-id:hover {
  background: #5c5e62;
}
.threadnotFound {
  float: left;
  width: 100%;
  margin: 40px 0 0 0;
}
.threadnotFound h3 {
  color: #096685;
  font-size: 40px;
  line-height: 40px;
  text-transform: uppercase;
  text-align: center;
  font-weight: 700;
}

.replyThreadHead li {
  width: 33.3333%;
  margin: 0;
  float: left;
  font-weight: 600;
  font-size: 14px;
  line-height: 18px;
}
.replyThreadHead h3
{
  color:#4b4b4b;
  font-weight: 700;
  margin-bottom: 5px;
}
.replyThreadHead h4
{
  color:#4b4b4b;
  font-weight: 600;
   margin-bottom: 5px;
}
.replyThreadHead li:nth-child(2) 
{
  text-align: right;
  float: right;
  margin-top: -17px;
  font-weight: 400;
}
.replyThreadHead li:nth-child(3) {
  text-align: right;
}
.you-message {
  float: right;
  position: relative;
  width: 100% !important;
  padding: 20px 20px 40px !important;
  border-radius: 8px;
  -moz-border-radius: 8px;
  -webkit-border-radius: 8px;
}
.admin-message {
  float: left;
  position: relative;
  width: 100% !important;
  padding: 20px 20px 40px !important;
  background: #dfdfdf;
  margin-bottom:8px;
  /*border-radius: 8px;
  -moz-border-radius: 8px;
  -webkit-border-radius: 8px;*/
}
.admin-message
{
  color:#202140;
}
.message-time {
  font-size: 11px;
}
.you-message .message-time {
  position: absolute;
  right: 25px;
  bottom: 4px;
}
.admin-message .message-time {
  position: absolute;
  right: 25px;
  bottom: 4px;
}
.replyThreadHead li:first-child
{
  width: 100%;
  font-weight:400;
}
#result
{
  margin-bottom: 0;
}
</style>
LOE Woo Messages

Plugin Name: LOE Woo Messages

Author: Loeion

Version: 1.0.0

Requires WordPress: 5.0+

Requires PHP: 7.4+

License: GPL v2


📋 Description

LOE Woo Messages enables real-time enquiry and discussion threads between customers and vendors on WooCommerce product pages. Customers can submit enquiries directly from the product page, receive a unique discussion ID, and continue the conversation through a dedicated frontend discussion page.


Shortcode: [loe-frontend-discussions]




✨ Features


💬 Enquiry form displayed below the Add to Cart button on every product page
🔁 Threaded discussion system between customers and vendors/admins
📧 Automatic email notifications to customer, vendor, and admin on each message
🔐 Unique discussion ID per enquiry for secure thread tracking
🛡️ Nonce-verified AJAX requests — fully secure
🗂️ Admin panel with DataTable listing of all enquiries
👁️ View full message thread from admin panel
↩️ Reply directly from admin panel
🗑️ Delete entire conversation thread
👥 Role-aware — admins see all enquiries, vendors see only their own
📱 Responsive frontend discussion page



📁 File Structure

loe-woo-messages/
│
├── wbc-woo-messages.php          # Main plugin file
│
├── inc/
│   └── loe-woo-messages.php      # Admin panel — enquiry listing & management
│
└── templates/
    ├── loe-WooMessage.php         # Enquiry form (contenteditable version)
    ├── loe-WooMessageSimple.php   # Enquiry form (textarea version)
    └── loe-WooDiscussion.php      # Frontend discussion thread page


⚙️ Installation


Download or clone this repository
Upload the loe-woo-messages folder to /wp-content/plugins/
Go to WordPress Admin → Plugins and activate LOE Woo Messages
On activation, the plugin automatically creates the database table wp_loe_woo_messages
Create a WordPress page and add the shortcode [loe-frontend-discussions]
Go to Settings → LOE Woo Messages and set the Discussion Page ID



🚀 Usage

Customer Flow


Customer visits a WooCommerce product page
Clicks "Ask More Info Now" or "Make an Enquiry" button below Add to Cart
Fills in name, email, title, and message — submits the form
Customer receives an email with a unique discussion ID
Customer visits the [loe-frontend-discussions] page
Enters their unique ID to view and continue the conversation


Admin / Vendor Flow


Go to WordPress Admin → Woo Messages
View all incoming enquiries in the DataTable
Click the eye icon to view the full message
Click the comment icon to send a reply
Click the trash icon to delete the entire thread



🔒 Security


All AJAX requests protected with WordPress nonces
All user inputs sanitized using WordPress functions (sanitize_text_field, sanitize_email, absint)
All database queries use $wpdb->prepare() — SQL injection prevention
Delete action restricted to Administrator role only
Plugin file access blocked with defined('ABSPATH') or die()
Output escaped with esc_html(), esc_attr(), esc_url() throughout



🗄️ Database

The plugin creates one table on activation:

Table: wp_loe_woo_messages

ColumnTypeDescriptionidINTAuto-increment primary keyvendor_idVARCHARPost author / vendor user IDuseridVARCHARCustomer nameuser_emailVARCHARCustomer emailtitleVARCHAREnquiry titlemessageLONGTEXTMessage contentproduct_idVARCHARWooCommerce product IDstatusVARCHAREnquiry statuschat_unique_idVARCHARUnique thread identifierreplytoVARCHARLinks reply to original threaduser_typeVARCHARuser or adminlast_updateTIMESTAMPAuto-updated on change


🔌 AJAX Actions

ActionAccessDescriptionloe_send_woo_messagePublicSubmit new enquiryloe_woo_message_deleteAdmin onlyDelete enquiry threadloe_woo_send_replyPublicAdmin/vendor replyloe_check_enquiryPublicLoad thread by unique IDloe_users_replyPublicCustomer reply to threadloe_get_thread_mainPublicGet thread header info


📧 Email Notifications

The plugin sends automatic emails on these events:

EventRecipientsNew enquiry submittedCustomer + AdminAdmin/vendor sends replyCustomerCustomer replies to threadVendor + Admin

All emails sent from: no-reply@loeion.com

From name: Loeion


🔧 Configuration

After installation, set the Discussion Page ID in the WordPress options table:

phpupdate_option( 'loe_discussion_page_id', YOUR_PAGE_ID );

Or add this temporarily to your theme's functions.php:

phpupdate_option( 'loe_discussion_page_id', 42 ); // Replace 42 with your page ID


🧩 Shortcode

Place this shortcode on any page to display the frontend discussion thread lookup:

[loe-frontend-discussions]


📦 Dependencies

LibraryVersionPurposeBootstrap3.3.7Admin panel UIDataTables1.10.16Admin enquiry tablejQuery3.2.1AJAX & UI interactions


WordPress loads its own jQuery — the admin panel CDN jQuery is only used inside the admin template.




🔄 Changelog

v1.0.0


Initial release under Loeion branding
Full rebrand from WBC → LOE
Added nonce security to all AJAX handlers
Replaced rand() with wp_generate_password() for unique IDs
All inputs sanitized and outputs escaped
All DB queries use $wpdb->prepare()
Added role-based access control
Improved email templates
Added responsive CSS
Removed dead/commented code
Added user_type column to DB schema
Added DB indexes on chat_unique_id and replyto



👨‍💻 Author

Loeion

🌐 loeion.com


📄 License

This plugin is licensed under the GNU General Public License v2 or later — the same license as WordPress itself.

You are free to use, modify, and distribute this plugin under the GPL terms.

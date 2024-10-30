=== Integration for CF7 Pardot Form Handlers ===
Contributors: leendertvb, arnodeleeuw, jorisvst
Donate link: https://www.iside.be
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Tags: CF7, form tracking, Pardot, Salesforce
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.0
Stable tag: 2.0
Send ContactForm7 fields to a Pardot form handler after a successful form submission.

== Description ==

This plugin allows you to add a [Pardot/Salesforce](https://www.salesforce.com/products/b2b-marketing-automation/) Form Handler on a form by form basis. After the form has been successfully submitted, the values of the fields will be sent to your provided Pardot Form Handler URL.

The main advantage of this plugin is that the submission will be done directly from the browser of your visitor. This will retain all the tracking info and adds the form submission to the track record in Pardot. This will improve your insights about the visitor, even before the form submission took place.

== Installation ==
This plugin can be installed like any other plugin.

### INSTALL FROM WITHIN WORDPRESS

1. Visit the plugins page within your dashboard and select ‘Add New’;
1. Search for ‘CF7 integration for Pardot Form Handlers’;
1. Install the plugin;
1. Activate the plugin from your Plugins page;

### INSTALL MANUALLY

1. Download the plugin for the Wordpress repository and unpack;
1. Upload the ‘iside-cf7-pardot-integration’ folder to the /wp-content/plugins/ directory;
1. Activate the plugin through the ‘Plugins’ menu in WordPress;

### AFTER ACTIVATION

There are no settings or configurations for this plugin.

== Frequently Asked Questions ==

= I connected the form handler URL but submissions are not coming through, what's wrong? =

Please make sure you map the IDs of the form tags from ContactForm7 with the form handler in Pardot. Double-check you made no mistakes (case sensitive). Another common issue: please do not mark any fields as required in the form handler (besides the email address). If a field is marked as required and missing from the form or query, the submission will be ignored by Pardot. So better to miss 1 value instead of the whole submission.

= I want to send a different value to Pardot then the one shown in the form, how do I do this? =

ContactForm7 supports [pipes](https://contactform7.com/selectable-recipient-with-pipes/) to define a different value to be used in emails as on the front-end. This plugin extends on that feature and can use the raw value after the pipe character as the value for Pardot. Just add the attribute <code>use-raw-values</code> to your form tag. Please use with care: this will reveal the raw values to the public. These pipe values can be used for drop down menus, radio buttons and checkboxes.

= The implementation is still not working, what can be the cause? =

Please make sure to match the HTTP/HTTPS protocol of the form handler with your website. If your website uses a secure connection over HTTPS your form handler should use that too in order to work.

= Why are some submissions not coming through? =

This can be caused by an adblocker or tracking blocker used by the visitor of your website. To minify the chances of this to happen, please use a custom [tracker domain](https://help.salesforce.com/s/articleView?id=sf.pardot_admin_first_party_cookie_add_tracker_domain.htm) for your Pardot links.

= What is this Pardot you are talking about? =

Pardot is the B2B marketing automation of [Salesforce](https://www.salesforce.com/), also known as Account Engagement. Pardot allows you to follow-up on prospects and as it is part of the Salesforce suite, it can easily transform your prospects into leads.

== Changelog ==

= 2.0 =

Release date: 2024-04-16

Bugfixes for the full plugin release.

= 1.0 =

Release date: 2024-04-05

Initial release of the plugin, adding a custom tab to CF7 forms and sending form fields to the Pardot Form Handler URL.
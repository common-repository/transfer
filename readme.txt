=== Transfer ===
Contributors: ekummer
Tags: sync, admin, administration, xml-rpc
Requires at least: 2.7
Tested up to: 2.7.1
Stable tag: 1.0.0

Allows users to submit posts to another WordPress installation.

== Description ==

Allows users to submit posts to other WordPress installations instantly. To submit the post data the transfer plugin uses the XML-RPC publishing protocol. Images, categories and tags are automatically transfered to the other WordPress instance. After content changes the plugin updates this automaticallly on save.

The plugin adds a checkbox to the edit post page, where the author can mark the current post to publish outside.

Within the WordPress backend there is a settings page to configure the other Wordpress installation.

== Installation ==

1. Upload the `transfer` folder to your `/wp-content/plugins/` directory
2. Download Zend Framework Minimal (http://framework.zend.com/download/latest) and put the Zend folder under `/wp-content/plugins/transfer/library/`
3. Activate the plugin through the `Plugins` menu in WordPress
4. Goto Settings -> Transfer to configure the plugin. Enter URL, account infos and mark the checkbox for instant publishing (if checked, all posts are instantly published on the external WordPress)

== Screenshots ==

1. The plugin adds a checkbox to the edit post page, where the author can mark the current post to publish outside.
2. You can enter URL, account infos and check the status box for instant publishing

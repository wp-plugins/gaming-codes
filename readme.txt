=== Gaming Codes ===
Contributors: Detective
Donate link: none
Tags: users, gaming, gamer, gamer codes, friend code, el-aleph, gamercard, gamertag, ggpo, ps3, nds, wii, x360
Requires at least: 2.5.1
Tested up to: 2.6
Stable tag: trunk

Gaming Codes allows each user to enter his gamer codes for "next-gen" consoles.

== Description ==

Gaming Codes allows each user to enter his gamer codes for "next-gen" consoles. These codes can be displayed in posts as author information.

Also, this plugin can use another plugin, [El Aleph](http://wordpress.org/extend/plugins/el-aleph "El Aleph on WP Extend"), to generate lists of users that have codes for a certain platform.

Examples:
* [Profile in bbPress](http://www.ryuuko.cl/bbpress/profile.php?id=15 ). This is a profile page in bbPress (integrated with WP). Note the gaming codes and the gamer card.
* [User List in Aleph](http://www.ryuuko.cl/busqueda/gente/emulacion/ggpo/ ). This is a user view in Aleph, showing all users who are registered in GGPO.net. GGPO is a gaming code supported by this plugin.

This plugin is licensed under the terms of the GPL v3. Please see the file license.txt.

= Features =

If you don't have 'El Aleph' installed: 

* Each user can add his/her own gamer code(s) for Nintendo Wii, Nintendo DS, XBox 360, PlayStation Network and GGPO.net.
* A template tag for post loops: `the_author_gaming_codes`.

If you have 'El Aleph' installed:

* A template tag for user loops: `the_user_gaming_codes`.
* An user list of people having gamer codes in a certain console/system. The template tag `gaming_codes_links` displays links to those user lists.
* The template tag `is_gaming_code_listing` returns true if the user is browsing a list based on gaming codes. 

== Changelog ==

= Version 1.5 =

* The plugin has been simplified, according to the changes in Aleph 0.7.
* Added a gamercard template tag for authors.

= Version 1.1 =

* Improved installation notes. Added a Screenshot. 
* Added localization for es_ES.
* The plugin didn't work if El Aleph wasn't installed. Now this is fixed.
* Some strings were being translated in another domain, not `gaming-codes`.
* Now if the user doesn't enter a gaming code, or deletes a previous one, the meta key/value is deleted from DB.

= Version 1.0 =

* Initial Release

== Installation ==

1. Uncompress the plugin in the subdirectory `gaming-codes` of your plugins directory.
2. Activate the plugin.
3. Add the template tag `the_author_gaming_codes` in your post/author templates.
4. For El Aleph users: Add the template tag `the_user_gaming_codes` in your profile/users templates.
5. Now your registered users can edit their gaming codes in the Dashboard Profile Edit page.   

== Screenshots ==

1. An example profile. You can see the user description, some registration details, and two gaming codes.

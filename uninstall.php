<?php
/**
 * BlockGram Uninstall
 *
 * Uninstalling BlockGram deletes options, and pages.
 */

if( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();


//Remove redirect page
wp_trash_post( get_option( 'BlockGramPlugin_Redirect_Page_ID' ) );

//Delete plugin options from wp-options table
delete_option( 'BlockGramPlugin_Redirect_Page_ID' );
delete_option( 'BlockGramPlugin_Options' );
delete_option( 'BlockGramPlugin_Follower_Options' );

?>
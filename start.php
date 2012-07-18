<?php
/**
 * Moderated Comments
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Matt Beckett
 * @copyright University of Athabasca 2011
 */

include_once 'lib/functions.php';

function moderated_comments_init() {

	// Extend system CSS with our own styles
	elgg_extend_view('css','moderated_comments/css');
	elgg_register_js('moderated_comments', elgg_get_site_url() . "mod/moderated_comments/js/javascript.js");

	//register action to approve/delete comments
	elgg_register_action("annotation/review", elgg_get_plugins_path() . "moderated_comments/actions/annotation/review.php");
	elgg_register_action("comments/anon_add", elgg_get_plugins_path() . "moderated_comments/actions/comments/anon_add.php", 'public');
	
	// register plugin hook to monitor comment counts - return only the count of approved comments
	elgg_register_plugin_hook_handler('comments:count', 'all', 'moderated_comments_comment_count', 1000); 
	
    // override permissions for the rssimport_cron context
	elgg_register_plugin_hook_handler('permissions_check', 'all', 'moderated_comments_permissions_check');	
  
  // prevent complications with editable comments
  elgg_register_plugin_hook_handler('editablecomments:canedit', 'comment', 'moderated_comments_editablecomments_check');
}


// call init
elgg_register_event_handler('init','system','moderated_comments_init');

// check if newly created comment needs to be reviewed
elgg_register_event_handler('create','annotation','moderated_comments_check');

// check if newly created entity is public - if so moderate
elgg_register_event_handler('create','all','moderated_comments_entity_create');

// check if newly updated entity is public - if so moderate
elgg_register_event_handler('update','all','moderated_comments_entity_create');

// extend the form view to present a notice that comments are moderated
elgg_extend_view('page/components/list', 'comments/forms/moderated_comments_pre_edit', 501);

?>
<?php
/**
 *
 *		This view prepends the comment form, displays the notice letting users know comments are moderated
 * 		If the user is the content owner, it gives the forms to batch approve/delete comments
 * 
 * 		Note that as of 1.8 this gets called inside another form, hence the javascript needed
 * 		to properly submit approve/delete
 *
 */
elgg_load_js('moderated_comments');

if($vars['annotation_name'] == "generic_comment" && is_numeric($vars['guid'])){

$entity_guid = $vars['guid'];
$entity = get_entity($entity_guid);

// have to count total number of comments like this because we have a plugin hook
// that modifies the default count to only include visible comments
$tmpcommentcount = $entity->getAnnotations('generic_comment');
$real_comment_num = count($tmpcommentcount);
$visible_comment_count = $entity->countComments();

$comments_to_moderate = $real_comment_num - $visible_comment_count;

// only show the message once
if($mc_notice_count != 1){
	$mc_notice_count = 1;
	

	if((moderated_comments_is_moderated($entity_guid) && !elgg_is_logged_in()) || (moderated_comments_is_moderated($entity_guid) && $comments_to_moderate > 0 && $entity->owner_guid == elgg_get_logged_in_user_guid())){
		echo "<div class=\"generic_comment mc_notice\">";
	}


	if(elgg_get_logged_in_user_guid() == $entity->owner_guid && moderated_comments_is_moderated($entity_guid) && $comments_to_moderate > 0){
		?>
<div class="mc_moderation_control">
	<form id="mcApprovalForm"
		action="<?php echo $vars['url']; ?>mod/moderated_comments/actions/annotation/review.php"
		method="post">
		<input id="mcApprovalID" type="hidden" name="id" value=""> <input
			type="hidden" name="method" value="approve"> <input type="submit"
			value="<?php echo elgg_echo('moderated_comments:approve_checked'); ?>">
	</form>
</div>
<div class="mc_moderation_control">
	<form id="mcDeleteForm"
		action="<?php echo $vars['url']; ?>mod/moderated_comments/actions/annotation/review.php"
		method="post">
		<input id="mcDeleteID" type="hidden" name="id" value=""> <input
			type="hidden" name="method" value="delete"> <input type="submit"
			value="<?php echo elgg_echo('moderated_comments:delete_checked'); ?>"
			onclick="return confirm('<?php echo elgg_echo('moderated_comments:delete_confirm'); ?>');">
	</form>
</div>
<script type="text/javascript">
	var idarray = new Array();
	</script>
		<?php
	} 

	if(moderated_comments_is_moderated($entity_guid) && !elgg_is_logged_in()){
		echo "<div style=\"clear: both\">" . elgg_echo('moderated_comments:moderated_notice') . "</div>";
	}

	if((moderated_comments_is_moderated($entity_guid) && !elgg_is_logged_in()) || (moderated_comments_is_moderated($entity_guid) && $comments_to_moderate > 0 && $entity->owner_guid == elgg_get_logged_in_user_guid())){
		echo "<div class=\"mc_clear_div\"></div>";
		echo "</div>";
	}
	?>

	<?php
} // end if $mc_notice_count
}

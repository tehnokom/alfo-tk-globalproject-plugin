/**
 * @author Sarvaritdinov Ravil
 */

var $j = jQuery.noConflict();

$j(document).ready(function ($j) {
	$j('.tkgp_vote_block').on('tkgp_vote_updated',test);
});

function test(event, res) {
	var percent = 100.0 * res.approval_votes / res.target_votes;
	if(percent > 100) {
		percent = 100;
	}
	
	$j(this).find('.tkgp_vote_buttons').replaceWith(res.new_content);
	$j(this).find('.tk-approval-votes').text(res.approval_votes);
	$j(this).find('.tk-target-votes').text(res.target_votes);
	$j(this).find('.tk-pb-approved').css('width',percent + '%');
}

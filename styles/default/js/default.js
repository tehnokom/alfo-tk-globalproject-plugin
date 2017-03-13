/**
 * @author Sarvaritdinov Ravil
 */

var $j = jQuery.noConflict();

$j(document).ready(function ($j) {
	$j('.tkgp_vote_block').on('tkgp_vote_updated',tk_vote_update);
	
	var main_tab = $j('a.tk-tab-main').attr("href");
	if(location.hash.search(/#tk-tab/i) < 0 && main_tab.length) { //временная заглушка, пока не работают все вкладки
		location = location + main_tab;
	}
});

function tk_vote_update(event, res) {
	var percent = 100.0 * res.approval_votes / res.target_votes;
	var new_content = res.new_content.length != 0 ? res.new_content : '<div class="tkgp_button tk-supported"><a>' + tkl10n.you_supported + '</a></div>' ;
	
	if(percent > 100) {
		percent = 100;
	}
		
	$j(this).find('.tkgp_vote_buttons').replaceWith(new_content);
	$j(this).find('.tk-approval-votes').text(res.approval_votes);
	$j(this).find('.tk-target-votes').text(res.target_votes);
	$j(this).find('.tk-pb-approved').css('width',percent + '%');
}

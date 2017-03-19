/**
 * @author Sarvaritdinov Ravil
 */

var $j = jQuery.noConflict();

$j(document).ready(function ($j) {
	$j('.tkgp_vote_block').on('tkgp_vote_updated',tk_vote_update);
	$j('.tk-tabs > a[href^=#tk-tab]').on('click', tk_tab_handler);
	
	if(location.hash !== '') {
		var cur_tab = location.hash.match(/#tk-tab[\d]{1,}/).toString().replace(/[^\d]+/,'');

	 	if(cur_tab) {
	 		$j('.tk-tabs > a[href=#tk-tab' + cur_tab + ']').click();
	 		tk_tab_handler(cur_tab);
	 	}	
	} else {
		tkgp_ajax_get_news(tk_update_tab, {tab_num: 1});
	}
});

function tk_tab_handler(tab_number) {
	var tab_num = typeof tab_number !== 'object' ? tab_number : $j(this).attr('href').replace(/[^\d]+/,'');

	switch(tab_num) {
		case '1':
			tkgp_ajax_get_news(tk_update_tab, {tab_num: tab_num});
			break;
		
		case '2':
			tkgp_ajax_get_target(tk_update_tab, {tab_num: tab_num});
			break;
		
		default:
			break;
	}
}

function tk_vote_update(event, res) {
	var percent = 100.0 * res.approval_votes / res.target_votes;
	var new_content = res.new_content.length != 0 ? res.new_content : '<div class="tkgp_button tk-supported"><a>' + tkl10n.you_supported + '</a></div>' ;
	
	if(percent < 0.75) {
		percent = 0.75;
	} else if(percent > 100) {
		percent = 100;
	}
		
	$j(this).find('.tkgp_vote_buttons').replaceWith(new_content);
	$j(this).find('.tk-approval-votes').text(res.approval_votes);
	$j(this).find('.tk-target-votes').text(res.target_votes);
	$j(this).find('.tk-pb-approved').css('width',percent + '%');
}

function tk_update_tab(html, args) {
	$j('.tk-tabs > div:nth-of-type(' + args.tab_num + ')').empty().append(html);
}

var $j = jQuery.noConflict();

$j(document).ready(function($j){
		$j(".tkgp_radio li input[type='radio']").addClass('tkgp_radio_hidden');
		$j(".tkgp_radio li input[type='radio']").click(handler_tkgp_radio); //обработчик для переключателей
		$j(".tkgp_radio li input[type='radio']").click(handler_tkgp_select_radio);
		
		if($j(".tkgp_radio li input[type='radio']:checked").length == 0) {
			$j(".tkgp_radio li input[type='radio'][checked='true']").addClass('tkgp_radio_checked').click();}
		else {
			$j(".tkgp_radio li input[type='radio']:checked").addClass('tkgp_radio_checked').click();}
	}
);

function handler_tkgp_radio() {
	$j(".tkgp_radio li input[type='radio']").removeClass('tkgp_radio_checked');
	$j(".tkgp_radio li input[type='radio']").removeAttr('checked');
	$j(this).addClass('tkgp_radio_checked');
	$j(this).attr('checked','true');
		
}

function handler_tkgp_select_radio() {
	if(this.value === '3') {
		//alert($j('.tkgp_group_select').length);
		$j('.tkgp_group_select option').attr('disabled','');
		$j('.tkgp_group_select option[selected=""]').removeAttr('selected');
		$j('.tkgp_group_select [value="0"]').attr('selected','').removeAttr('disabled');
	}
	else $j('.tkgp_group_select option').removeAttr('disabled');
}

var $j = jQuery.noConflict();

$j(document).ready(function($j){
		$j(".tkgp_radio li input[type='radio']").addClass('tkgp_radio_hidden');
		if($j(".tkgp_radio li input[type='radio']:checked").length == 0) {
			$j(".tkgp_radio li input[type='radio'][checked='true']").addClass('tkgp_radio_checked');}
		else {
			$j(".tkgp_radio li input[type='radio']:checked").addClass('tkgp_radio_checked');}
		
		$j(".tkgp_radio li input[type='radio']").click(function() { //обработчик нажатия на список переключателей
			$j(".tkgp_radio li input[type='radio']").removeClass('tkgp_radio_checked');
			$j(".tkgp_radio li input[type='radio']").removeAttr('checked');
			$j(this).addClass('tkgp_radio_checked');
			$j(this).attr('checked','true');
		});
	}
);
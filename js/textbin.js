jQuery(document).ready(function($) {
	$("a.tb_delete").click(function(){
		var tb_delete_confirm = confirm('Delete this TextBin Item?');
		var id = $(this).attr('id');
		if(tb_delete_confirm) {
			return true;
		} else {
			return false;
		}
	});
	
	
	$("a#btn_textbin_import").click(function(evt){
		$('#textbin_input_form_fields').toggle();
		evt.preventDefault();
	});
	
	
	$('#textbin-search-input').on('input', function() {
		if($('#textbin-search-input').val() == '') {
			$('#textbin_search_indicator').show();
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: { 
					term: '',
					action:'tb_search',
				}
			}).done(function(data) {
				$('#textbin_search_indicator').hide();
				$('#textbin_table_list').html(data);
			});
		}
	});
	
	
	
	$('#textbin_filter').submit(function () {
		var tb_term = $('#textbin-search-input').val();
		
		$('#textbin_search_indicator').show();
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: { 
				term: tb_term,
				action:'tb_search',
			}
		}).done(function(data) {
			$('#textbin_search_indicator').hide();
			$('#textbin_table_list').html(data);
		});
				
		return false;
	});
	
	
	
	var textbin_fixHelper = function(e, ui) {
		ui.children().each(function() {
			$(this).width($(this).width());                  
		});
		return ui;
    };
	
	
	// prevents cells from collapse
	$("#textbin_table_list table tbody").sortable({
		helper: textbin_fixHelper,
		handle: '.tbHandle',
		axis : 'y',
		update : function () {
			$('#textbin_save_indicator').show();
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: { 
					sort: $('#textbin_table_list table tbody').sortable('serialize'),
					action:'tb_reorder'
				}
			}).done(function(data) {
				$('#textbin_save_indicator').hide();
			});
		}
	});
});

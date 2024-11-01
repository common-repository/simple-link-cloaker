jQuery(document).ready(function($){
	$("#wpsc_add_link").click(function(e){
		var loading_img = $(this).siblings('.loading-img').first();
		var invalid = false;
		e.preventDefault();
		if($('#link-name').val() == ''){
			$('#link-name').closest('.form-field').addClass('form-invalid');
			invalid = true;
		}
		if($('#link-url').val() == ''){
			$('#link-url').closest('.form-field').addClass('form-invalid');
			invalid = true;
		}
		if(invalid) return;
		loading_img.show();
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: { action: 'wpsc_add_link', name: $('#link-name').val(), slug: $('#link-slug').val(), url: $('#link-url').val(), status: $('#link-status').val()},
			success:function(data, textStatus, XMLHttpRequest){
				loading_img.hide();
				wpsc_refresh_table();
			},
			error: function(MLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
			}
		});
	});

	$('#links-table-container .delete a').click(function(e){
		e.preventDefault();
		row = $(this).closest('tr');
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: { action: 'wpsc_delete_link', id: $(this).siblings('.link-id').first().val() },
			success:function(data, textStatus, XMLHttpRequest){
				if(data)
					row.css({'background':'#FFEBE8'}).fadeOut();
			},
			error: function(MLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
			}
		});
	});

	function wpsc_refresh_table(){
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: { action: 'wpsc_get_table_html', page: $('#links-table-container #wpsc_page').val() },
			success: function(data, textStatus, XMLHttpRequest){
				$('#links-table-container .table-wrap').html(data);
				$('input[type=text], textarea').val('');
				$('.form-invalid').removeClass('form-invalid');
			},
			error: function(MLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
			}
		});
	}
});
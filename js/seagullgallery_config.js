/*	SeagullGallery for admin part
*	Version 0.0.3
*	Last update: 2012-03-21
*/
var ajaxurl = '/assets/modules/seagullgallery/ajax.php';

$(document).ready(function() {

//	COLOR PICKER ------------------------
	$('input.colorpicker').jPicker({
		window: {
			expandable: false,
			alphaSupport: true,
			updateInputColor: false,
			position: {
				x: 'screenCenter', /* acceptable values "left", "center", "right", "screenCenter", or relative px value */
				y: 'bottom' /* acceptable values "top", "bottom", "center", or relative px value */
			}
		},
		color: {
			mode: 'a'
		}
	},
	function(color, context) {
		$(this).val(color.val('ahex'));
	}
	);

	$('#f-add-gallery').ajaxForm({ url:ajaxurl, dataType:'json',
		success: function(data) {
			msg.showAjax(data);

			$(this).oneTime('1s', function() {
				postForm('editGallery', data.gallery_id);
			});

		},
		error: function(data){
			msg.show('Ошибка при отправке запроса', 'error');
		}
	});

	$('#f-gallery-config').ajaxForm({ url:ajaxurl, dataType:'json', data:{cmd:'saveGallery'},
		beforeSend: function() {
		},
		success: function(data) {
			msg.showAjax(data);
		}
	});

//	RESIZE THUMBS ------------------------
	$('#btn-resize-thumbs').click(function() {
//		var id = row.attr('id');
		msg.show('Операция выполняется...', 'loading');

		$.post(ajaxurl, {cmd:'resizeThumbs', itemID:$('#ff-gid').val()}, function(data) {
			msg.showAjax(data);
		}, 'json');
	});

	$('#btn-clear-tables').click(function() {
		if (confirm("Будут удалены все галереи без возвратно. Продолжить?")) {
			msg.show('Операция выполняется...', 'loading');

			$.post(ajaxurl, {cmd:'clearTables', itemID:$('#ff-gid').val()}, function(data) {
				msg.showAjax(data);
			}, 'json');
		}
		return false;
	});

});

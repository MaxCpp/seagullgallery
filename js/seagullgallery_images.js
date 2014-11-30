/*	SeagullGallery for admin part
*	Version 0.0.3
*	Last update: 2012-03-21
*/
var ajaxurl = '/assets/modules/seagullgallery/ajax.php';
var arrSelectImages = [];

$(document).ready(function() {
	allLockBtns = $('#btn-del-imgs, #btn-copy-imgs');
	allLockBtns.addClass('disabled');

//  BROWSER VIEW
    $('.btn-browser-view').click(function() {

        $.ajax({ type:'POST', url:ajaxurl, timeout:5000, dataType:'json',
            data: {cmd:'changeBrowser', view:$(this).data('view'), itemID:$('#ff-gid').val()},
            success: function(data){
                msg.showAjax(data);

                if (data.msgType === 'info') {
                    $(this).oneTime('1s', function() {
                        postForm('editGallery', $('#ff-gid').val());
                    });
                }
            },
            error: function(data){
                showMsg('Ошибка при отправке запроса', 'error');
            }
        });

        return false;
    });

// SORT TABLE ---------------------------
	$("table.tsort").tableDnD({
		onDragClass: "tsort__dragClass",
		dragHandle: "tsort__dragHandle",
		onDrop: function(table, row) {
			var rows = table.tBodies[0].rows;
			var w = '';
			// В цикле создаем разделенный символом ";" список, в котором последовательно размещены id строк
			for (var i = 0; i < rows.length; i++) {
				if (i != 0)
					w += ',';
				w += rows[i].id;
			}
			// Передаем данные на сервер
			$.ajax({ type:'POST', url:ajaxurl, timeout:5000, dataType:'json',
				data: {arr_sort: w, cmd: 'imgs_sort'},
				success: function(data){
					msg.showAjax(data);
				},
				error: function(data){
					msg.show('Ошибка при отправке запроса', 'error');
				}
			});
		}
	});
	$('table.tsort tbody tr').hover(function() {
		$(this.cells[1]).addClass('showDragHandle');
	}, function() {
		$(this.cells[1]).removeClass('showDragHandle');
	});

//	SELECT IMAGES ------------------------
	$(document).on('click', '.b-table .img_select', function() {
		var selTR = $(this).parent('td').parent('tr');
		var selID = selTR.prop('id');

		if (arrSelectImages.in_array(selID)) arrSelectImages.splice(arrSelectImages.indexOf(selID), 1);
		else arrSelectImages.push(selID);

		if (arrSelectImages.length === 0)	allLockBtns.addClass('disabled');
		else allLockBtns.removeClass('disabled');

		if ($(this).prop('checked') == true) selTR.addClass('row-selected');
		else selTR.removeClass('row-selected');

	});

	$(document).on('click', '.gallery-grid-item', function() {
		var selID = $(this).attr('id');
		var checked = $(this).children('.img_select');

		if (arrSelectImages.in_array(selID)) {
			arrSelectImages.splice(arrSelectImages.indexOf(selID), 1);
			$(this).removeClass('gallery-grid-item_selected_yes');
		}
		else {
			arrSelectImages.push(selID);
			$(this).addClass('gallery-grid-item_selected_yes');
			checked.prop('checked', true);
		}

		if (arrSelectImages.length === 0)	allLockBtns.addClass('disabled');
		else allLockBtns.removeClass('disabled');
	});

	// $('.gallery-grid-item').dblclick(function() {
	// 	console.log('dbl click');
	// });

//	UPLOAD IMAGES ------------------------
	var bar = $('.b-progress__bar');
	var percent = $('.b-progress__percent');

	$('#btn-add-imgs').click(function() {
		$('#btn-selectfiles').click();
		return false;
	});
	$('#btn-selectfiles').change(function() {
		$('#f-upload').submit();
	});

	$('#f-upload').ajaxForm({ url:ajaxurl, dataType: 'json',
		beforeSend: function() {
			$('.b-progress').show();
			var percentVal = '0%';
			bar.width(percentVal)
			percent.html(percentVal);
		},
		uploadProgress: function(event, position, total, percentComplete) {
			console.log('position:'+position+' total:'+total+' percentComplete:'+percentComplete);
			var percentVal = percentComplete + '%';
			bar.width(percentVal)
			percent.html(percentVal);
			//console.log(percentVal, position, total);
		},
		complete: function(xhr) {
			console.log(xhr.responseText);
//			status.html(xhr.responseText);
		},
		success: function(data) {
			msg.showAjax(data);
			$('#t-imgs > tbody').append(data.rows);
			$('.b-progress').hide();
			$('#no-imgs').remove();
		},
		error: function(data){
			msg.show('Ошибка при отправке запроса (f-upload)', 'error');
		}
	});

//	SUBMIT DELETE IMAGES ------------------------
	$('#btn-del-imgs').click(function() {
		if ($(this).hasClass('disabled'))
			return false;

		var query = $('#f-imgs-form').formSerialize();

		$.post(ajaxurl, query+'&cmd=delImgs', function(data){
			console.log(data.remove_arr);
			$(data.remove_arr).remove();
			msg.showAjax(data);
//			Скрытие/отключение разных кнопок, чтобы не возникало конфликтов
//			guiStatus('lock');
		}, 'json');
		return false;
	});

//	IMAGES COPY TO GALLERY ------------------------
    $('#dialog-select-gallery').dialog({
        autoOpen: false,
        height: 500,
        width: 450,
        modal: true,
        buttons: {
            'Скопировать изображение(я)': function() {
                console.log('copy');
                $.ajax({ type:'POST', url:ajaxurl, timeout:5000, dataType:'json',
                    data: {
                        cmd:'copyImages',
                        fromGalID:$('#ff-gid').val(),
                        toGalID:$('.selectGallery:checked').val(),
                        imgs:arrSelectImages
                    },
                    success: function(data) {
                        msg.showAjax(data);
                        // $('#table-select-gallery').html(data.tbody);
                    },
                    error: function(data) {
                        msg.show('Ошибка при отправке запроса', 'error');
                    }
                });
            },
            'Закрыть': function() {
                $(this).dialog( "close" );
            }
        },
        close: function() {
            // form[0].reset();
            // allFields.removeClass("ui-state-error");
        }
    });

	$('#btn-copy-imgs').click(function() {

		$.ajax({ type:'POST', url:ajaxurl, timeout:5000, dataType:'json',
			data: {cmd: 'ckeditorSelectGallery'},
			success: function(data){
				$('#table-select-gallery').html(data.tbody);
			},
			error: function(data){
				showMsg('Ошибка при отправке запроса', 'error');
			}
		});

		$('#table-select-gallery').on('click', 'tr.row-edit', function() {
			$(this).children('td').eq(0).children('input').prop('checked', true);
		});

        $('#dialog-select-gallery').dialog('open');

		return false;
	});

//	enable/disable элементов интерфейса при вызове форм
	function guiStatus(action) {
		if (action==='lock') {
			$('#f-addimage input').attr('disabled', 'disabled');
//			$('#add-image').button('disable');
//			$('#submit-image').button('disable');
//			$('#btn-del-imgs').button('disable');
		}
		else {
			$('#f-addimage input').attr('disabled', '');
//			$('#add-image').button('enable');
//			$('#submit-image').button('enable');
//			$('#btn-del-imgs').button('enable');
		}
	};

//	OPEN EDIT FORM ------------------------
	$(document).on('click', '#t-imgs td.col-edit', function() {
		btn_cancel();

		var row = $(this).parent('tr');
		var id = row.attr('id');

		row.before('<tr id="edit_form_row"><td id="photo" style="vertical-align:top"></td><td></td><td id="edit_form_col" colspan="'+($('#t-imgs tr:first-child td').length-2)+'"></td></tr>');
		row.hide();
		$('#edit_form_col').append($('#form-container').contents());

		$.post(ajaxurl, {cmd:'getimg', id:id}, function(data){

			$('#photo').append($('#'+id+' td:eq(0)').html());

			$('#ff-img-id').val(data.obj.id);
			$('#ff-img-title').val(data.obj.title);
			$('#ff-img-description').val(data.obj.description);

//			Скрытие/отключение разных кнопок, чтобы не возникало конфликтов
			guiStatus('lock');
		}, 'json');
	});

//	CLOSE FORM ------------------------
	$(document).on('click', '#t-imgs input.btn-cancel', btn_cancel);

	function btn_cancel() {
		if ($('#edit_form_row').length) {	// if edit form
			$('#edit_form_row').next().show();
			$('#edit_form_col').contents().appendTo($('#form-container'));
			$('#edit_form_row').remove();

			$('#ff-id').remove();
			$('#f-image').clearForm();
//			Активация/включение разных кнопок, полей после их блокировки
			guiStatus('unlock');

			if ($('#btn-crop-release').is(':visible')) {
				crop_destroy();
			}
		}
		else {	// if new form
			if ($('#new_form_col').length) {
				$('#new_form_col').contents().appendTo($('#form-container'));
				$('#new_form_row').remove();

				$('#btn-new-form').button('option', 'label', $('#btn-new-form').attr('title'));
				$('#f-image').find('.btn-del').show();
			}
		}
//		$(this).button('enable');
	};

// SUBMIT FORM ---------------------------
	$('#f-image').ajaxForm({ url:ajaxurl, dataType:'json',
		beforeSubmit: function(arr, $form, options) {
			msg.show('Операция выполняется...', 'loading');
		},
		success: function(data) {
			msg.showAjax(data);
			if (data.ok.length) {
				if (data.edit) {
					$('#edit_form_row').next().remove();	// delete row with old data
					$('#edit_form_row').after(data.obj); // insert row with new data

					$('#edit_form_col').contents().appendTo($('#form-container'));
					$('#edit_form_row').remove();
//						guiStatus('unlock');
				}
				else {
					$('#new_form_row').after(data.obj);
				}
				$('#f-image').resetForm();
			}
		},
		error: function(data){
			msg.show('Ошибка при отправке запроса', 'error');
		}
	});

/*	$("#t-imgs img").click(function() {

	});
*/

});

Array.prototype.in_array = function(p_val) {
	for(var i = 0, l = this.length; i < l; i++)	{
		if(this[i] == p_val) {
			return true;
		}
	}
	return false;
}

var ajaxurl = '/assets/modules/seagullgallery/ajax.php';

$(document).ready(function() {
//	В td:not(td:eq(1)) перечислить номера тех столбцов на которых при клике не осуществлять переход
	$(document).on('click', '#t-galleries tbody tr.row-edit td:not(td:nth-child(2))', function() {
		postForm('editGallery', $(this).parent('tr').attr('id').replace(/row/, ''));
	});
});
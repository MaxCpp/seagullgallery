var ajaxurl = '/assets/modules/seagullgallery/ajax.php';

$(document).ready(function() {
//	� td:not(td:eq(1)) ����������� ������ ��� �������� �� ������� ��� ����� �� ������������ �������
	$(document).on('click', '#t-galleries tbody tr.row-edit td:not(td:nth-child(2))', function() {
		postForm('editGallery', $(this).parent('tr').attr('id').replace(/row/, ''));
	});
});
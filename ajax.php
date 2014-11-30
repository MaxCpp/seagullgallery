<?php

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

	require_once('./classes/class.seagullgallery.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'/manager/includes/config.inc.php');

	$connect = db_connect($database_server, $database_user, $database_password);
	$db = str_replace('`', '', $dbase);
	$db = db_select($db, $connect);

	if (!$db) {
		echo 'Невозможно установить соединение c базой данных "'.$dbase.'" на "'.$database_server.'"';
		exit();
	}

	$sg = new CSeagullGallery($msg);
	$response = array();

	switch ($_REQUEST['cmd']) {
		case 'addGallery':
			if ($sg->saveGallery($_POST)) {
				$msg->setReload();
			}
		break;

		case 'saveGallery':
			if (!$sg->saveGallery($_POST))
				$msg->setError('Ошибка при сохранении настроек');
		break;

		case 'addimgs':
			$rows = $sg->addImages($_POST['itemID'], $_FILES['imgs']);
			if ($rows)
				$response['rows'] = $rows;
			else
				$msg->setError('Сбой при загрузке, попробуйте еще раз');
		break;

		case 'saveimg':
			if (isset($_POST['id'])) {
				$r = $sg->updateImage($_POST, $_FILES['imgfile']);

				if ($r) {
//							$config->gallery_dir.'/thumb'.$this->path.'/'.$item['file_thumb']
					$response['obj'] = $r;
					$response['edit'] = true;
					$msg->setOk('Изображение #'.$_POST['id'].' отредактированно');
				}
				elseif (!$msg->keep_error)
					$msg->setError('Сбой при сохранении изображении');
			}
		break;

		case 'getimg': {
			$_POST['id'] = str_replace('img', '', $_POST['id']);
			$r = $sg->getImage($_POST['id']);

			if ($r) {
				$response['obj'] = $r;
			}
			else
				$msg->setError('Сбой при получении данных о изображении');
		} break;

		case 'delImgs':
			if ($_POST['imgs_select']) {
				if ($sg->delImages($_POST['imgs_select'])) {
					$c = count($sg->arr_del);
					for ($i=0; $i<$c; $i++)
						$response['remove_arr'][] = '#img'.$sg->arr_del[$i];
					$response['remove_arr'] = implode(',', $response['remove_arr']);

					$msg->setInfo('Изображение(я) удалено');
				}
				else
					$msg->setError('Ошибка при удалении изображения(й)');
			}
			else {
				$msg->setInfo('Выделите изображения для удаления');
			}
		break;

		case 'delGallery':
			if ($sg->delGallery($_POST['itemID'])) {
				$msg->setInfo('Галерея удалена');
				$msg->setReload();
			}
			else
				$msg->setError('Ошибка при удалении изображения(й)');
		break;

		case 'changeBrowser':
			if ($sg->changeBrowserView($_POST['view'])) {
				$msg->setInfo('Смена отображения');
				$msg->setReload();
			}
			else
				$msg->setError('Ошибка при удалении изображения(й)');
		break;

		case 'imgs_sort':
			if ($sg->sortImages($_POST['arr_sort']))
				$msg->setOk('Изображения отсортированы');
			else
				$msg->setError('Ошибка при сортировке');
		break;

		case 'copyImages':
			if ($sg->copyImages($_POST['fromGalID'], $_POST['toGalID'], $_POST['imgs']))
				$msg->setOk('Изображение(я) скопированы');
			else
				$msg->setError('Ошибка при копировании');
		break;

		case 'resizeThumbs':
			if ($sg->resizeThumbs($_POST['itemID']))
				$msg->setOk('Миниатюры пересчитаны');
			else
				$msg->setError('Ошибка при пересчете миниатюр');
		break;

		case 'ckeditorSelectGallery':
			$r = $sg->renderTableCKEditor();

			if ($r) {
				$response['tbody'] = $r;
			}
			elseif (!$msg->keep_error)
				$msg->setError('Сбой при сохранении изображении');
		break;

		case 'clearTables':
			if ($sg->clearTables($_POST['itemID']))
				$msg->setOk('Таблицы очищены');
			else
				$msg->setError('Ошибка при сохранении настроек');
		break;

		case 'saveConfig':
			if ($sg->config->saveForm($_POST['config'], $sg->nameModule))
				$msg->setOk('Глобальные настройки сохранены');
			else
				$msg->setError('Ошибка при сохранении');
		break;

		case 'setPublished':
			$r = run_sql('UPDATE '.$sg->tables['galleries']->table.' SET `published`=\''.$_POST['val'].'\' WHERE `id`='.$_POST['itemID']);	// запись прочитана
			$_POST['val'] ? $msg->setOk('Галерея №'.$_POST['itemID'].' опубликована') : $msg->setOk('Галерея №'.$_POST['itemID'].' скрыта');
		break;

		case 'getPaginatorPage':
			if ($_REQUEST['param']==='gals') {
				$response['tbody'] = $sg->tables['galleries']->renderTableBody($_REQUEST['pageID']);
				$response['links'] = $sg->tables['galleries']->renderPaginatorLinks($_REQUEST['pageID']);
			}
			else {
				$gid = str_replace('imgs', '', $_POST['param']);
				$response['tbody'] = $sg->renderImages($gid, $_REQUEST['pageID'], TBODY);
				$response['links'] = $sg->tables['images']->renderPaginatorLinks($_REQUEST['pageID'], array('param'=>'imgs', 'query'=>'SELECT COUNT(`id`) FROM '.$sg->tables['images']->table.' WHERE `gallery_id`='.$gid));
			}
			$msg->setInfo('Следующая страница');
		break;
	}
	$response = array_merge($response, $msg->get());
	echo json_encode($response);
}
?>

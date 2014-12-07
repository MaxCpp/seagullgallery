<?php
/*	Class SeagullGallery 0.0.8
	Date update 0.0.8: 2014-12-07
		- save size thumbs in BD
	Date update 0.0.7: 2014-11-30
		- copy image from gallery to gallery
	Date update 0.0.6: 2014-04-11
	Date update 0.0.5: 2014-04-07
	Date update 0.0.4: 2014-01-23
	Date update 0.0.3: 2013-10-01
	Date update 0.0.2: 2012-03-22
*/

//ini_set('display_errors',1);
//error_reporting(E_ALL);
define('BROWSER_FULL_TABLE', 'table');
define('BROWSER_TBODY', 'tbody');
define('BROWSER_GRID', 'grid');
define('DIR_WRITE_TRUE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/assets/modules/seagulllibrary/class.seagullmodule.php');

class CSeagullGallery extends CSeagullModule {
	var $modx = null;
	var $ph = array();
	var $tables = array();
	var $nameModule = 'seagullgallery';
	const mailto = 'maxcpp@gmail.com';

	function __construct() { //--------------------------------
		$args = func_get_args();
		$this->msg = $args[0];

		$this->config = new CConfig($this->msg);
		$this->config->getVariables($this->nameModule);
		$this->config->labelParam .= ' style="width:240px"';

		if (isset($args[1]))
			$this->modx = $args[1];
		$this->ph['title'] = 'Менеджер галерей';
		$this->ph['nameModule'] = $this->nameModule;

//------------------------------------------------
		$columns = array();
		$columns['id'] = array(
					'title'=>'ID',
					'form_hidden'=>true,
					'form_dontEdit'=>true,
					'table_theadParam'=>' style="width:30px;"'
					);

		$columns['published'] =	array(
					'title'=>'Опубликован',
					'form_fieldType'=>'checkbox',
					'table_td_content'=>array('published'=>array(0=>'<div class="b-unpublished" title="Скрыт"></div>', 1=>'<div class="b-published" title="Опубликован"></div>')),
					'table_theadParam'=>'style="width:20px"',
					'table_title_hidden'=>true
					);

		$columns['title'] =	array(
					'title'=>'Заголовок',
					'form_fieldType'=>'input',
					'form_fieldParam'=>'style="width:80%"'
					);

		$columns['description'] = array(
					'title'=>'Подпись',
					'form_fieldType'=>'textarea',
					'form_fieldParam'=>'style="width:80%"',
					'table_hidden'=>true
					);

		$columns['browser_view'] = array(
					'title'=>'Вид галереи в админке',
					'form_fieldType'=>'radio',
					'values'=>array('table'=>'Таблица', 'grid'=>'Миниатюры'),
					'table_hidden'=>true
					);

		$columns['type_view'] =	array(
					'title'=>'Внешний вид галереи на сайте',
					'form_fieldType'=>'select',
					'values'=>array('thumbs'=>'Миниатюры с увеличением', 'images'=>'Большие изображения', 'image_and_thumbs'=>'Большое изображение и прокручиваемый список миниатюр', 'slider'=>'Слайдер'),
					'table_hidden'=>true
					);

		$columns['parent_id'] =	array(
					'title'=>'Родительская галерея',
					'form_fieldType'=>'number',
					'form_fieldParam'=>'style="width:50px"',
					'table_hidden'=>true
					);

		$columns['sort_id'] =	array(
					'title'=>'Сортировка',
					'form_fieldType'=>'number',
					'form_fieldParam'=>'style="width:50px"',
					'table_hidden'=>true
					);

		$columns['alias'] =	array(
					'title'=>'Псевдоним',
					'form_fieldType'=>'input',
					'table_hidden'=>true
					);

		$columns['path'] =	array(
					'title'=>'Путь',
					'form_fieldType'=>'input',
					'form_dontEdit'=>true,
					'table_hidden'=>true,
					'form_fieldParam'=>'disabled="disabled"'
					);

		$columns['count_img'] =	array(
					'title'=>'Кол-во изображений',
					'form_fieldType'=>'number',
					'form_fieldParam'=>'style="width:50px" disabled="disabled"',
					'table_theadParam'=>' style="width:130px;"'
					);

		$columns['default_img'] = array(
					'title'=>'Изображение на обложку',
					'form_fieldType'=>'number',
					'form_fieldParam'=>'style="width:50px"',
					'table_hidden'=>true
					);

		$columns['max_width'] =	array(
					'title'=>'Ширина изображений',
					'form_fieldType'=>'number',
					'form_fieldParam'=>'style="width:50px"',
					'table_hidden'=>true
					);

		$columns['max_height'] =	array(
					'title'=>'Высота изображений',
					'form_fieldType'=>'number',
					'form_fieldParam'=>'style="width:50px"',
					'table_hidden'=>true
					);

		$columns['max_priority_side'] =	array(
					'title'=>'Приоритет стороны изображений',
					'form_fieldType'=>'radio',
					'values'=>array('w'=>'ширине', 'h'=>'высоте'),
					'table_hidden'=>true
					);

		$columns['thumb_width'] = array(
					'title'=>'Ширина миниатюр',
					'form_fieldType'=>'number',
					'form_fieldParam'=>'style="width:50px"',
					'table_hidden'=>true
					);

		$columns['thumb_height'] = array(
					'title'=>'Высота миниатюр',
					'form_fieldType'=>'number',
					'form_fieldParam'=>'style="width:50px"',
					'table_hidden'=>true
					);

		$columns['thumb_priority_side'] = array(
					'title'=>'Приоритет стороны миниатюры',
					'form_fieldType'=>'radio',
					'values'=>array('w'=>'ширине', 'h'=>'высоте'),
					'table_hidden'=>true
					);

		$columns['description_active'] = array(
					'title'=>'Подписи',
					'form_fieldType'=>'select',
					'values'=>array('0'=>'Отключить', '1'=>'Включить', 'global'=>'Как в глобальных настройках'),
					'table_hidden'=>true
					);

		$columns['align'] = array(
					'title'=>'Позиция подписи по горизонтале',
					'form_fieldType'=>'select',
					'values'=>array('left'=>'Слева', 'center'=>'В центре', 'right'=>'Справа', 'global'=>'Глобальные настройки'),
					'table_hidden'=>true
					);

		$columns['valign'] = array(
					'title'=>'Позиция подписи по вертикали',
					'form_fieldType'=>'select',
					'values'=>array('top'=>'Сверху', 'bottom'=>'Снизу', 'global'=>'Глобальные настройки'),
					'table_hidden'=>true
					);

		$columns['watermark'] = array(
					'title'=>'Водяной занк',
					'form_fieldType'=>'input',
					'table_hidden'=>true
					);

		$columns['watermark_type'] = array(
					'title'=>'Тип водяного знака',
					'form_fieldType'=>'radio',
					'values'=>array('image'=>'изображение', 'text'=>'текст'),
					'table_hidden'=>true
					);

		$columns['watermark_color'] = array(
					'title'=>'Цвет водяного знака',
					'form_fieldType'=>'input',
					'form_fieldParam'=>'class="colorpicker"',
					'table_hidden'=>true
					);

		$columns['html_param'] = array(
					'title'=>'HTML-параметры',
					'form_fieldType'=>'input',
					'table_hidden'=>true
					);

		$columns['date_update'] = array(
					'title'=>'Дата обновления',
					'form_fieldType'=>'date',
					'values'=>'unix_timestamp(now())',
					'form_mysql_mask'=>'FROM_UNIXTIME(`date_update`, "%d.%m.%Y %h:%i") `date_update`',
					'table_mysql_mask'=>'FROM_UNIXTIME(`date_update`, "%d.%m.%Y %h:%i") `date_update`',
					'form_hidden'=>true,
					'table_theadParam'=>' style="width:110px;"'
					);

		$this->tables['galleries'] = new CEditTable('seagull_galleries', $columns);
		$this->tables['galleries']->config = &$this->config;
		$this->tables['galleries']->setConfig('table_mysql_select', '`id`, `title`, `published`, `count_img`, FROM_UNIXTIME(`date_update`, "%d.%m.%Y %H:%i") `date_update`');
		$this->tables['galleries']->setConfig('table_param', 'id="t-galleries" class="b-table tpaginator" cellpadding="0" cellspacing="0"');
		$this->tables['galleries']->setConfig('tr_param', array('id'=>' id="row%id%" class="row-edit"'));
		$this->tables['galleries']->setConfig('tag_begin', '<p style="overflow:hidden">');
		$this->tables['galleries']->setConfig('label_begin', '<label style="width:200px; display:block; float:left">');
		$this->tables['galleries']->setConfig('paginatorRowsByPage', $this->config->backend->paginatorGal->rowsByPage);
		$this->tables['galleries']->setConfig('paginatorAdvLinks', $this->config->backend->paginatorGal->advLinks);

//------------------------------------------------
		$columns = array();
		$columns['id'] = array(
					'title'=>'ID',
					'form_hidden'=>true,
					'form_dontEdit'=>true,
					'table_hidden'=>true
					);

		$columns['imgs_select'] = array(
					'title'=>'',
					'form_dontEdit'=>true,
					'form_hidden'=>true,
					'non-exist'=>true,
					'table_td_content'=>array('id'=>'<input type="checkbox" name="imgs_select[]" value="%id%" />'),
					'table_theadParam'=>' style="width:20px;"'
					);

		$columns['image'] = array(
					'title'=>'Миниатюра',
					'table_theadParam'=>' style="width:90px; text-align:center"',
					'non-exist'=>true
					);

		$columns['gallery_id'] = array(
					'title'=>'ID галереи',
					'table_hidden'=>true
					);

		$columns['sort_id'] =	array(
					'title'=>'Сортировка',
					'table_theadParam'=>' style="width:10px"',
					'table_title_hidden'=>true
					);

		$columns['published'] =	array(
					'title'=>'Опубликован',
					'form_fieldType'=>'checkbox',
					'table_hidden'=>true
					);

		$columns['title'] =	array(
					'title'=>'Название',
					'form_fieldType'=>'input',
					'form_fieldParam'=>'style="width:80%"',
					'table_td_link2edit'=>true
					);

		$columns['description'] = array(
					'title'=>'Описание',
					'form_fieldType'=>'textarea',
					'form_fieldParam'=>'style="width:80%"',
					'table_hidden'=>true
					);

		$columns['alt'] = array(
					'title'=>'Описание в alt',
					'table_hidden'=>true
					);

		$columns['size'] =	array(
					'title'=>'Размер',
					'table_theadParam'=>' style="width:70px; text-align:right"'
					);

		$columns['file'] = array(
					'title'=>'Файл изображения',
					'form_dontEdit'=>true,
					'table_hidden'=>true
					);

		$columns['file_thumb'] = array(
					'title'=>'Файл миниатюры',
					'form_dontEdit'=>true,
					'table_hidden'=>true
					);

		$columns['thumb_width'] = array(
					'title'=>'Ширина миниатюр',
					'form_dontEdit'=>true,
					'table_hidden'=>true
					);

		$columns['thumb_height'] = array(
					'title'=>'Высота миниатюр',
					'form_dontEdit'=>true,
					'table_hidden'=>true
					);

		$columns['date_update'] = array(
					'title'=>'Дата обновления',
					'form_fieldType'=>'date',
					'values'=>'unix_timestamp(now())',
					'form_mysql_mask'=>'FROM_UNIXTIME(`date_update`, "%d.%m.%Y %H:%i") `date_update`',
					'form_hidden'=>true,
					'table_theadParam'=>' style="width:110px;"'
					);

		$this->tables['images'] = new CEditTable('seagull_images', $columns);
		$this->tables['images']->config = &$this->config;
		$this->tables['images']->setConfig('table_mysql_select', '`id`, `sort_id`, `published`, `size`, `title`, `description`, `file`, `file_thumb`, FROM_UNIXTIME(`date_update`, "%d.%m.%Y %H:%i") `date_update`');
		$this->tables['images']->setConfig('table_param', 'id="t-imgs" class="b-table tsort tpaginator" cellpadding="0" cellspacing="0"');
		$this->tables['images']->setConfig('tr_param', array('id'=>' id="img%id%" class="row-edit"'));
		$this->tables['images']->setConfig('sort_col', 'sort_id');
		$this->tables['images']->setConfig('group_id', 'gallery_id');
		$this->tables['images']->setConfig('label_begin', '<label style="width:100px; display:block; float:left">');
		$this->tables['images']->setConfig('paginatorRowsByPage', $this->config->backend->paginatorImg->rowsByPage);
		$this->tables['images']->setConfig('paginatorAdvLinks', $this->config->backend->paginatorImg->advLinks);
	}

	function handlePost() { //------------------------------------------------

		switch($_POST['cmd']) {
			case 'install':
				$this->install();
				$this->ph['title'] = 'Установка модуля';
				$this->file_tpl = 'install';
				$this->ph['msg'] = $this->msg->renderAll();
				return 1;
			break;

			case 'addGallery':
				$this->ph['form_gallery'] = $this->tables['galleries']->renderForm($_POST['itemID']);
				$this->ph['title'] = 'Добавление галереи';
				$this->file_tpl = 'addgallery';
			break;

			case 'editGallery':
				$this->init($_POST['itemID']);
				$this->ph['images_list'] = $this->renderImages($_POST['itemID'], NULL, $this->browser_view);
				$this->ph['paginator_links'] = $this->tables['images']->renderPaginator(1, array('param'=>'imgs'.$_POST['itemID'], 'query'=>'SELECT COUNT(*) FROM '.$this->tables['images']->table.' WHERE `gallery_id`='.$_POST['itemID'], 'limit'=>$this->config->backend->paginatorImg->rowsByPage, 'advLinks'=>$this->config->backend->paginatorImg->advLinks));
				$this->ph['gallery_id'] = $_POST['itemID'];
				$this->ph['gallery_title'] = $this->title;
				$this->file_tpl = 'editgallery';
			break;

			case 'configGallery':
				$this->ph['form_gallery'] = $this->tables['galleries']->renderForm($_POST['itemID']);
				$this->ph['gallery_id'] = $_POST['itemID'];
				$this->file_tpl = 'configgallery';
			break;

			case 'config':
				$this->ph['config'] = $this->config->renderForm($this->nameModule);
				$this->file_tpl = 'config';
			break;

			default:
				$this->ph['galleries_list'] = $this->tables['galleries']->renderTable(1, NULL, NULL, $this->config->backend->paginatorGal->rowsByPage);
				$this->ph['paginator_links'] = $this->tables['galleries']->renderPaginator(1, array('param'=>'gals', 'limit'=>$this->config->backend->paginatorGal->rowsByPage, 'advLinks'=>$this->config->backend->paginatorGal->advLinks));
				$this->file_tpl = 'main';
			break;
		}
		$this->ph['msgType'] = 'msg_'.$this->msg->getType();
		$this->ph['msg'] = $this->msg->render();
	}

	function init($gid) { //--------------------------------

		if ($gid) {
			$arr = retr_sql('SELECT * FROM '.$this->tables['galleries']->table." WHERE `id`='".$gid."'");

			if ($arr) {
				foreach ($arr as $key=>$item) {
					$this->{$key} = $item;
				}
				return $arr;
			}
		}

		return 0;
	}

	function getGalleryPath($gid) { //--------------------------------

		if ($gid) {
			$path = retr_sql('SELECT `path` FROM '.$this->tables['galleries']->table." WHERE `id`='".$gid."'");

			if ($path)
				return $path;
		}

		return 0;
	}

	function getImage($img_id, $where='') { //--------------------------------
		global $tables, $site, $config;

		$img = retr_sql('SELECT `id`, `gallery_id`, `title`, `description`, `alt`, `file`, `file_thumb`, `size`, FROM_UNIXTIME(`date_update`, "%d.%m.%Y %h:%i") `date_update` FROM '.$this->tables['images']->table.' WHERE `id`='.$img_id.' '.$where);

		$img['path'] = $this->config->galleryDir.'/'.$img['gallery_id'].'/'.$img['file'];
		$img['path_thumb'] = $this->config->galleryDir.'/thumb/'.$img['gallery_id'].'/'.$img['file_thumb'];
		$img['size'] = round($img['size']/1024).' КБ';

		if ($img)
			return($img);
		else
			return 0;
	}

	function saveGallery($aData) { //--------------------------------
		if (isset($aData['title']) and empty($aData['title']))
			$this->msg->setError('Введите <strong>Заголовок</strong>');

		$aData['sort_id'] = empty($aData['sort_id']) ? 1 : $aData['sort_id'];
		$aData['alias'] = empty($aData['alias']) ? translit2URL($aData['title']) : $aData['alias'];
		$aData['published'] = (isset($aData['published']) and $aData['published']==1) ? 1 : 0;
		$aData['need_auth'] = (isset($aData['need_auth']) and $aData['need_auth']==1) ? 1 : 0;
		$aData['max_width'] = empty($aData['max_width']) ? '0' : $aData['max_width'];
		$aData['max_height'] = empty($aData['max_height']) ? '0' : $aData['max_height'];
		$aData['max_priority_side'] = empty($aData['max_priority_side']) ? 'w' : $aData['max_priority_side'];
		$aData['thumb_width'] = empty($aData['thumb_width']) ? 0 : $aData['thumb_width'];
		$aData['thumb_height'] = empty($aData['thumb_height']) ? 0 : $aData['thumb_height'];
		$aData['thumb_priority_side'] = empty($aData['thumb_priority_side']) ? 'w' : $aData['thumb_priority_side'];
		$aData['count_img'] = 0;
		$aData['date_update'] = 1;

		if (!$this->msg->keep) {

			if (isset($aData['itemID']) and !empty($aData['itemID']) and is_numeric($aData['itemID'])) {
				$aData['path'] = $this->createPath($aData['itemID']);
				$aData['count_img'] = retr_sql("SELECT count(`id`) FROM ".$this->tables['images']->table." WHERE `gallery_id`=".$aData['itemID']);

				$r = $this->tables['galleries']->updateRow($aData['itemID'], $aData, DONT_UPDATE_ALL_FIELDS_OF_TABLE);

				$this->check_dirs($aData['path']);
				$this->check_dirs('/thumb'.$aData['path']);

				if ($r)
					$this->msg->setOk('Галерея сохранена');
				else
					$this->msg->setError('Ошибка сохранения галереи #'.$aData['itemID'].' в БД');
				return $aData['itemID'];
			}
			else {
				$tempname = $aData['title'];
				$aData['title'] = md5(rand(0, 100));
				$r = $this->tables['galleries']->insertRow($aData);

				if ($r) {
					$gallery_id = retr_sql('SELECT `id` FROM '.$this->tables['galleries']->table." WHERE `title`='".$aData['title']."'");

					$aData = array();
					$aData['path'] = $this->createPath($gallery_id);
					$aData['title'] = $tempname;
					$r = $this->tables['galleries']->updateRow($gallery_id, $aData, DONT_UPDATE_ALL_FIELDS_OF_TABLE);

					$this->check_dirs($aData['path']);
					$this->check_dirs('/thumb'.$aData['path']);

					if ($r) {
						$this->msg->setOk('Галерея добавлена');
						$this->msg->setVar('gallery_id', $gallery_id);
					}
					else {
						$this->msg->setError('Ошибка сохранения в БД');
					}
					return $gallery_id;
				}
				else {
					$errno = mysql_errno();
					if ($errno==1062)
						$this->msg->setError('Галерея с таким псевдонимом уже существует');
					else
						$this->msg->setError('Ошибка ('.$errno.') сохранения в БД');
				}
			}
		}
		return 0;
	}

	function addImages($gid, $arrImgs) { //--------------------------------

		$output = '';
		$msgs = '';
		$this->init($gid);

		$c = count($arrImgs['name']);

		for ($i = 0; $i < $c; $i++) {
			$img['name'] = $arrImgs['name'][$i];
			$img['type'] = $arrImgs['type'][$i];
			$img['tmp_name'] = $arrImgs['tmp_name'][$i];
			$img['error'] = $arrImgs['error'][$i];
			$img['size'] = $arrImgs['size'][$i];

			$imgData = $this->createImage($gid, $img);

			if ($imgData) {
				$thumbData = $this->createThumb($imgData['id'], array('name'=>$imgData['file']));

				if (!$thumbData)
					$this->msg->setError('Ошибка при создании миниатюры изображения #'.$imgData['id']);
			}
			else
				$this->msg->setError('Ошибка при обновлении изображения #'.$imgData['id']);

			if ($imgData) {
				$msgs .= $img['name'].'<br>';
				$output .= $this->renderAddedImage($imgData['id']);
			}
		}

		if (!$this->msg->hold) {
			$this->updateCountImages($gid);
			if ($c)
				$this->msg->setOk('Загруженные изображения:<br>'.$msgs);
			else
				$this->msg->setOk('Изображение "'.$msgs.'" загружено');

			return $output;
		}
		return 0;
	}

	function updateImage($aData, $imgfile=NULL) { //--------------------------------

		if (isset($aData['id']) and !empty($aData['id'])) {
			// $aData['sort_id'] = 1;

			// $aData['published'] = (isset($aData['published']) and $aData['published']==1) ? 1 : 0;

			if (!$this->msg->hold) {

				if ($imgfile) {
					$gid = retr_sql('SELECT `gallery_id` FROM '.$this->tables['images']->table.' WHERE `id`='.$aData['id']);
					$this->init($gid);

					if ($gid) {
						$imgData = $this->createImage($gid, $imgfile, $aData['id']);

						if ($imgData) {
							$thumbData = $this->createThumb($aData['id'], array('name'=>$imgData['file']));

							if ($thumbData)
								$imgData = array_merge($imgData, $thumbData);
							else
								$this->msg->setError('Ошибка при создании миниатюры изображения #'.$aData['id']);
						}
						else
							$this->msg->setError('Ошибка при обновлении изображения #'.$aData['id']);
					}
				}

				$imgData['title'] = $aData['title'];
				$imgData['description'] = $aData['description'];
				$imgData['alt'] = $aData['alt'];
				$imgData['date_update'] = time();
				unset($imgData['sort_id']);
				$r = $this->tables['images']->updateRow($aData['id'], $imgData, DONT_UPDATE_ALL_FIELDS_OF_TABLE);

				if ($r) {
					return $this->renderAddedImage($aData['id']);
				}
				else {
					$this->msg->setError('MySQL: Ошибка (#'.mysql_errno().') при выполнении запроса');
				}
			}
		}
		else
			$this->msg->setError('Отсутствует ID изображения');
		return 0;
	}

	function renderAddedImage($imgID) {

		$img = $this->getImage($imgID);

		$output = '<tr id="img'.$img['id'].'" class="b-row_green">
			<td><input class="img_select" type="checkbox" name="imgs_select[]" value="'.$img['id'].'" /></td>
			<td><img src="'.$img['path'].'" height="60" alt="image '.$img['id'].'" /></td>
			<td class="tsort__dragHandle"></td>
			<td class="col-edit"><span class="b-td__title">'.$img['title'].'</span><div class="b-td__desc">'.$img['description'].'</div></td>
			<td class="tr">'.$img['size'].'</td>
			<td>'.$img['date_update'].'</td>
		</tr>';

		return $output;
	}

	function resizeImage($sourceImage, $newpath, $newfilename, $newwidth=100, $newheight=100, $priority_side='w', $quality=93) { //--------------------------------
		$output = [];

        if ($sourceImage) {
			$pathinfo = pathinfo($sourceImage['name']);
			$ext = strtolower($pathinfo['extension']);

			list($width, $height) = getimagesize($sourceImage['tmp_name']);

			if ($width > $newwidth) {
				$ratio = $width/$height;

				if ($newwidth==0 or $priority_side==='h') {
					$newwidth = round($newheight * $ratio);
				}
				elseif ($newheight==0 or $priority_side==='w') {
					$newheight = round($newwidth / $ratio);
				}
				$img_create= imagecreatetruecolor($newwidth, $newheight);
				// imageantialias($img_create, true);

				switch ($ext) {
					case 'jpg': $img_source = imagecreatefromjpeg($sourceImage['tmp_name']); break;
					case 'gif': $img_source = imagecreatefromgif($sourceImage['tmp_name']);	break;
					case 'png': $img_source = imagecreatefrompng($sourceImage['tmp_name']);	break;
				}

				if (!$img_source)
					return 0;
				imagecopyresampled($img_create, $img_source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

				// $img_create = $this->create_watermark($img_create, 'www.natalia-fisher.ru', $_SERVER['DOCUMENT_ROOT'].'/assets/modules/seagullgallery/fonts/calibrib.ttf', 255, 255, 255, 20);

				// if ($this->config->watermark) {
				// 	add_log(ea($this->config->watermark, 1), 'watermark.log');
				// 	// $img_create = $this->createWatermark($img_create, $this->config->watermark);
				// }

				if (empty($newfilename))
					$newfilename = $sourceImage['name'].'.'.$ext;
				else
					$newfilename = $newfilename.'_'.$newwidth.'x'.$newheight.'.'.$ext;

				$output['width'] = $newwidth;
				$output['height'] = $newheight;
				$output['filename'] = $newfilename;

				$imgfile = $newpath.'/'.$newfilename;
				switch ($ext) {
					case 'jpg': $r = imagejpeg($img_create, $imgfile, $quality);	break;
					case 'gif': $r = imagegif($img_create, $imgfile, $quality);		break;
					case 'png': $r = imagepng($img_create, $imgfile, $quality);		break;
				}

				if ($r)
					return $output;
			}
			else {
				if (empty($newfilename))
					$newfilename = $sourceImage['name'].'.'.$ext;
				else
					$newfilename = $newfilename.'_'.$width.'x'.$height.'.'.$ext;

				$output['width'] = $width;
				$output['height'] = $height;
				$output['filename'] = $newfilename;

				$imgfile = $newpath.'/'.$newfilename;
				if (copy($sourceImage['tmp_name'], $imgfile))
					return $output;
			}
		}
		return 0;
	}

    function copyImages($fromGalID, $toGalID, $arrImgs) { //--------------------------------

        $this->init($toGalID);

        $strImgs = str_replace('img', '', implode(',', $arrImgs));
        $fromGalPath = SITE_ROOT.$this->config->galleryDir.'/'.$fromGalID.'/';

        $aImgsDB = sql2table('SELECT `id`, `file` FROM '.$this->tables['images']->table.' WHERE `id` IN ('.$strImgs.')');

        if ($aImgsDB) {
	        foreach ($aImgsDB as $img) {
	            $imgPath['name'] = $img['file'];
	            $imgPath['tmp_name'] = $fromGalPath.$img['file'];

	            $imgData = $this->createImage($toGalID, $imgPath);

                if ($imgData) {
                    $thumbData = $this->createThumb($imgData['id'], array('name'=>$imgData['file']));

                    if (!$thumbData)
                        $this->msg->setError('Ошибка при создании миниатюры изображения #'.$imgData['id']);
                }
                else {
                    $this->msg->setError('Ошибка при обновлении изображения #'.$imgData['id']);
                }
	        }

	        if ($imgData) {
                $r = $this->updateCountImages($toGalID);
		        return $r;
	        }
	    }

        return 0;
    }

//  Сохраняет изображение в БД и в папку галереи
//	gid - ID-галереи куда необходимо добавить изображение
//	source_img - данные о файле в виде массива (как при отправке через форму):
//		[name] => 5min.jpg
//		[type] => image/jpeg
//		[tmp_name] => /Applications/XAMPP/xamppfiles/temp/php09ajdv
//		[error] => 0
//		[size] => 341511
	function createImage($gid, $source_img, $update_imgID = NULL) { //--------------------------------

		if ($update_imgID) {
			$img_id = $update_imgID;
		}
		else {
			$randname = md5(rand(0, 1000));
			$r = run_sql('INSERT INTO '.$this->tables['images']->table." (`gallery_id`, `title`) VALUES (".$gid.", '".$randname."')");
			if ($r) {
				$img_id = retr_sql("SELECT `id` FROM ".$this->tables['images']->table." WHERE `gallery_id`=".$gid." AND `title`='".$randname."'");
				if (!$img_id) {
					$this->msg->setError('Ошибка при добавлении изображения в БД (#432)');
					return 0;
				}
			}
			else {
				$this->msg->setError('Ошибка при добавлении изображения в БД (#433)');
				return 0;
			}
		}

		if ($this->max_width==0)
			$this->max_width = $this->config->image->maxWidth;

		if ($this->max_height==0)
			$this->max_height = $this->config->image->maxHeight;

		$newGalleryPath = SITE_ROOT.$this->config->galleryDir.$this->path;
		$this->check_dirs($this->path);

		$filename = $img_id;
		$image = $this->resizeImage($source_img, $newGalleryPath, $filename, $this->max_width, $this->max_height, $this->max_priority_side);

		if ($filename) {
			$imgData = array();
			$imgData['sort_id'] = $this->count_img+1;
			$imgData['title'] = $imgData['description'] = '';
			$imgData['size'] = filesize(SITE_ROOT.$this->config->galleryDir.$this->path.'/'.$image['filename']);
			$imgData['file'] = $image['filename'];
			$imgData['date_update'] = time();
			// return $imgData;
			$r = $this->tables['images']->updateRow($img_id, $imgData, DONT_UPDATE_ALL_FIELDS_OF_TABLE);

			if ($r) {
				$imgData['id'] = $img_id;
				return $imgData;
			}
			else
				$this->msg->setError('Ошибка при обновлении данных изображения '.$image['filename'].' в БД');
		}
		else
			$this->msg->setError('Ошибка при копировании временного файла "'.$image['filename'].'"');
	}

	function createThumb($img_id, $source_img=NULL) { //--------------------------------

		if (!$source_img['name'])
			$source_img['name'] = retr_sql('SELECT `file` FROM '.$this->tables['images']->table." WHERE `id`=".$img_id);

		if ($this->thumb_width==0)
			$this->thumb_width = $this->config->thumb->maxWidth;

		if ($this->thumb_height==0)
			$this->thumb_height = $this->config->thumb->maxHeight;

		$thumbPath = SITE_ROOT.$this->config->galleryDir.'/thumb'.$this->path;

		$this->check_dirs('/thumb'.$this->path);

		$source_img['tmp_name'] = SITE_ROOT.$this->config->galleryDir.$this->path.'/'.$source_img['name'];
		$filename_thumb = $img_id;

		$thumb = $this->resizeImage($source_img, $thumbPath, $filename_thumb, $this->thumb_width, $this->thumb_height, $this->thumb_priority_side);

		if ($filename_thumb) {
			$thumbData = array();
			$thumbData['file_thumb'] = $thumb['filename'];
			$thumbData['thumb_width'] = $thumb['width'];
			$thumbData['thumb_height'] = $thumb['height'];
			$thumbData['date_update'] = time();

			$r = $this->tables['images']->updateRow($img_id, $thumbData, DONT_UPDATE_ALL_FIELDS_OF_TABLE);
			return $thumbData;
		}
		else
			$this->msg->setError('Ошибка при создании миниатюры "'.$filename_thumb.'"');
	}


	function resizeThumbs($gid) {

//		Получаю массив изображений
		$aImages = sql2table('SELECT `id`, `file` FROM '.$this->tables['images']->table." WHERE `gallery_id`=".$gid);

//		Беру новые значения для миниатюр
		$aGallery = retr_sql('SELECT `thumb_width`, `thumb_height`, `thumb_priority_side`, `path` FROM '.$this->tables['galleries']->table." WHERE `id`=$gid");
//
		if ($aGallery['thumb_width']==0)
			$aGallery['thumb_width'] = $this->config->thumb->maxWidth;

		if ($aGallery['thumb_height']==0)
			$aGallery['thumb_height'] = $this->config->thumb->maxHeight;

		$galPath = SITE_ROOT.$this->config->galleryDir.$aGallery['path'].'/';
		$thumbPath = SITE_ROOT.$this->config->galleryDir.'/thumb'.$aGallery['path'];
		$r = $this->check_dirs('/thumb'.$aGallery['path']);

		if ($r) {
//			Удаляю все миниатюры
			cleardir($thumbPath, CLEAR_FILES);

			foreach ($aImages as $img) {
				$source_img['tmp_name'] = $galPath.$img['file'];
				$source_img['name'] = $img['file'];

				if (is_dir($source_img['tmp_name']) and file_exists($source_img['tmp_name'])) {
					$source_img['tmp_name'] = $galPath.$img['id'].'.jpg';
					$source_img['name'] = $img['id'].'.jpg';
					if (!file_exists($source_img['tmp_name'])) {
						$this->msg->setError('Проблема с записью в БД изображения №'.$img['id']);
						continue;
					}
				}
// ea($source_img);
				$filename_thumb = $img['id'];
				$thumb = $this->resizeImage($source_img, $thumbPath, $filename_thumb, $aGallery['thumb_width'], $aGallery['thumb_height'], $aGallery['thumb_priority_side']);
	//			$filename_thumb = $this->resizeImage($dir_imgs, $img['file'], $thumbPath, $filename_thumb, $aGallery['thumb_width'], $aGallery['thumb_height'], $aGallery['thumb_priority_side'], 70);

				if ($thumb) {
					$update = array();
					$update['file_thumb'] = $thumb['filename'];
					$update['thumb_width'] = $thumb['width'];
					$update['thumb_height'] = $thumb['height'];
					$update['date_update'] = 1;
					if (!$this->tables['images']->updateRow($img['id'], $update, DONT_UPDATE_ALL_FIELDS_OF_TABLE))
						$this->msg->setError('Ошибка при сохранении данных миниатюры "'.$thumb['filename'].'"');
				}
				else {
					$this->msg->setError('Ошибка при создании миниатюры изображения "'.$thumb['filename'].'"');
				}
			}
		}

		if (!$this->msg->keep)
			return 1;

		return 0;
	}

	function createWatermark($main_img_obj, $watermark) {
		$width = imagesx($main_img_obj);
		$height = imagesy($main_img_obj);
		$rgba = hex2rgb($watermark->color);
		$rgba[3] = 127-(($rgba[3]-1)/2);
		$font = SITE_ROOT.$watermark->font;
		add_log(ea($rgba, 1), 'watermark.log');
		add_log($font, 'watermark.log');

		if ($watermark->type === 'text') {
			switch ($watermark->direction) {
				case 'xy':
					$angle =  -rad2deg(atan2((-$height),($width)));
					$text = ' '.$watermark->text.' ';
					$c = imagecolorallocatealpha($main_img_obj, 255, 255, 255, 80);
					$size = (($width+$height)/2)*2/strlen($text);
					$box  = imagettfbbox($size, $angle, $font, $text);
					$x = $width/2 - abs($box[4] - $box[0])/2;
					$y = $height/2 + abs($box[5] - $box[1])/2;

					$r = imagettftext($main_img_obj, $size, $angle, $x, $y, $c, $font, $text);
					add_log(ea($r, 1), 'watermark.log');
				break;

				case 'diagonal':
					$angle =  -rad2deg(atan2((-$height),($width)));
					$text = ' '.$watermark->text.' ';
					$c = imagecolorallocatealpha($main_img_obj, $rgba[0], $rgba[1], $rgba[2], $rgba[3]);
					$size = (($width+$height)/2)*2/strlen($text);
					$box  = imagettfbbox($size, $angle, $font, $text);
					$x = $width/2 - abs($box[4] - $box[0])/2;
					$y = $height/2 + abs($box[5] - $box[1])/2;

					imagettftext($main_img_obj, $size, $angle, $x, $y, $c, $font, $text);
				break;
			}
		}

		return $main_img_obj;
	}

/*function create_watermark( $main_img_obj, $text, $font, $r = 128, $g = 128, $b = 128, $alpha_level = 100 ) {
	$width = imagesx($main_img_obj);
	$height = imagesy($main_img_obj);
	$angle =  -rad2deg(atan2((-$height),($width)));

	$text = ' '.$text.' ';

	$c = imagecolorallocatealpha($main_img_obj, $r, $g, $b, $alpha_level);
	$size = (($width+$height)/2)*2/strlen($text);
	$box  = imagettfbbox ( $size, $angle, $font, $text );
	$x = $width/2 - abs($box[4] - $box[0])/2;
	$y = $height/2 + abs($box[5] - $box[1])/2;

	imagettftext($main_img_obj, $size, $angle, $x, $y, $c, $font, $text);
	return $main_img_obj;
}*/

	function createPath($gallery_id) { //--------------------------------
		global $tables, $config;

		$path = $str = '';
		if ($gallery_id) {
			$parent = retr_sql("SELECT `parent_id`, `id` FROM ".$this->tables['galleries']->table." WHERE `id`=".$gallery_id);
			if ($parent['parent_id']!==0)
				$str = $this->createPath($parent['parent_id']);

			$path = '/'.$str.$parent['id'];
		}
		return $path;
	}

	function check_dir($path, $make = 1, $check_write = false) { //--------------------------------
// echo 'check_dir:'.$path.'   ';
//	Проверка и создание папки галерей
		$r = file_exists($path);

		if ($make and !$r) {
			// echo 'before mkdir: '.substr(sprintf('%o', fileperms(SITE_ROOT.$this->config->galleryDir.'/thumb')), -4);
			$r = mkdir($path, 0777);

			if (!$r) {
				$this->msg->setError('Ошибка создания папки "'.$path.'"');
			}
		} else {
			if ($check_write === DIR_WRITE_TRUE) {
				$perms = substr(sprintf('%o', fileperms($path)), -4);

				if ($perms !== '0777' and $perms !== '0775') {
					// $r = chmod($path, 0777);
					$r = 0;
					$this->msg->setError('Необходимо изменить права доступа к папке '.$path);
				}
			}
		}

		return $r;
	}

	function check_dirs($path, $make=1) { //--------------------------------

//	Проверка и создание папки галерей
		$r = $this->check_dir(SITE_ROOT.$this->config->galleryDir, $make, DIR_WRITE_TRUE);
//	Проверка и создание папки миниатюр
		$r &= $this->check_dir(SITE_ROOT.$this->config->galleryDir.'/thumb', DIR_WRITE_TRUE);

//	Проверка и создание папки для пути
			if ($r) {
				$r = $this->check_dir(SITE_ROOT.$this->config->galleryDir.$path, $make);
			}

		return $r;
	}

	function updateCountImages($gid) { //--------------------------------

		$count = retr_sql("SELECT count(`id`) FROM ".$this->tables['images']->table." WHERE `gallery_id`=".$gid);
		$r = run_sql("UPDATE ".$this->tables['galleries']->table." SET `count_img`='".$count."' WHERE `id`=".$gid);

		if ($r)
			return $count;
		return -1;
	}

	function sortImages($str) { //--------------------------------

		$str = str_replace('img', '', $str);
		$arr = explode(',', $str);

		$c = count($arr);
		for ($i=0; $i<$c; $i++) {
			$case .= ' WHEN `id`='.$arr[$i].' THEN '.$i;
		}
		$r = run_sql("UPDATE ".$this->tables['images']->table." SET `sort_id`=CASE $case END WHERE `id` IN ($str) ORDER BY `sort_id`");

		if ($r)
			return($r);
		else
			return 0;
	}

	function renderImages($gid, $pageID=NULL, $typeView=BROWSER_FULL_TABLE) { //--------------------------------
		$path = $this->getGalleryPath($gid);

		if (isset($this->config->backend->paginatorImg->rowsByPage)) {
			$pageID = isset($pageID) ? $pageID : 1;
			$limit = ($pageID-1) * $this->config->backend->paginatorImg->rowsByPage.','.$this->config->backend->paginatorImg->rowsByPage;
		}

		$arr = $this->tables['images']->getRows($this->tables['images']->table_mysql_select, '`gallery_id`='.$gid, NULL, $limit);

		if ($arr) {
//		ea($arr);
			switch ($typeView) {
				case BROWSER_FULL_TABLE:
				case BROWSER_TBODY:
					foreach ($arr as $img) {
						$gDir = $this->config->galleryDir.$path.'/'.$img['file'];
						$output .= '<tr id="img'.$img['id'].'">
										<td><input class="img_select" type="checkbox" name="imgs_select[]" value="'.$img['id'].'" /></td>
										<td><a class="img-download" target="_blank" href="/assets/modules/seagullgallery/download.php?file='.$gDir.'"></a><img src="'.$this->config->galleryDir.'/thumb'.$path.'/'.$img['file_thumb'].'" height="60" title="'.$gDir.'" alt="image '.$img['id'].'" /></td>
										<td class="tsort__dragHandle"></td>
										<td class="col-edit"><span class="b-td__title">'.$img['title'].'</span><div class="b-td__desc">'.$img['description'].'</div></td>
										<td class="tr">'.round($img['size']/1024).' КБ</td>
										<td>'.$img['date_update'].'</td>
									</tr>';
					}

					if ($typeView === BROWSER_FULL_TABLE)
						$output = '<table '.$this->tables['images']->table_param.'>'.$this->tables['images']->renderTableHead().'<tbody>'.$output.'</tbody></table>';
				break;

				case BROWSER_GRID:
					foreach ($arr as $img) {
                        $gDir = $this->config->galleryDir.$path.'/'.$img['file'];
						$img['path'] = $this->config->galleryDir.$path.'/'.$img['file'];
						$img['path_thumb'] = $this->config->galleryDir.'/thumb'.$path.'/'.$img['file_thumb'];
						$img['title'] = htmlspecialchars($img['title']);
						$img['description'] = htmlspecialchars($img['description']);
						$img['html_param'] = $gal['html_param'];
						$img['desc_align'] = ' thumb__desc_'.(($gal['align'] === 'global') ? $this->config->description->align : $gal['align']);
						// $output .= $this->parseContent($tpl, $img);
						$output .= '<div id="img'.$img['id'].'" class="gallery-grid-item"><a class="img-download" target="_blank" href="/assets/modules/seagullgallery/download.php?file='.$gDir.'"></a><img src="'.$img['path_thumb'].'" title="'.$gDir.'"><input class="img_select" type="checkbox" name="imgs_select[]" value="'.$img['id'].'" /></div>';
					}
					$output = '<div class="gallery-grid">'.$output.'</div>';
				break;

				default:
				break;
			}
			return $output;
		}
		else {
			return('<table '.$this->tables['images']->table_param.'>'.$this->tables['images']->renderTableHead().'<tbody><tr id="no-imgs"><td colspan="6" style="text-align:center">Нет изображений</td></tr></tbody></table>');
		}


		return 0;
	}

	function renderGallery($gid=NULL, $view=NULL) {

		$output = '';

		if (!isset($view)) {
			$gal = retr_sql('SELECT `type_view`, `html_param`, `published`, `description_active`, `align`, `valign` FROM '.$this->tables['galleries']->table.' WHERE `id`='.$gid);
		}

		if ($gal['description_active'] === '1' or ($gal['description_active'] === 'global' and $this->config->description->active === '1')) {
			$tpl = $this->getTpl('frontend/img_desc_'.(($gal['valign'] === 'global') ? $this->config->description->valign : $gal['valign']));
		}
		else {
			$tpl = $this->getTpl('frontend/img');
		}

		if ($gal['published']) {
			$arr = sql2table('SELECT `id`, `gallery_id`, `title`, `description`, `alt`, `file`, `file_thumb` FROM '.$this->tables['images']->table." WHERE `published`='1' AND `gallery_id`=$gid ORDER BY `sort_id`");

		//	$arr = getImages();
			switch ($gal['type_view']) {
				case 'image_and_thumbs':
	/*				$output = '<script src="assets/modules/seagullgallery/js/site/jquery.ad-gallery.pack.js" type="text/javascript"></script>
								<script type="text/javascript">
								$(function() {
									var galleries = $(".ad-gallery").adGallery({slideshow:{enable: false}});
								});
								</script>';
	*/
					$output .= '<div id="gallery" class="ad-gallery">
						<div class="ad-image-wrapper"></div>
						<div class="ad-controls"></div>
						<div class="ad-nav">
							<div class="ad-thumbs">
								<ul class="ad-thumb-list">';

					foreach ($arr as $img) {
						$img['path'] = $this->config->galleryDir.'/'.$img['gallery_id'].'/'.$img['file'];
						$img['path_thumb'] = $this->config->galleryDir.'/thumb/'.$img['gallery_id'].'/'.$img['file_thumb'];
						$output .= '<li><a href="'.$img['path'].'"><img src="'.$img['path_thumb'].'" title="'.htmlspecialchars($img['title']).'" longdesc="'.$img['file'].'" alt="'.htmlspecialchars($img['description']).'" class="image'.$img['id'].'"></a></li>';
					}

					$output .= '</ul>
							</div>
						</div>
					</div>
					<div id="descriptions"></div>';
				break;

				case 'thumbs':
					foreach ($arr as $img) {
						$img['path'] = $this->config->galleryDir.'/'.$img['gallery_id'].'/'.$img['file'];
						$img['path_thumb'] = $this->config->galleryDir.'/thumb/'.$img['gallery_id'].'/'.$img['file_thumb'];
						$img['title'] = htmlspecialchars($img['title']);
						$img['description'] = htmlspecialchars($img['description']);
						$img['html_param'] = $gal['html_param'];
						$img['desc_align'] = ' thumb__desc_'.(($gal['align'] === 'global') ? $this->config->description->align : $gal['align']);
						$output .= $this->parseContent($tpl, $img);
					}
					$output = '<div class="gallery gallery-thumbs">'.$output.'</div>';

				break;

				case 'slider':
					$tpl = $this->getTpl('frontend/gallery-slider__img');

					foreach ($arr as $img) {
						$img['path'] = $this->config->galleryDir.'/'.$img['gallery_id'].'/'.$img['file'];
						$img['path_thumb'] = $this->config->galleryDir.'/thumb/'.$img['gallery_id'].'/'.$img['file_thumb'];
						$img['title'] = htmlspecialchars($img['title']);
						$img['description'] = htmlspecialchars($img['description']);
						$img['html_param'] = $gal['html_param'];
						$img['desc_align'] = ' thumb__desc_'.(($gal['align'] === 'global') ? $this->config->description->align : $gal['align']);
						$output .= $this->parseContent($tpl, $img);
					}
					$output = '<div class="gallery gallery-slider slides">'.$output.'</div>';

				break;

				case 'images':
					foreach ($arr as $img) {
						$img['path'] = $this->config->galleryDir.'/'.$img['gallery_id'].'/'.$img['file'];
						$img['path_thumb'] = $this->config->galleryDir.'/thumb/'.$img['gallery_id'].'/'.$img['file_thumb'];
						$output .= '<div class="image"><img class="image__img" src="'.$img['path'].'" title="'.htmlspecialchars($img['title']).'" alt="'.htmlspecialchars($img['description']).'" class="image'.$img['id'].'" /><span class="b-image__title">'.$img['title'].'</span><span class="b-image__desc">'.$img['description'].'</span></div>';
					}
	//				$output = '<ul class="b-seagullGallery">'.$output.'</ul>';
				break;
			}
		}
		else {
//			Галерея выключена
		}
		return $output;
	}

	function renderTableCKEditor() {
		$output = '';
        $arr = sql2table('SELECT `id`, `title`, `count_img` FROM '.$this->tables['galleries']->table.' ORDER BY `id`');

		if ($arr) {
			foreach ($arr as $gal) {
				$output .= '<tr class="row-edit"><td><input class="selectGallery" type="radio" name="selectGallery" value="'.$gal['id'].'"></td><td>'.$gal['id'].'</td><td>'.$gal['title'].'</td><td>'.$gal['count_img'].'</td></tr>';
			}

			return $output;
		}
	}

	function delGallery($gid) { //--------------------------------

		if ($this->delImages($gid)) {
			$path = $this->getGalleryPath($gid);
			$dir = SITE_ROOT.$this->config->galleryDir;
			cleardir($dir.$path, CLEAR_FILES);
			cleardir($dir.'/thumb'.$path, CLEAR_FILES);

			if (rmdir($dir.$path) and rmdir($dir.'/thumb'.$path))
				$r = run_sql('DELETE FROM '.$this->tables['galleries']->table.' WHERE `id`='.$gid);
			else
				$this->msg->setError('Ошибка при удалении папок "'.$path.'" галереи');

			if ($r)
				return 1;
		}
		else
			$this->msg->setError('Ошибка при удалении изображений');

		return 0;
	}

//	Параметром может быть либо ID галереи, тогда удаляются все изображения в галереии, либо массив с ID-шниками тех изображений, которые надо удалить.
	function delImages($param) { //--------------------------------
		global $msg;

		$dir = SITE_ROOT.$this->config->galleryDir;

		if (is_array($param)) {
			$str = implode(',', $param);

			$arr = sql2table('SELECT `id`, `gallery_id`, `file`, `file_thumb` FROM '.$this->tables['images']->table.' WHERE `id` IN ('.$str.')');
			if ($arr) {
				$gid = $arr[0]['gallery_id'];
				$arr_del = array();
				foreach ($arr as $item) {
					$del_file = $del_thumb = false;

					if (empty($item['file'])) {
						$del_file = true;
					}
					else {
						if (file_exists($dir.'/'.$item['gallery_id'].'/'.$item['file'])) {
							if (unlink($dir.'/'.$item['gallery_id'].'/'.$item['file']))
								$del_file = true;
							else
								$this->msg->setError('Файл изображения #'.$item['id'].' не удален');
						}
						else
							$del_file = true;
					}

					if (empty($item['file_thumb'])) {
						$del_thumb = true;
					}
					else {
						if (file_exists($dir.'/thumb/'.$item['gallery_id'].'/'.$item['file_thumb'])) {
							if (unlink($dir.'/thumb/'.$item['gallery_id'].'/'.$item['file_thumb']))
								$del_thumb = true;
							else
								$this->msg->setError('Файл изображения #'.$item['id'].' не удален');
						}
						else
							$del_thumb = true;
					}

					if ($del_file and $del_thumb)
						$arr_del[] = $item['id'];
				}

				if (count($arr_del)) {
					$str = implode(',', $arr_del);
					$r = run_sql("DELETE FROM ".$this->tables['images']->table." WHERE `id` IN (".$str.')');
					if ($r) {
						$this->updateCountImages($gid);
						$this->arr_del = $arr_del;
						return 1;
					}
				}
			}
		}
		elseif (is_numeric($param)) {
			$gid = $param;

			$arr = sql2table("SELECT `id`, `gallery_id`, `file`, `file_thumb` FROM ".$this->tables['images']->table." WHERE `gallery_id`=".$gid);
			foreach ($arr as $item) {
				if (!(unlink($dir.'/'.$item['gallery_id'].'/'.$item['file']) and unlink($dir.'/thumb/'.$item['gallery_id'].'/'.$item['file_thumb']))) {
					break;
				}
			}

			$r = run_sql("DELETE FROM ".$this->tables['images']->table." WHERE `gallery_id`=".$param);
		}
		$this->updateCountImages($gid);

		if ($r)
			return 1;
		return 0;
	}

	function clearTables() { //--------------------------------
		$r = run_sql('TRUNCATE TABLE '.$this->tables['galleries']->table);
		$r = run_sql('TRUNCATE TABLE '.$this->tables['images']->table);
		cleardir(SITE_ROOT.$this->config->galleryDir, CLEAR_ALL);
		if ($r)
			return 1;
		return 0;
	}

	function changeBrowserView() { //--------------------------------
        // ea($_POST);
        $aData['itemID'] = $_POST['itemID'];
        $aData['browser_view'] = $_POST['view'];
        $r = $this->tables['galleries']->updateRow($aData['itemID'], $aData, DONT_UPDATE_ALL_FIELDS_OF_TABLE);
        return $r;
	}

	function install() { //--------------------------------
		global $dbase;

		$r = true;
		$this->config->install();
		$groupID = $this->config->addModule($this->nameModule);

		$arr = array(
			array('name'=>'images','title'=>'Большие изображения','val'=>1),
			array('name'=>'thumbs','title'=>'Миниатюры','val'=>0),
			array('name'=>'image_and_thumbs','title'=>'Большое изображение и миниатюры','val'=>0),
			array('name'=>'slider','title'=>'Слайдер','val'=>0)
		);
		$r &= (boolean)$this->config->setVariable('galleryViewDefault', $arr, $this->nameModule, NULL, 'S', 'Вид отображения галереи', '240px');
		$r &= (boolean)$this->config->setVariable('galleryDir', '/assets/gallery', $this->nameModule, NULL, 'T', 'Папка для галерей', '240px');

		$r &= (boolean)$this->config->setVariable('position', NULL, $this->nameModule, NULL, 'FIELDSET', 'Подписи к изображениям');
		$r &= (boolean)$this->config->setVariable('active', 1, $this->nameModule, 'position', 'C', 'Включить подписи');
		$arr = array(
			array('name'=>'bottom','title'=>'Снизу','val'=>1),
			array('name'=>'top','title'=>'Сверху','val'=>0)
		);
		$r &= (boolean)$this->config->setVariable('valign', $arr, $this->nameModule, 'position', 'R', 'Позиция по вертикале');
		$arr = array(
			array('name'=>'left','title'=>'Слева','val'=>0),
			array('name'=>'center','title'=>'В центре','val'=>1),
			array('name'=>'right','title'=>'Справа','val'=>0)
		);
		$r &= (boolean)$this->config->setVariable('align', $arr, $this->nameModule, 'position', 'R', 'Позиция по горизонтале');

		$r &= (boolean)$this->config->setVariable('image', NULL, $this->nameModule, NULL, 'FIELDSET', 'Размеры изображений');
		$r &= (boolean)$this->config->setVariable('maxWidth', 800, $this->nameModule, 'image', 'N', 'Максимальная ширина');
		$r &= (boolean)$this->config->setVariable('maxHeight', 600, $this->nameModule, 'image', 'N', 'Максимальная высота');

		$r &= (boolean)$this->config->setVariable('thumb', NULL, $this->nameModule, NULL, 'FIELDSET', 'Размеры миниатюр');
		$r &= (boolean)$this->config->setVariable('maxWidth', 150, $this->nameModule, 'thumb', 'N', 'Максимальная ширина');
		$r &= (boolean)$this->config->setVariable('maxHeight', 100, $this->nameModule, 'thumb', 'N', 'Максимальная высота');

		$r &= (boolean)$this->config->setVariable('watermark', NULL, $this->nameModule, NULL, 'FIELDSET', 'Водяной знак');
		$r &= (boolean)$this->config->setVariable('text', '', $this->nameModule, 'watermark', 'T', 'Текст или URL-изображения', '240px');
		$r &= (boolean)$this->config->setVariable('font', '/assets/modules/seagullgallery/fonts/calibrib.ttf', $this->nameModule, 'watermark', 'T', 'URL к шрифту', '240px');
		$arr = array(
			array('name'=>'text','title'=>'Текстовая строка','val'=>1),
			array('name'=>'image','title'=>'Изображение','val'=>0)
		);
		$r &= (boolean)$this->config->setVariable('type', $arr, $this->nameModule, 'watermark', 'S', 'Тип водяного знака');
		$r &= (boolean)$this->config->setVariable('color', 'FFFFFFFF', $this->nameModule, 'watermark', 'T', 'Цвет водяного знака', '80px', NULL, 'colorpicker');
		$arr = array(
			array('name'=>'xy','title'=>'По координатам','val'=>1),
			array('name'=>'diagonal','title'=>'По диагонале','val'=>0)
		);
		$r &= (boolean)$this->config->setVariable('direction', $arr, $this->nameModule, 'watermark', 'S', 'Направление');
		$r &= (boolean)$this->config->setVariable('offsetX', '-3%', $this->nameModule, 'watermark', 'N', 'Сдвиг по X', '50px', 'Значение могут быть либо в "px" либо в "%". Если со знаком минус, то отсчет ведеться от правой стороны');
		$r &= (boolean)$this->config->setVariable('offsetY', '-3%', $this->nameModule, 'watermark', 'N', 'Сдвиг по Y', '50px', 'Значение могут быть либо в "px" либо в "%". Если со знаком минус, то отсчет ведеться от нижней стороны');

		$r &= (boolean)$this->config->setVariable('backend', NULL, $this->nameModule, NULL, 'FIELDSET', 'Административный сайт (то где ты сейчас находишься)');
//		$r &= (boolean)$this->config->setVariable('active', 1, $this->nameModule, 'paginatorBackend', 'C', 'Включить');

		$r &= (boolean)$this->config->setVariable('paginatorGal', NULL, $this->nameModule, 'backend', 'FIELDSET', 'Постраничная навигация галерей');
		$r &= (boolean)$this->config->setVariable('rowsByPage', '15', $this->nameModule, 'paginatorGal', 'N', 'Кол-во галерей на странице', '50px');
		$r &= (boolean)$this->config->setVariable('advLinks', '2', $this->nameModule, 'paginatorGal', 'N', 'Общее кол-во выводимых ссылок', '50px');

		$r &= (boolean)$this->config->setVariable('paginatorImg', NULL, $this->nameModule, 'backend', 'FIELDSET', 'Постраничная навигация изображений');
		$r &= (boolean)$this->config->setVariable('rowsByPage', '15', $this->nameModule, 'paginatorImg', 'N', 'Кол-во изображений на странице', '50px');
		$r &= (boolean)$this->config->setVariable('advLinks', '2', $this->nameModule, 'paginatorImg', 'N', 'Общее кол-во выводимых ссылок', '50px');

		$r &= (boolean)$this->config->setVariable('paginatorFrontend', NULL, $this->nameModule, NULL, 'FIELDSET', 'Постраничная навигация на сайте');
		$r &= (boolean)$this->config->setVariable('active', 1, $this->nameModule, 'paginatorFrontend', 'C', 'Включить');
		$r &= (boolean)$this->config->setVariable('rowsByPage', '15', $this->nameModule, 'paginatorFrontend', 'N', 'Кол-во записей на странице', '50px');
		$r &= (boolean)$this->config->setVariable('advLinks', '2', $this->nameModule, 'paginatorFrontend', 'N', 'Кол-во ссылок на соседние страницы', '50px');


		$r ? $this->msg->setOk('Переменные установлены') : $this->msg->setError('Ошибка при установки переменных');

		$r = retr_sql("SHOW TABLE STATUS FROM ".$dbase." LIKE '".$this->tables['galleries']->tablename."'");
		if (!$r) {
			$r = run_sql("CREATE TABLE ".$this->tables['galleries']->table." (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`parent_id` int(10) unsigned DEFAULT NULL,
					`sort_id` int(10) unsigned NOT NULL DEFAULT '1',
					`alias` varchar(255) NOT NULL,
					`path` varchar(255) NOT NULL,
					`title` varchar(255) DEFAULT NULL,
					`description` varchar(255) DEFAULT NULL,
					`description_active` enum('0','1','global') NOT NULL DEFAULT 'global',
					`align` enum('left','center','right','global') NOT NULL DEFAULT 'global',
					`valign` enum('top','bottom','global') NOT NULL DEFAULT 'global',
					`published` enum('0','1') NOT NULL DEFAULT '1',
					`type_view` enum('images','thumbs','image_and_thumbs','slider') NOT NULL DEFAULT 'thumbs',
					`count_img` int(10) unsigned DEFAULT NULL,
					`default_img` int(10) unsigned DEFAULT NULL,
					`show_desc_img` enum('0','1') NOT NULL DEFAULT '1',
					`max_width` int(4) unsigned NOT NULL DEFAULT '0',
					`max_height` int(4) unsigned NOT NULL DEFAULT '0',
					`max_priority_side` enum('w','h') NOT NULL DEFAULT 'w',
					`thumb_width` int(4) unsigned NOT NULL DEFAULT '0',
					`thumb_height` int(4) unsigned NOT NULL DEFAULT '0',
					`thumb_priority_side` enum('w','h') NOT NULL DEFAULT 'w',
					`watermark` varchar(255) DEFAULT NULL,
					`watermark_type` enum('text','image') DEFAULT NULL,
					`watermark_color` varchar(8) DEFAULT 'FFFFFFFF',
					`watermark_x` int(5) DEFAULT NULL,
					`watermark_y` int(5) DEFAULT NULL,
					`html_param` varchar(255) DEFAULT NULL,
					`date_update` int(10) unsigned NOT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=MYISAM DEFAULT CHARSET=utf8");
			if ($r)
				$this->msg->setOk('Таблица "'.$this->tables['galleries']->tablename.'" создана');
		} else
			$this->msg->setWarning('Таблица "'.$this->tables['galleries']->tablename.'" уже создана');

		$r = retr_sql("SHOW TABLE STATUS FROM ".$dbase." LIKE '".$this->tables['images']->tablename."'");
		if (!$r) {
			$r = run_sql("CREATE TABLE ".$this->tables['images']->table." (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`gallery_id` int(10) unsigned NOT NULL,
					`sort_id` int(11) NOT NULL DEFAULT '1',
					`published` enum('0','1') NOT NULL DEFAULT '1',
					`size` int(10) unsigned DEFAULT NULL,
					`title` varchar(255) NOT NULL,
					`description` text,
					`alt` varchar(255) DEFAULT NULL,
					`file` varchar(128) NOT NULL,
					`file_thumb` varchar(128) NOT NULL,
					`thumb_width` int(4) unsigned NOT NULL DEFAULT '0',
					`thumb_height` int(4) unsigned NOT NULL DEFAULT '0',
					`watermarked` enum('0','1') NOT NULL DEFAULT '0',
					`date_update` int(10) unsigned NOT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=MYISAM DEFAULT CHARSET=utf8");
			if ($r)
				$this->msg->setOk('Таблица "'.$this->tables['images']->tablename.'" создана');
		} else
			$this->msg->setWarning('Таблица "'.$this->tables['images']->tablename.'" уже создана');

		if (!$this->msg->keep) {
			return 1;
		}
		return 0;
	}
}
?>

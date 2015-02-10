<?php
/*! Copyright (C) 2014 Eundong. All rights reserved. */
/**
 * @class  magiccontentModel
 * @author Eundong (dh1024@gmail.com)
 * @brief model class of the magic content module
 */
class magiccontentModel extends magiccontent {
	function setTemporaryWidgetData($args) {
		$_SESSION['XE_MAGICCONTENT_WIDGET_ARGS'][$args->widget_sequence] = $args;
	}

	function getSetupData($widget_sequence, $is_complete = 1)
	{
		$args = new stdClass();
		$args->widget_sequence = $widget_sequence;
		$args->is_complete = $is_complete;

		$output = executeQuery('magiccontent.getMagicContentData', $args);
		if($output->data) {
			$return = unserialize($output->data->data);
			$return->is_complete = $is_complete;
			$return->widget_sequence = $widget_sequence;

			return $return;
		}
		else return false;
	}

	function getThumbnailByUrl($image_url, $width = 80, $height = 0, $thumbnail_type = '')
	{
		if(!$height) $height = $width;
		if(!in_array($thumbnail_type, array('crop','ratio')))
		{
			$config = $GLOBALS['__document_config__'];
			if(!$config)
			{
				$oDocumentModel = getModel('document');
				$config = $oDocumentModel->getDocumentConfig();
				$GLOBALS['__document_config__'] = $config;
			}
			$thumbnail_type = $config->thumbnail_type;
		}

		if(!is_dir('./files/thumbnails/magiccontent_thumbnail')) FileHandler::makeDir('./files/thumbnails/magiccontent_thumbnail');
		$thumbnail_path = sprintf('files/thumbnails/magiccontent_thumbnail/%s',base64_encode($image_url));
		$thumbnail_file = sprintf('%s%dx%d.%s.jpg', $thumbnail_path, $width, $height, $thumbnail_type);
		$thumbnail_url  = Context::getRequestUri().$thumbnail_file;

		if(file_exists($thumbnail_file))
		{
			if(filesize($thumbnail_file)<1) return false;
			else return $thumbnail_url;
		}

		$tmp_file = sprintf('./files/cache/tmp/%s', md5(rand(111111,999999).$image_url));
		if(!is_dir('./files/cache/tmp')) FileHandler::makeDir('./files/cache/tmp');
		if(!preg_match('/^(http|https):\/\//i',$image_url)) $image_url = Context::getRequestUri().$image_url;

		FileHandler::getRemoteFile($image_url, $tmp_file);
		if(!file_exists($tmp_file)) return false;
		else
		{
			list($_w, $_h, $_t, $_a) = @getimagesize($tmp_file);
			if(!in_array($_t, array(1, 2, 3, 6, 7, 8))) {
				FileHandler::writeFile($thumbnail_file, '','w');
				return false;
			}

			$source_file = $tmp_file;
		}
		
		$output = FileHandler::createImageFile($source_file, $thumbnail_file, $width, $height, 'jpg', $thumbnail_type);
		FileHandler::removeFile($source_file);

		if($output) return $thumbnail_url;
		else FileHandler::writeFile($thumbnail_file, '','w');

		return false;
	}
}
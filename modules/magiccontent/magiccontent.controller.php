<?php
/*! Copyright (C) 2014 Eundong. All rights reserved. */
/**
 * @class  magiccontentController
 * @author Eundong (dh1024@gmail.com)
 * @brief controller class of the magic content module
 */
class magiccontentController extends magiccontent {
	var $cache_path = './files/cache/widget_cache/';
	function triggerDisplayBefore(&$content)
	{
		if(Context::getResponseMethod()!='HTML') return new Object();

		$grant = Context::get('grant');
		$logged_info = Context::get('logged_info');
		if((!$grant || $grant->manager != 1) && $logged_info->is_admin != 'Y') return new Object();

		preg_replace_callback('!<img([^\>]*)widget=([^\>]*?)\>!is', array($this,'_setGrantByWidgetSequence'), $content);

		Context::loadFile('./modules/magiccontent/tpl/js/magiccontent_for_admin.js');
		Context::loadFile('./modules/magiccontent/tpl/css/magiccontent_for_admin.css');

		return new Object();
	}

	function _setGrantByWidgetSequence($matches) {
		$buff = trim($matches[0]);

		$oXmlParser = new XmlParser();
		$xml_doc = $oXmlParser->parse(trim($buff));

		$widget_sequence = $vars->widget_sequence;
		if($widget_sequence) $_SESSION['magic_content_grant'][$widget_sequence] = true;
	}

	function procMagiccontentSetup() {
		$req = Context::getRequestVars();
		$widget_sequence = $req->widget_sequence;
		$is_complete = $req->is_complete;

		unset($req->is_complete);
		unset($req->widget_sequence);
		unset($req->act);

		foreach($req as $key => $value) {
			if($value == '') unset($req->$key);
		}
		$serialize_data = serialize($req);

		$args = new stdClass();
		$args->data = $serialize_data;
		$args->widget_sequence = $widget_sequence;
		$args->is_complete = $is_complete;
		
		if($is_complete == 1) {
			$dargs = new stdClass();
			$dargs->widget_sequence = $widget_sequence;
			$dargs->is_complete = 0;
			$output = executeQuery('magiccontent.deleteMagicContentData', $dargs);
		}

		$oMagiccontentModel = &getModel('magiccontent');
		if($oMagiccontentModel->getSetupData($widget_sequence, $is_complete) === false) { 
			$args->data_srl = getNextSequence();
			$output = executeQuery('magiccontent.insertMagicContentData', $args);
		} else {
			$output = executeQuery('magiccontent.updateMagicContentData', $args);
		}

		$oCacheHandler = CacheHandler::getInstance('template');
		if($oCacheHandler->isSupport())
		{
			$key = 'widget_cache:' . $widget_sequence;
			$oCacheHandler->delete($key);
		}
		
		$lang_type = Context::getLangType();
		$cache_file = sprintf('%s%d.%s.cache', $this->cache_path, $widget_sequence, $lang_type);
		FileHandler::removeFile($cache_file);

		return new Object(0, 'success');
	}

	function procMagiccontentResetTempSetup() {
		$req = Context::getRequestVars();
		$widget_sequence = $req->widget_sequence;

		$args = new stdClass();
		$args->widget_sequence = $widget_sequence;
		$args->is_complete = 0;
		$output = executeQuery('magiccontent.deleteMagicContentData', $args);

		return new Object(0, 'success');
	}

	function procMagiccontentInsertDocument() {
		$logged_info = Context::get('logged_info');

		// setup variables
		$obj = Context::getRequestVars();
		$obj->module_srl = $obj->widget_sequence;
		$obj->commentStatus = 'N';

		settype($obj->title, "string");
		if($obj->title == '') $obj->title = cut_str(strip_tags($obj->content),20,'...');
		if($obj->title == '') $obj->title = 'Untitled';

		$oDocumentModel = getModel('document');
		$oDocumentController = getController('document');

		$obj->status = 'PUBLIC';

		$oDocument = $oDocumentModel->getDocument($obj->document_srl, $this->grant->manager);

		$is_update = false;
		if($oDocument->isExists() && $oDocument->document_srl == $obj->document_srl)
		{
			$is_update = true;
		}

		if($is_update)
		{
			if(!$oDocument->isGranted())
			{
				return new Object(-1,'msg_not_permitted');
			}
	
			$output = $oDocumentController->updateDocument($oDocument, $obj);
			$msg_code = 'success_updated';
		} else {
			$output = $oDocumentController->insertDocument($obj, false);
			$msg_code = 'success_registed';
			$obj->document_srl = $output->get('document_srl');
		}

		if(!$output->toBool())
		{
			return $output;
		}

		$this->add('mid', Context::get('mid'));
		$this->add('document_srl', $output->get('document_srl'));
		$this->add('title', $obj->title);

		$this->setMessage($msg_code);
	}
}
<?php
/*! Copyright (C) 2014 Eundong. All rights reserved. */
/**
 * @class  magiccontentView
 * @author Eundong (dh1024@gmail.com)
 * @brief view class of the magic content module
 */

class magiccontentView extends magiccontent {
	function init()
	{
		$this->setTemplatePath($this->module_path.'tpl');
	}

	function dispMagiccontentSetup()
	{
		$widget_args = $_SESSION['XE_MAGICCONTENT_WIDGET_ARGS'][Context::get('widget_sequence')];
		$widget_args->is_complete = 0;
		Context::set('admin_bar','false');

		$logged_info = Context::get('logged_info');

		if($_SESSION['magic_content_grant'][$widget_args->widget_sequence] != true && $logged_info->is_admin != 'Y') return $this->stop('msg_not_permitted');
		
		$widget_structure = new stdClass();
		if($widget_args->tab_type != 'none') $widget_structure->tab = true;
		else $widget_structure->tab = false;

		if($widget_structure->tab == true) {
			$widget_structure->tabs = $widget_args->module_srls_info;
		} else {
			$tab = new stdClass();
			$tab->module_srl = 0;
			$tab->browser_title = '단일';
			$widget_structure->tabs = array($tab);
		}

		$widget_structure->list_count = $widget_args->list_count;
		$widget_structure->page_count = $widget_args->page_count;
		$widget_structure->widgetContentItem = $widget_args->widgetContentItem;

		$oWidgetController = &getController('widget');
		$widget_args->widget_cache = 0;
		$output = $oWidgetController->execute($widget_args->widget, $widget_args, false, false);

		Context::set('widget_structure', $widget_structure);
		Context::set('widget_result', $output);

		$oDocumentModel = &getModel('document');
		Context::set('oDocumentModel', $oDocumentModel);

		$oModuleModel = &getModel('module');
		Context::set('oModuleModel', $oModuleModel);

		$oMagiccontentModel = &getModel('magiccontent');
		$setup_data = $oMagiccontentModel->getSetupData($widget_args->widget_sequence, 0);
		if($setup_data === false) $setup_data = $oMagiccontentModel->getSetupData($widget_args->widget_sequence, 1);

		Context::set('setup_data', get_object_vars($setup_data));
		$this->setLayoutFile('default_layout');
		$this->setTemplateFile('setup');
	}

	function dispMagiccontentInsertDocument() {
		$widget_sequence = Context::get('widget_sequence');
		$document_srl = (int)Context::get('document_srl');
		if(!$document_srl) $document_srl = 0;

		$oDocumentModel = &getModel('document');
		$oDocument = $oDocumentModel->getDocument($document_srl, true);
		$oDocument->module_srl = $widget_sequence;

		Context::set('oDocument', $oDocument);

		Context::addJsFilter($this->module_path.'tpl/filter', 'insert.xml');

		$this->setLayoutFile('default_layout');
		$this->setTemplateFile('insert_document');
	}

	function dispMagiccontentSearchDocument() {
		$oDocumentAdminView = &getAdminView('document');
		$oDocumentAdminView->dispDocumentAdminList();

		$this->setLayoutFile('default_layout');
		$this->setTemplateFile('search_document');
	}

	function dispMagiccontentGetNewSequence() {
		$this->add('widget_sequence', getNextSequence());
	}
}
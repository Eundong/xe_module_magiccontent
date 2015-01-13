<?php
/*! Copyright (C) 2014 Eundong. All rights reserved. */
/**
 * @class  magiccontent
 * @author Eundong (dh1024@gmail.com)
 * @brief high class of the magic content module
 */
class magiccontent extends ModuleObject
{
	private $triggers = array(
		array( 'display', 'magiccontent', 'controller', 'triggerDisplayBefore', 'before')
	);

	function moduleInstall()
	{
		$oModuleController = getController('module');
		foreach ($this->triggers as $trigger)
		{
			$oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
		}

		return new Object();
	}

	function moduleUninstall()
	{
		$oModuleController = getController('module');
		foreach ($this->triggers as $trigger)
		{
			$oModuleController->deleteTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
		}

		return new Object();
	}

	function checkUpdate()
	{
		$oModuleModel = getModel('module');
		foreach ($this->triggers as $trigger)
		{
			if (!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]))
			{
				return true;
			}
		}

		return false;
	}

	function moduleUpdate()
	{
		$oModuleModel = getModel('module');
		$oModuleController = getController('module');
		foreach ($this->triggers as $trigger)
		{
			if (!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]))
			{
				$oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
			}
		}

		return new Object();
	}
}

<?php
/**
 * @package modules.theme
 */
class theme_Setup extends object_InitDataSetup
{
	public function install()
	{
		$tms = theme_ModuleService::getInstance();
		$tms->initPaths();
		
		$this->executeModuleScript('init.xml');
	}

	/**
	 * @return string[]
	 */
	public function getRequiredPackages()
	{
		return array('modules_website');
	}
}
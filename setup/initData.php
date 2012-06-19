<?php
/**
 * @package modules.theme.setup
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
		// Return an array of packages name if the data you are inserting in
		// this file depend on the data of other packages.
		// Example:
		// return array('modules_website', 'modules_users');
		return array('modules_website');	
	}
}
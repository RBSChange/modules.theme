<?php
/**
 * theme_patch_0350
 * @package modules.theme
 */
class theme_patch_0350 extends patch_BasePatch
{
 
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		$this->execChangeCommand('compile-locales', array('theme'));
		$this->execChangeCommand('compile-editors-config');

		$this->log('add editableblocks field...');
		$newPath = f_util_FileUtils::buildWebeditPath('modules/theme/persistentdocument/pagetemplate.xml');
		$newModel = generator_PersistentModel::loadModelFromString(f_util_FileUtils::read($newPath), 'theme', 'pagetemplate');
		$newProp = $newModel->getPropertyByName('editableblocks');
		f_persistentdocument_PersistentProvider::getInstance()->addProperty('theme', 'pagetemplate', $newProp);

	}
}
<?php
/**
 * theme_PagetemplatedeclinationScriptDocumentElement
 * @package modules.theme.persistentdocument.import
 */
class theme_PagetemplatedeclinationScriptDocumentElement extends theme_PagetemplateScriptDocumentElement
{
	/**
	 * @return theme_persistentdocument_pagetemplatedeclination
	 */
	protected function initPersistentDocument()
	{
		return theme_PagetemplatedeclinationService::getInstance()->getNewDocumentInstance();
	}
	
	/**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_theme/pagetemplatedeclination');
	}
	
}
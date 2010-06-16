<?php
/**
 * theme_ThemeScriptDocumentElement
 * @package modules.theme.persistentdocument.import
 */
class theme_ThemeScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return theme_persistentdocument_theme
     */
    protected function initPersistentDocument()
    {
    	return theme_ThemeService::getInstance()->getNewDocumentInstance();
    }
    
    /**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_theme/theme');
	}
}
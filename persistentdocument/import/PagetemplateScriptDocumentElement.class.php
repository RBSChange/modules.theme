<?php
/**
 * theme_PagetemplateScriptDocumentElement
 * @package modules.theme.persistentdocument.import
 */
class theme_PagetemplateScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return theme_persistentdocument_pagetemplate
     */
    protected function initPersistentDocument()
    {
    	return theme_PagetemplateService::getInstance()->getNewDocumentInstance();
    }
    
    /**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_theme/pagetemplate');
	}
	
	/**
	 * @see import_ScriptDocumentElement::getParentInTree()
	 *
	 * @return f_persistentdocument_PersistentDocument
	 */
	protected function getParentInTree()
	{
		return null;
	}
}
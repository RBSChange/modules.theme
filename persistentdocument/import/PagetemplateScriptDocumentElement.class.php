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
	

	/**
	 * @return theme_persistentdocument_theme
	 */
	private function getTheme()
	{
		$doc = $this->getParentDocument();
		if ($doc && $doc->getPersistentDocument() instanceof theme_persistentdocument_theme)
		{
			return $doc->getPersistentDocument();
		}
		return null;
	}
	
	/**
	 * @see import_ScriptDocumentElement::getDocumentProperties()
	 *
	 * @return array
	 */
	protected function getDocumentProperties()
	{
		$properties = parent::getDocumentProperties();
		$theme = $this->getTheme();
		if (isset($properties['byCodename']))
		{
			$codename = $properties['byCodename'];
		}
		else
		{
			$codename = $this->getPersistentDocument()->getCodename();
			if ($codename)
			{
				list(, $codename) = explode('/', $codename);
			}
		}
		
		if ($theme && !isset($properties['label']) && $codename)
		{
			$properties['label'] = '&themes.'.$theme->getCodename().'.templates.'.ucfirst($codename).';';
		}
		return $properties;
	}	
}
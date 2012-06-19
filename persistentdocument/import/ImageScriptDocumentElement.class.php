<?php
/**
 * theme_ImageScriptDocumentElement
 * @package modules.theme.persistentdocument.import
 */
class theme_ImageScriptDocumentElement extends import_ScriptDocumentElement
{
	/**
	 * @return theme_persistentdocument_image
	 */
	protected function initPersistentDocument()
	{
		return theme_ImageService::getInstance()->getNewDocumentInstance();
	}
	
	/**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_theme/image');
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
		if ($theme)
		{
			$properties['themeid'] = $theme->getId();
		}
		return $properties;
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
	 * @see import_ScriptDocumentElement::getParentInTree()
	 *
	 * @return f_persistentdocument_PersistentDocument
	 */
	protected function getParentInTree()
	{
		return null;
	}
}
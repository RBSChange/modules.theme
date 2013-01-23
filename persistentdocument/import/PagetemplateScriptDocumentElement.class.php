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
		$doc = $this;
		while ($doc instanceof import_ScriptDocumentElement)
		{
			if ($doc->getPersistentDocument() instanceof theme_persistentdocument_theme)
			{
				return $doc->getPersistentDocument();
			}
			$doc = $doc->getParentDocument();
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
		$codename = (isset($properties['codename'])) ? $properties['codename'] : $this->getPersistentDocument()->getCodename();
		list(, $codename) = explode('/', $codename);
		
		if ($theme && !isset($properties['label']) && $codename)
		{
			$properties['label'] = 't.'.$theme->getCodename().'.templates.'.ucfirst($codename);
		}
		return $properties;
	}	
	
	public function endProcess()
	{
		$children = $this->script->getChildren($this);
		if (count($children))
		{
			$blockScripts = array();
			foreach ($children as $child)
			{
				if ($child instanceof theme_ScriptTemplateblockElement)
				{
					$blockScripts[] = $child;
				}
			}
				
			if (count($blockScripts) > 0)
			{
				$declination = $this->getPersistentDocument();
				$this->updateConfiguredBlocks($declination, $blockScripts);
				$declination->save();
			}
		}
	}
	
	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $declination
	 * @param theme_ScriptTemplateblockElement[] $blockScripts
	 */
	protected function updateConfiguredBlocks($declination, $blockScripts)
	{
		$blocks = $declination->getConfiguredBlocks();
		foreach ($blockScripts as $blockScript)
		{
			/* @var $blockScript theme_ScriptTemplateblockElement */
			list($editname, $infos) = $blockScript->getBlockInfos();
			$blocks[$editname] = $infos;
		}
		$declination->setConfiguredBlocks($blocks);
	}
}
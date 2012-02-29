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
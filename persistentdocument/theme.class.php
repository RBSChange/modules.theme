<?php
/**
 * Class where to put your custom methods for document theme_persistentdocument_theme
 * @package modules.theme.persistentdocument
 */
class theme_persistentdocument_theme extends theme_persistentdocument_themebase 
{
	/**
	 * @param string $moduleName
	 * @param string $treeType
	 * @param array<string, string> $nodeAttributes
	 */
	protected function addTreeAttributes($moduleName, $treeType, &$nodeAttributes)
	{
		$thumbnail = $this->getThumbnail();
		if ($thumbnail)
		{		
			$nodeAttributes['hasPreviewImage'] = true;
			if ($treeType == 'wlist')
			{
	    		$nodeAttributes['thumbnailsrc'] = $thumbnail->getUISrc();
			}
		}
	}
	
	/**
	 * @param string $actionType
	 * @param array $formProperties
	 */
//	public function addFormProperties($propertiesNames, &$formProperties)
//	{	
//	}

	/**
	 * @return theme_persistentdocument_image
	 */
	public function getThumbnail()
	{
		if ($this->getPagetemplateCount())
		{
			return $this->getPagetemplate(0)->getThumbnail();
		}
		return null;
	}
}
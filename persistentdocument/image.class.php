<?php
/**
 * Class where to put your custom methods for document theme_persistentdocument_image
 * @package modules.theme.persistentdocument
 */
class theme_persistentdocument_image extends theme_persistentdocument_imagebase 
{
	/**
	 * @param string $moduleName
	 * @param string $treeType
	 * @param array<string, string> $nodeAttributes
	 */
//	protected function addTreeAttributes($moduleName, $treeType, &$nodeAttributes)
//	{
//	}
	
	/**
	 * @param string $actionType
	 * @param array $formProperties
	 */
//	public function addFormProperties($propertiesNames, &$formProperties)
//	{	
//	}

	/**
	 * @return string
	 */
	public function getUISrc()
	{
		return LinkHelper::getUIRessourceLink($this->getCodename())->getUrl();
	}
}
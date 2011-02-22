<?php
/**
 * Class where to put your custom methods for document theme_persistentdocument_image
 * @package modules.theme.persistentdocument
 */
class theme_persistentdocument_image extends theme_persistentdocument_imagebase 
{
	/**
	 * @return string
	 */
	public function getUISrc()
	{
		return LinkHelper::getUIRessourceLink($this->getCodename())->getUrl();
	}
}
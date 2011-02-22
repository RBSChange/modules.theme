<?php
/**
 * Class where to put your custom methods for document theme_persistentdocument_theme
 * @package modules.theme.persistentdocument
 */
class theme_persistentdocument_theme extends theme_persistentdocument_themebase 
{
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
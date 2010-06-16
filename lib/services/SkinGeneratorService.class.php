<?php
/**
 * theme_SkinGeneratorService
 * @package modules.theme
 */
class theme_SkinGeneratorService extends BaseService
{
	/**
	 * theme_SkinGeneratorService instance.
	 *
	 * @var theme_SkinGeneratorService
	 */
	private static $instance = null;


	/**
	 * Gets a theme_SkinGeneratorService instance.
	 *
	 * @return theme_SkinGeneratorService
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}	
	
	/**
	 * @param theme_persistentdocument_theme $theme
	 */
	public function updateSkinVars($theme)
	{
		$skinVars = $this->getThemeVars($theme);
		$result = theme_PagetemplateService::getInstance()->createQuery()
			->setProjection(Projections::count('id', 'nbUseStd'))
			->add(Restrictions::eq('theme', $theme))
			->add(Restrictions::eq('useprojectcss' , true))
			->find();
		if ($result[0]['nbUseStd'])
		{
			$skinVars = array_merge($skinVars, $this->getStandardVars());
		}
		
		$skinVarsPath = f_util_FileUtils::buildWebeditPath('themes', $theme->getCodename(), 'skin', 'skin.xml');		
		if (!file_exists($skinVarsPath))
		{
			$content = '<?xml version="1.0" encoding="UTF-8"?><sections />';
			f_util_FileUtils::writeAndCreateContainer($skinVarsPath, $content);
		}
		
		$skinLocalPath = f_util_FileUtils::buildWebeditPath('themes', $theme->getCodename(), 'locale', 'skin.xml');
		if (!file_exists($skinLocalPath))
		{
			$content = '<?xml version="1.0" encoding="utf-8"?><localization />';
			f_util_FileUtils::writeAndCreateContainer($skinLocalPath, $content);
		}		
		
		$undefinedSection = null;
		$modifiedLocal = false;
		$modified = false;
		$skinDoc = f_util_DOMUtils::fromPath($skinVarsPath);
		$skinLocalDoc = f_util_DOMUtils::fromPath($skinLocalPath);
		foreach ($skinVars as $varName => $initialvalue) 
		{
			$varNode = $skinDoc->findUnique('//field[@name="'.$varName.'"]');
			if (!$varNode)
			{
				if ($undefinedSection === null)
				{
					$modified = true;
					$undefinedSection = $skinDoc->documentElement->appendChild($skinDoc->createElement('section'));
					$undefinedSection->setAttribute('name', 'undefined-vars');
				}
				
				$varNode = $undefinedSection->appendChild($skinDoc->createElement('field'));
				$varNode->setAttribute('name', $varName);
				$varNode->setAttribute('type', 'text');
				$varNode->setAttribute('initialvalue', $initialvalue);
				
				if (!$skinLocalDoc->findUnique('/localization/entity[@id="'.$varName.'"]'))
				{
					$modifiedLocal = true;
					$localNode = $skinLocalDoc->documentElement->appendChild($skinLocalDoc->createElement('entity'));
					$localNode->setAttribute('id', $varName);
					foreach (array('fr', 'en') as $lang) 
					{
						$langNode = $localNode->appendChild($skinLocalDoc->createElement('locale'));
						$langNode->setAttribute('lang', $lang);
						$langNode->appendChild($skinLocalDoc->createTextNode('_' . $varName));
					}
					$varNode->setAttribute('hidehelp', 'true'); 
				}
				else if (!$skinLocalDoc->findUnique('/localization/entity[@id="'.$varName.'-help"]'))
				{
					$varNode->setAttribute('hidehelp', 'true'); 
				}
			}
			else
			{
				if ($varNode->getAttribute('initialvalue') != $initialvalue)
				{
					$modified = true;
					$varNode->setAttribute('initialvalue', $initialvalue);
				}
				if (!$varNode->hasAttribute('type'))
				{
					$modified = true;
					$varNode->setAttribute('type', 'text');
				}
				if ($varNode->hasAttribute('allowfile') && $varNode->getAttribute('type') != 'imagecss')
				{
					$modified = true;
					$varNode->setAttribute('type', 'imagecss');
					$varNode->setAttribute('mediafoldername', 'Inbox_' .$theme->getCodename());
				}
			}
		}
		
		if ($modified)
		{
			
			echo "Update: $skinVarsPath\n";
			f_util_FileUtils::mkdir(dirname($skinVarsPath));
			$skinDoc->save($skinVarsPath);
		}
		
		if ($modifiedLocal)
		{
			echo "Update: $skinLocalPath\n";
			f_util_FileUtils::mkdir(dirname($skinLocalPath));
			$skinLocalDoc->save($skinLocalPath);
		}
	}
	
	/**
	 * @param theme_persistentdocument_theme $theme
	 */	
	private function getThemeVars($theme)
	{
		$allThemeSkinVars = array();
		foreach ($theme->getCssArray() as $style) 
		{
			$skinRefs =  theme_CssService::getInstance()->extractSkinVars($style);
			if (count($skinRefs))
			{
				$allThemeSkinVars = array_merge($allThemeSkinVars, $skinRefs);
			}
		}
		return 	$allThemeSkinVars;
	}
	
	private function getStandardVars()
	{
		$allGenericSkin = array();
		
		$styleIds = theme_PagetemplateService::getInstance()->getStandardScreenStyleIds();
		foreach ($styleIds as $styleId) 
		{
			$stylePath = StyleService::getInstance()->getSourceLocation($styleId);
			$skinRefs = theme_CssService::getInstance()->extractSkinVarsByFile($stylePath);
			if (count($skinRefs))
			{
				$allGenericSkin = array_merge($allGenericSkin, $skinRefs);
			}
		}
		return 	$allGenericSkin;
	}
	
}
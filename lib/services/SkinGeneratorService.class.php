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
		$codeName = $theme->getCodename();
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
		
		$skinVarsPath = f_util_FileUtils::buildWebeditPath('themes', $codeName, 'skin', 'skin.xml');		
		if (!file_exists($skinVarsPath))
		{
			$content = '<?xml version="1.0" encoding="UTF-8"?><sections />';
			f_util_FileUtils::writeAndCreateContainer($skinVarsPath, $content);
		}
		
		$ls = LocaleService::getInstance();
		$modified = false;
		$undefinedSection = null;
		$skinDoc = f_util_DOMUtils::fromPath($skinVarsPath);
		$keysInfos = array();		
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
				$varNode->setAttribute('hidehelp', 'true');			
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
					$varNode->setAttribute('mediafoldername', 'Inbox_' .$codeName);
				}
			}
				
			// [id => [text, format]]
			$keysInfos[strtolower($varName)] = array('_' . $varName, 'text');
		}
		
		$lcid = $ls->getLCID('fr');
		$baseKey = strtolower('t.'.$codeName.'.skin');
		//Framework::fatal(__METHOD__ . ' ' . var_export($keysInfos, true));
		$ls->updatePackage($baseKey, array($lcid => $keysInfos), false, true);
		
		if ($modified)
		{
			echo "Update: $skinVarsPath\n";
			f_util_FileUtils::mkdir(dirname($skinVarsPath));
			$skinDoc->save($skinVarsPath);
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
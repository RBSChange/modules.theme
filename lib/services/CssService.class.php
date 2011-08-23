<?php
/**
 * theme_CssService
 * @package modules.theme
 */
class theme_CssService extends f_persistentdocument_DocumentService
{
	/**
	 * @var theme_CssService
	 */
	private static $instance;

	/**
	 * @return theme_CssService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @return theme_persistentdocument_css
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_theme/css');
	}

	/**
	 * Create a query based on 'modules_theme/css' model.
	 * Return document that are instance of modules_theme/css,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_theme/css');
	}
	
	/**
	 * Create a query based on 'modules_theme/css' model.
	 * Only documents that are strictly instance of modules_theme/css
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_theme/css', false);
	}
	
	/**
	 * @param string $codeName
	 * @return theme_persistentdocument_css
	 */
	public function getByCodeName($codeName)
	{
		return $this->createQuery()->add(Restrictions::eq('codename', $codeName))->findUnique();
	}
	
	/**
	 * @param theme_persistentdocument_theme $theme
	 */
	public function refreshByFiles($theme)
	{
		$paths = FileResolver::getInstance()
				->setPackageName('themes_' . $theme->getCodename())
				->setDirectory('style')
				->getPaths('');	

		$stylesPath = array();
		if (is_array($paths) && count($paths))
		{
			foreach ($paths as $path) 
			{
				$dir = new DirectoryIterator($path);
				foreach ($dir as $fileinfo) 
				{
				    if ($fileinfo->isFile()) 
				    {
				    	$styleParts = explode('.', $fileinfo->getFilename());
				    	if (count($styleParts) == 2 && $styleParts[1] == 'css')
				    	{
				    		$stylesPath[$styleParts[0]] = $fileinfo->getPathname();
				    	}
				    }
				}
			}
		}
		
		$styles = array();
		foreach ($stylesPath as $baseName => $path) 
		{
			$codeName = 'themes.' . $theme->getCodename() . '.' . $baseName;
			$style = $this->getByCodeName($codeName);
			if (!$style)
			{
				$style = $this->getNewDocumentInstance();		
				$style->setCodename($codeName);
				$style->setLabel($baseName);
				$style->setThemeid($theme->getId());
				$style->setProjectpath('themes/' . $theme->getCodename() . '/style/' . $baseName .'.css');
				$style->save();
				$theme->addCss($style);
			}
			$styles[] = $style->getId();
		}
		
		$toDelete = array();
		foreach ($theme->getCssArray() as $style) 
		{
			if (!in_array($style->getId(), $styles))
			{
				$toDelete[] =  $style->getId();
				$theme->removeCss($style);	
			}
		}
			
		if (count($toDelete))
		{
			$this->createQuery()
				->add(Restrictions::in('id', $toDelete))
				->delete();
		}
	}
	
	/**
	 * @param theme_persistentdocument_css $style
	 */
	public function extractSkinVars($style)
	{
		$stylePath = website_StyleService::getInstance()->getSourceLocation($style->getCodename());
		if ($stylePath)
		{
			return $this->extractSkinVarsByFile($stylePath);
		}
		return array();
	}
	
	public function extractSkinVarsByFile($stylePath)
	{
		$skinRefs = array();
		$ss = website_CSSStylesheet::getInstanceFromFile($stylePath);
		foreach ($ss->getCSSRules() as $CSSRule) 
		{
			foreach ($CSSRule->getDeclarations() as $CSSDeclaration) 
			{
				if ($CSSDeclaration->getSkinRef())
				{
					$skinRefs[$CSSDeclaration->getSkinRef()] = $CSSDeclaration->getPropertyValue();
				}
			}
		}
		return $skinRefs;
	}
}
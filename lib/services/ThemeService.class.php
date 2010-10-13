<?php
/**
 * theme_ThemeService
 * @package modules.theme
 */
class theme_ThemeService extends f_persistentdocument_DocumentService
{
	/**
	 * @var theme_ThemeService
	 */
	private static $instance;

	/**
	 * @return theme_ThemeService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}

	/**
	 * @return theme_persistentdocument_theme
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_theme/theme');
	}

	/**
	 * Create a query based on 'modules_theme/theme' model.
	 * Return document that are instance of modules_theme/theme,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_theme/theme');
	}
	
	/**
	 * Create a query based on 'modules_theme/theme' model.
	 * Only documents that are strictly instance of modules_theme/theme
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_theme/theme', false);
	}
	
	/**
	 * @param string $codeName
	 * @return theme_persistentdocument_theme
	 */
	public function getByCodeName($codeName)
	{
		return $this->createQuery()->add(Restrictions::eq('codename', $codeName))->findUnique();
	}
	
	/**
	 * @param string $codeName
	 * @param generic_persistentdocument_folder $folder
	 * @return theme_persistentdocument_theme
	 */
	public function refreshByFiles($codeName, $folder = null)
	{
		$installPath = FileResolver::getInstance()
				->setPackageName('themes_' . $codeName)
				->getPath('install.xml');

		if (!$installPath)
		{
			return null;
		}
				
		$theme = $this->getByCodeName($codeName);
		if (!$theme)
		{
			$theme = $this->getNewDocumentInstance();
			$theme->setCodename($codeName);
			$theme->setLabel($codeName);	
			$folderId = $folder != null ? $folder->getId() : ModuleService::getInstance()->getRootFolderId('theme');	
			$theme->save($folderId);
		}
		return $theme;
	}
	
	/**
	 * @param theme_persistentdocument_theme $document
	 * @param string $forModuleName
	 * @param array $allowedSections
	 * @return array
	 */
	public function getResume($document, $forModuleName, $allowedSections = null)
	{
		$resume = parent::getResume($document, $forModuleName, $allowedSections);
		$resume['properties']['codename'] = $document->getCodename();
		$resume['properties']['nbtemplate'] = $document->getPagetemplateCount();
		return $resume;
	}
}
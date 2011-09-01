<?php
/**
 * @package modules.theme.lib.services
 */
class theme_ModuleService extends ModuleBaseService
{
	/**
	 * Singleton
	 * @var theme_ModuleService
	 */
	private static $instance = null;

	/**
	 * @return theme_ModuleService
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * @param Integer $documentId
	 * @return f_persistentdocument_PersistentTreeNode
	 */
//	public function getParentNodeForPermissions($documentId)
//	{
//		// Define this method to handle permissions on a virtual tree node. Example available in list module.
//	}

	/**
	 * @param Integer $documentId
	 * @return theme_persistentdocument_pagetemplate[]
	 */
	public function getAllowedTemplateForDocumentId($documentId)
	{
		$result = array();
		try 
		{
			$document = DocumentHelper::getDocumentInstance($documentId);
			$ancestors = $document->getDocumentService()->getAncestorsOf($document);
			$ancestors[] = $document;
			foreach (array_reverse($ancestors) as $ancestor) 
			{
				if ($ancestor instanceof website_persistentdocument_website || 
					$ancestor instanceof website_persistentdocument_topic) 
				{
					$result = $ancestor->getPublishedAllowedpagetemplateArray();
					if (count($result))
					{
						break;
					}
				}
			}	
		}
		catch (Exception $e)
		{
			Framework::exception($e);
		}
		return $result;
	}
	
	public function initPaths()
	{
		$paths = array(
			f_util_FileUtils::buildWebeditPath('themes'),
			f_util_FileUtils::buildWebeditPath('media', 'themes')
		);
		foreach ($paths as $path) 
		{
			f_util_FileUtils::mkdir($path);
		}		
	}
	
	public function removeThemePaths($codeName)
	{
		f_util_FileUtils::rmdir(f_util_FileUtils::buildWebeditPath('themes', $codeName));
		f_util_FileUtils::rmdir(f_util_FileUtils::buildWebeditPath('media', 'themes', $codeName));		
	}
	
	public function initThemePaths($codeName)
	{
		$paths = array(
			f_util_FileUtils::buildWebeditPath('themes', $codeName, 'templates'),
			f_util_FileUtils::buildWebeditPath('themes', $codeName, 'style'),
			f_util_FileUtils::buildWebeditPath('themes', $codeName, 'js'),
			f_util_FileUtils::buildWebeditPath('themes', $codeName, 'locale'),
			f_util_FileUtils::buildWebeditPath('themes', $codeName, 'image'),
			f_util_FileUtils::buildWebeditPath('media', 'themes', $codeName)
		);
		foreach ($paths as $path) 
		{
			f_util_FileUtils::mkdir($path);
		}		
	}

	/**
	 * @param string $codeName
	 * @param generic_persistentdocument_folder $folder
	 * @return theme_persistentdocument_theme
	 */
	public function installTheme($codeName, $folder = null)
	{
		$script = FileResolver::getInstance()
			->setPackageName('themes_' . $codeName)
			->setDirectory('setup')->getPath('init.xml');
			
		if (!file_exists($script))
		{
			throw new Exception('Invalid theme: ' .$codeName);
		}

		$theme = $this->regenerateTheme($codeName, $folder);
		
		$scriptReader = import_ScriptReader::getInstance();
		$scriptReader->execute($script);
		return $theme;
	}
	
	/**
	 * @param boolean $doEcho
	 */
	public function regenerateAllThemes($doEcho = false)
	{
		$path = f_util_FileUtils::buildWebeditPath('themes', '*');
		$themes = glob($path, GLOB_ONLYDIR);
		if (is_array($themes))
		{
			foreach ($themes as $codeName)
			{
				$this->regenerateTheme(basename($codeName), null, $doEcho);
			}
		}
	}
	
	/**
	 * @param string $codeName
	 * @param generic_persistentdocument_folder $folder
	 * @param boolean $doEcho
	 * @return theme_persistentdocument_theme
	 */
	public function regenerateTheme($codeName, $folder = null, $doEcho = false)
	{
		if ($doEcho)
		{
			echo "Compile theme: $codeName\n";
		}
		$theme = theme_ThemeService::getInstance()->refreshByFiles($codeName, $folder);
		if (!$theme)
		{
			Framework::warn(__METHOD__ . ' Unable to regenerate Theme: '. $codeName);
			return null;
		}
		theme_ImageService::getInstance()->refreshByFiles($theme);
		theme_JavascriptService::getInstance()->refreshByFiles($theme);
		theme_CssService::getInstance()->refreshByFiles($theme);
		theme_PagetemplateService::getInstance()->refreshByFiles($theme);
		
		$theme->save();
		LocaleService::getInstance()->regenerateLocalesForTheme('themes_' . $codeName);
		
		$this->buildSkinVars($theme, $doEcho);
		
		return $theme;
	}
	
	/**
	 * @param theme_persistentdocument_theme $theme
	 * @param boolean $doEcho
	 */
	private function buildSkinVars($theme, $doEcho = false)
	{
		$skinVarsPath = FileResolver::getInstance()
			->setPackageName('themes_' . $theme->getCodename())
			->setDirectory('skin')->getPath('skin.xml');
		$skinVars = array();
		
		if ($skinVarsPath)
		{
			$skinDoc = f_util_DOMUtils::fromPath($skinVarsPath);
			$fields = $skinDoc->find('//field[@name]');
			foreach ($fields as $field) 
			{
				$varName = $field->getAttribute('name');
				$skinVars[$varName] = array('type' => $field->getAttribute('type'), 'ini' => $field->getAttribute('initialvalue'));
			}
		}
		$variablesPath = f_util_FileUtils::buildChangeBuildPath('themes', $theme->getCodename(), 'variables.ser');
		if ($doEcho)
		{
			echo "Update: $variablesPath\n";
		}
		f_util_FileUtils::writeAndCreateContainer($variablesPath, serialize($skinVars), f_util_FileUtils::OVERRIDE);
	}
	
	/**
	 * @param string $codeName
	 * @return DOMDocument
	 */
	public function getEditVariablesBinding($codeName)
	{
		$theme = theme_ThemeService::getInstance()->getByCodeName($codeName);
		if (!$theme)
		{
			throw new Exception('Theme not found: ' . $codeName);
		}
		
		theme_BindingHelper::setCurrentTheme($theme);
		
		$xslPath = FileResolver::getInstance()->setPackageName('modules_theme')
			->setDirectory('templates')->getPath('variables.xsl');

		$skinDefPath = FileResolver::getInstance()->setPackageName('themes_' . $codeName)
			->setDirectory('skin')->getPath('skin.xml');

		$skinDefDoc = new DOMDocument('1.0', 'UTF-8');
		$skinDefDoc->load($skinDefPath);
			
		$xsl = new DOMDocument('1.0', 'UTF-8');
		$xsl->load($xslPath);
		$xslt = new XSLTProcessor();
		$xslt->registerPHPFunctions();
		$xslt->importStylesheet($xsl);
		$xslt->setParameter('', 'theme', $codeName);
		$panelDoc = $xslt->transformToDoc($skinDefDoc);
		return $panelDoc;
	}
}
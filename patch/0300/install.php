<?php
/**
 * theme_patch_0300
 * @package modules.theme
 */
class theme_patch_0300 extends patch_BasePatch
{
	/**
	 * Returns true if the patch modify code that is versionned.
	 * If your patch modify code that is versionned AND database structure or content,
	 * you must split it into two different patches.
	 * @return Boolean true if the patch modify code that is versionned.
	 */
	public function isCodePatch()
	{
		return true;
	}
	
	private $themecodename = "projecttheme";
	
	private $moveRes = true;
	
	private $createArchive = false;
	
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		//START Import init data 
		
		$tms = theme_ModuleService::getInstance();
		$tms->initPaths();
		
		$this->executeModuleScript('init.xml', 'theme');
		//END Import init data 

		$result = $this->getStaticTemplates();
		if (!is_array($result))
		{
			$this->logWarning('No page template founded');
			$webfactoryTheme = f_util_FileUtils::buildWebeditPath('themes', 'webfactory');
			if (!is_dir($webfactoryTheme))
			{
				$this->logWarning('Please copy the default "webfactory" theme in "themes" folder before apply patch theme 0301');
			}
			return;
		}		
		
		$tms->initThemePaths($this->themecodename);
		$themeDoc = $this->getInstallDoc();
			
		$localDom = f_util_DOMUtils::fromString('<?xml version="1.0" encoding="UTF-8"?><localization />');
		
		foreach ($result as $pageTemplateInfo)
		{
			$this->log('Convert template: ' . $pageTemplateInfo['file']);
			$scriptId = null;
			if ($pageTemplateInfo['script'] !== '')
			{
				$scriptId = $this->moveScript($pageTemplateInfo['script'], $themeDoc);
			}
			$styleId = null;
			if ($pageTemplateInfo['style'] !== '')
			{
				$styleId = $this->moveStyle($pageTemplateInfo['style'], $themeDoc);
			}
			$this->moveTemplate($pageTemplateInfo, $styleId, $scriptId, $themeDoc, $localDom);
		}
		
		$localDom->save(f_util_FileUtils::buildWebeditPath('themes', $this->themecodename, 'locale', 'templates.xml'));
		$themeDoc->ownerDocument->save(f_util_FileUtils::buildWebeditPath('themes', $this->themecodename, 'install.xml'));
		$this->generateArchive();
		$this->removeDisplay();
	}

	/**
	 * @param array $pageTemplateInfo
	 * @param string $styleId
	 * @param string $scriptId
	 * @param DOMElement $themeDoc
	 * @param DOMDocument $localDom
	 */
	private function moveTemplate($pageTemplateInfo, $styleId, $scriptId, $themeDoc, $localDom)
	{
		$pageTplDoc = $themeDoc->appendChild($themeDoc->ownerDocument->createElement('pagetemplate'));
		$newId = $this->themecodename . '/' . $pageTemplateInfo['file'];
		$pageTplDoc->setAttribute('byCodename', $newId);
		
		if (defined('DEFAULT_DOC_TYPE') && 'DEFAULT_DOC_TYPE' == 'XHMTL-1.0-Transitional')
		{
			$pageTplDoc->setAttribute('doctype', 'XHMTL-1.0-Transitional');
		}
		else
		{
			$pageTplDoc->setAttribute('doctype', 'XHTML-1.0-Strict');
		}
		$pageTplDoc->setAttribute('useprojectcss', 'true');
		if ($styleId)
		{
			$pageTplDoc->setAttribute('cssscreen', $styleId);
		}
		if ($scriptId)
		{
			$pageTplDoc->setAttribute('js', $scriptId);
		}
		
		$oldKey = $pageTemplateInfo['label'];
		$entity = $localDom->documentElement->appendChild($localDom->createElement('entity'));
		$entity->setAttribute('id', $pageTemplateInfo['file']);
		
		$langs = array('fr', 'en', 'de');
		foreach ($langs as $lang)
		{
			$value = f_Locale::translate($oldKey, null, $lang, false);
			if ($value)
			{
				$loc = $entity->appendChild($localDom->createElement('locale'));
				$loc->setAttribute('lang', $lang);
				$loc->appendChild($localDom->createTextNode($value));
			}
		}
			
		$file = $pageTemplateInfo['file'] . '.all.all.xul';		
		$oldTemplatepath = FileResolver::getInstance()->setPackageName('modules_website')
			->setDirectory('templates')->getPath($file);
		$newTemplatepath = f_util_FileUtils::buildWebeditPath('themes', $this->themecodename, 'templates', $file);
		
		$this->migrateFile($oldTemplatepath, $newTemplatepath);
	}
	
	/**
	 * @param string $scriptId
	 * @param DOMElement $themeDoc
	 * @return string
	 */
	private function moveScript($scriptId, $themeDoc)
	{
		$match = null;
		if (! preg_match('/^modules\.(\w+)\.(.*)/i', $scriptId, $match))
		{
			$this->logWarning('Invalid scriptId: ' . $scriptId);
			return null;
		}
		
		$package = 'modules_' . $match[1];
		$path = str_replace('.', '/', $match[2]) . '.js';
	
		$fileLocation = FileResolver::getInstance()->setPackageName($package)
			->setDirectory(dirname($path))
			->getPath(basename($path));
		if (!$fileLocation)
		{
			$this->logWarning('Invalid script file for: ' . $scriptId);
			return null;
		}
		
		$newScriptPath = f_util_FileUtils::buildWebeditPath('themes', $this->themecodename, 'js', basename($path));
		$newId = 'themes.' . $this->themecodename . '.js.' . basename($path, '.js');
		$this->migrateFile($fileLocation, $newScriptPath);
		return $newId;
	}
	
	/**
	 * @param string $styleId
	 * @param DOMElement $themeDoc
	 * @return string
	 */
	private function moveStyle($styleId, $themeDoc)
	{
		$stylesheetName = explode('.', $styleId);
		$stylesheetName = $stylesheetName[count($stylesheetName) - 1];
		$styleId = 'modules.website.' . $stylesheetName;
		
		$fileLocation = StyleService::getInstance()->getSourceLocation($styleId);
		$newScriptPath = f_util_FileUtils::buildWebeditPath('themes', $this->themecodename, 'style', $stylesheetName . '.css');
		if ($fileLocation !== null)
		{
			$this->convertStyleSheet($fileLocation, $newScriptPath);
		}
		$newId = 'themes.' . $this->themecodename . '.' . $stylesheetName;		
		return $newId;
	}
	
	private function convertStyleSheet($fileLocation, $newScriptPath)
	{
		$css = file_get_contents($fileLocation);
		$match = array();
		if (preg_match_all('/@import url\(([^)]+)\)/', $css, $match, PREG_SET_ORDER))
		{
			foreach ($match as $import)
			{
				$newUrl = $this->convertImportStyleSheet($import[1]);
				$css = str_replace($import[0], '@import url(' . $newUrl . ')', $css);
			}
		}
		
		$images = array();
		if (preg_match_all('/url\(\/media\/frontoffice\/([^)]+)\)/', $css, $images, PREG_SET_ORDER))
		{
			foreach ($images as $image)
			{
				$newUrl = $this->moveFrontEndImage($image[1]);
				$css = str_replace($image[0], 'url(' . $newUrl . ')', $css);
			}
		}
		if ($this->moveRes)
		{
			@unlink($fileLocation);
		}
		file_put_contents($newScriptPath, $css);
	}
	
	private function convertImportStyleSheet($url)
	{
		$parts = explode('/', $url);
		$parts[1] = 'themes';
		$parts[2] = $this->themecodename;
		$parts[3] = 'style';
		
		$path = $this->getStyleFilePathByUrl($url);
		if ($path && file_exists($path))
		{
			$css = file_get_contents($path);
			$match = array();
			if (preg_match_all('/@import url\(([^)]+)\)/', $css, $match, PREG_SET_ORDER))
			{
				foreach ($match as $import)
				{
					$newUrl = $this->convertImportStyleSheet($import[1]);
					$css = str_replace($import[0], '@import url(' . $newUrl . ')', $css);
				}
			}
			
			$images = array();
			if (preg_match_all('/url\(\/media\/frontoffice\/([^)]+)\)/', $css, $images, PREG_SET_ORDER))
			{
				foreach ($images as $image)
				{
					$newUrl = $this->moveFrontEndImage($image[1]);
					$css = str_replace($image[0], 'url(' . $newUrl . ')', $css);
				}
			}
			
			$newpath = f_util_FileUtils::buildWebeditPath('themes', $this->themecodename, 'style', basename($url));
			if ($this->moveRes)
			{
				@unlink($path);
			}
			file_put_contents($newpath, $css);
		}
		else
		{
			$this->logWarning('Unable to find import style: ' . $url);
		}
		return implode('/', $parts);
	}
	
	private function getStyleFilePathByUrl($url)
	{
		$parts = explode('/', $url);
		$path = FileResolver::getInstance()->setPackageName($parts[1] . '_' . $parts[2])->setDirectory($parts[3])->getPath($parts[4]);
		return $path;
	}
	
	private function moveFrontEndImage($url)
	{
		$path = f_util_FileUtils::buildWebeditPath('media', 'frontoffice', $url);
		if ($path && file_exists($path))
		{
			$newPath = f_util_FileUtils::buildWebeditPath('themes', $this->themecodename, 'image', $url);
			if (! file_exists($newPath))
			{
				$this->migrateFile($path, $newPath);
			}
		}
		else
		{
			$this->logWarning('Unable to find image: ' . $url);
		}
		return '/media/themes/' . $this->themecodename . '/' . $url;
	}
	
	/**
	 * @return String
	 */
	protected final function getModuleName()
	{
		return 'theme';
	}
	
	/**
	 * @return String
	 */
	protected final function getNumber()
	{
		return '0300';
	}
	
	private function getStaticTemplates()
	{
		
		$displayFilePath = FileResolver::getInstance()->setPackageName('modules_website')
			->setDirectory('config')->getPath('display.xml');
		
		if ($displayFilePath === null)
		{
			return null;
		}
		
		$results = array();
		$domDocument = new DOMDocument();
		$domDocument->load($displayFilePath);
		$templates = $domDocument->getElementsByTagName('display');
		foreach ($templates as $template)
		{
			$templateProps = array();
			$templateProps['file'] = $template->getAttribute("file");
			;
			$templateProps['label'] = $template->getAttribute("label");
			$templateProps['style'] = $template->getAttribute("style");
			$templateProps['script'] = $template->getAttribute("script");
			$results[] = $templateProps;
		}
		return $results;
	}
	
	private function removeDisplay()
	{
		if ($this->moveRes)
		{
			$displayFilePath = FileResolver::getInstance()->setPackageName('modules_website')->setDirectory('config')->getPath('display.xml');
			if ($displayFilePath)
			{
				unlink($displayFilePath);
			}
		}
	}
	
	private function migrateFile($src, $dest)
	{
		if ($this->moveRes)
		{
			if (file_exists($dest))
			{
				$this->logWarning('Dest file exist: ' . $dest);
			}
			else
			{
				@rename($src, $dest);
			}
		}
		else
		{
			f_util_FileUtils::cp($src, $dest, f_util_FileUtils::OVERRIDE);
		}
	}
	
	private function generateArchive()
	{
		if ($this->createArchive)
		{
			$path = f_util_FileUtils::buildWebeditPath('themes', $this->themecodename . '.zip');
			if (file_exists($path))
			{
				unlink($path);
			}
			$this->log('Archive created in:' . theme_ArchiveService::getInstance()->archive($this->themecodename));
		}
	}
	
	/**
	 * @return DOMElement
	 */
	private function getInstallDoc()
	{
		$installDoc = f_util_DOMUtils::fromPath(f_util_FileUtils::buildWebeditPath('modules', 'theme', 'templates', 'install.xml'));
		$rootFolder = $installDoc->getElementsByTagName('rootfolder')->item(0);
		
		$themeDoc = $rootFolder->appendChild($installDoc->createElement('theme'));
		$themeDoc->setAttribute('id', $this->themecodename);
		$themeDoc->setAttribute('byCodename', $this->themecodename);
		$themeDoc->setAttribute('label', $this->themecodename);
		$themeDoc->setAttribute('description', $this->themecodename);
		return $themeDoc;
	}
}
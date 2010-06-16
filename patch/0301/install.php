<?php
/**
 * theme_patch_0301
 * @package modules.theme
 */
class theme_patch_0301 extends patch_BasePatch
{ 
	private $themecodename = "projecttheme";
		
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		//$this->clearAllDocuments();
		
		if (!PatchService::getInstance()->isInstalled('website', '0315'))
		{
			throw new Exception('Execute: change.php apply-patch website 0315 before this patch');
		}

		$installPath = f_util_FileUtils::buildWebeditPath('themes', $this->themecodename, 'install.xml');
		if (!file_exists($installPath))
		{
			$webfactoryPath = f_util_FileUtils::buildWebeditPath('themes', 'webfactory', 'install.xml');
			if (!file_exists($webfactoryPath))
			{
				$this->logError('Unable to install webfactory theme');
				return;
			}
			theme_ModuleService::getInstance()->installTheme('webfactory');
			$this->updatePageTemplateName('webfactory');			
			$this->applyPageTemplateToAllWebsite('webfactory');
		}		
		theme_ModuleService::getInstance()->installTheme($this->themecodename);
		$this->updatePageTemplateName($this->themecodename);
		$this->applyPageTemplateToAllWebsite($this->themecodename);
	}

	private function applyPageTemplateToAllWebsite($themecodename)
	{
		$this->log('Allow page template to all website');
		$theme = theme_ThemeService::getInstance()->getByCodeName($themecodename);
		if (!$theme)
		{
			$this->logError('Unable to find ' . $themecodename . ' db theme');
			return;
		}
		
		$pageTemplates = $theme->getPublishedPagetemplateArray();
		$websites = website_WebsiteService::getInstance()->getAll();
		foreach ($websites as $website) 
		{
			foreach ($pageTemplates as $pageTemplate) 
			{
				$website->addAllowedpagetemplate($pageTemplate);
			}
			$website->save();
		}	
	}
		
	private function updatePageTemplateName($themecodename)
	{
		$this->log('Update template name for all pages');
		$sql = "UPDATE `m_website_doc_page` SET `template` =  CONCAT('". $themecodename ."/', `template`) WHERE `template` not like '". $themecodename ."/%'";
		$this->executeSQLQuery($sql);
		
		$sql = "UPDATE `m_website_doc_template` SET `template` =  CONCAT('". $themecodename ."/', `template`) WHERE `template` not like '". $themecodename ."/%'";
		$this->executeSQLQuery($sql);
	}
	
	
	private function clearAllDocuments()
	{
		theme_ThemeService::getInstance()->createQuery()->delete();		
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
		return '0301';
	}
}
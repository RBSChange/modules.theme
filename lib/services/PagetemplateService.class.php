<?php
/**
 * theme_PagetemplateService
 * @package modules.theme
 */
class theme_PagetemplateService extends f_persistentdocument_DocumentService
{
	/**
	 * @var theme_PagetemplateService
	 */
	private static $instance;

	/**
	 * @return theme_PagetemplateService
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
	 * @return theme_persistentdocument_pagetemplate
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_theme/pagetemplate');
	}

	/**
	 * Create a query based on 'modules_theme/pagetemplate' model.
	 * Return document that are instance of modules_theme/pagetemplate,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_theme/pagetemplate');
	}
	
	/**
	 * Create a query based on 'modules_theme/pagetemplate' model.
	 * Only documents that are strictly instance of modules_theme/pagetemplate
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_theme/pagetemplate', false);
	}
	
	
	/**
	 * @param string $codeName
	 * @return theme_persistentdocument_pagetemplate
	 */
	public function getByCodeName($codeName)
	{
		return $this->createQuery()->add(Restrictions::eq('codename', $codeName))->findUnique();
	}
		
	/**
	 * Get the list of the id's of all the change:content tags in the template
	 * @param theme_persistentdocument_pagetemplate $template
	 * @return string[]
	 */
	public function getLayoutIds($template)
	{
		try
		{		
			$contentIds = array();		
			$DOMDoc = $template->getDOMContent();
			if ($DOMDoc)
			{
				$DOMDoc->registerNamespace('change', website_PageService::CHANGE_PAGE_EDITOR_NS);
				$templateNode = $DOMDoc->findUnique('//change:template[@content-type="html"]');
				if (!$templateNode)
				{
					$templateNode = $DOMDoc->findUnique('//change:template');
				}
				if (!$templateNode)
				{
					Framework::warn("template " . $template->getCodename() . " has no change:template tag");
				} 
				else
				{
					foreach ($DOMDoc->find('.//change:content', $templateNode) as $content)
					{
						$contentIds[] = $content->getAttribute('id');
					}
				}
			}		
		}
		catch (Exception $e)
		{
			Framework::exception($e);
			return array();
		}
		return $contentIds;		
	}
	
	
	/**
	 * Get the list of the id's of all the change:content tags in the template
	 * $codeName 
	 * @param string $codeName
	 * @return string[]
	 */
	public function getChangeContentIds($codeName)
	{
		$template = $this->getByCodeName($codeName);
		if (!$template)
		{
			throw new TemplateNotFoundException($codeName);
		}
		return $this->getLayoutIds($template);
	}
		
	private $standardScriptIds;
	private $standardScreenStyleIds;
	private $standardPrintStyleIds;
	
	/**
	 * @return string[]
	 */
	public function getStandardScriptIds()
	{
		if ($this->standardScriptIds === null)
		{
			$this->standardScriptIds = array();
			
			foreach (ModuleService::getInstance()->getModulesObj() as $module)
			{
				$scriptPath = FileResolver::getInstance()->setPackageName($module->getFullName())
					->setDirectory('lib')
					->getPath('frontoffice.js');
				if ($scriptPath)
				{
					$this->standardScriptIds[] = 'modules.'.$module->getName().'.lib.frontoffice';
				}
			}					
		}
		return $this->standardScriptIds;
	}
	
	/**
	 * @return string[]
	 */
	public function getStandardScreenStyleIds()
	{
		if ($this->standardScreenStyleIds === null)
		{
			$this->standardScreenStyleIds = array('modules.generic.frontoffice', 'modules.generic.richtext', 'modules.website.frontoffice', 'modules.website.richtext');			
			$ss = StyleService::getInstance();
			foreach (ModuleService::getInstance()->getModulesObj() as $changeModule)
			{
				$moduleName = $changeModule->getName();
				if ($moduleName == "website" || $moduleName == "generic")
				{
					continue;
				}
				$stylesheetId = 'modules.' . $moduleName . '.frontoffice';
				if ($ss->getSourceLocation($stylesheetId))
				{
					$this->standardScreenStyleIds[] = $stylesheetId;
				}
			}				
		}
		return $this->standardScreenStyleIds;
	}
	
	/**
	 * @return string[]
	 */
	public function getStandardPrintStyleIds()
	{
		if ($this->standardPrintStyleIds === null)
		{
			$this->standardPrintStyleIds = array('modules.generic.print', 'modules.website.print');
			$ss = StyleService::getInstance();			
			foreach (ModuleService::getInstance()->getModulesObj() as $changeModule)
			{
				$moduleName = $changeModule->getName();
				if ($moduleName == "website" || $moduleName == "generic")
				{
					continue;
				}
				$stylesheetId = 'modules.' . $moduleName . '.print';
				if ($ss->getSourceLocation($stylesheetId))
				{
					$this->standardPrintStyleIds[] = $stylesheetId;
				}
			}
		}
		return $this->standardPrintStyleIds;
	}
	
	/**
	 * @param theme_persistentdocument_theme $theme
	 */
	public function refreshByFiles($theme)
	{
		$paths = FileResolver::getInstance()
				->setPackageName('themes_' . $theme->getCodename())
				->setDirectory('templates')
				->getPaths('');	

		$pageTemplatesPaths = array();
		if (is_array($paths) && count($paths))
		{
			foreach ($paths as $path) 
			{
				$dir = new DirectoryIterator($path);
				foreach ($dir as $fileinfo) 
				{
				    if ($fileinfo->isFile()) 
				    {
				    	$ext = f_util_FileUtils::getFileExtension($fileinfo->getFilename());
				    	if ($ext == 'xul' || $ext == 'xml')
				    	{
				    		$pageTemplatesPaths[$fileinfo->getFilename()] = $fileinfo->getPathname();
				    	}
				    }
				}
			}
		}
		
		$pageTemplates = array();
		foreach ($pageTemplatesPaths as $baseName => $path) 
		{
			$key = str_replace(array('.xul', '.all', '.xml'), '', $baseName);	
			$codeName = $theme->getCodename() . '/' . $key;
			$pageTemplate = $this->getByCodeName($codeName);
			if (!$pageTemplate)
			{
				$pageTemplate = $this->getNewDocumentInstance();		
				$pageTemplate->setCodename($codeName);
				$pageTemplate->setLabel('&themes.'.$theme->getCodename() .'.templates.' . ucfirst($key).';');
				$theme->addPagetemplate($pageTemplate);
			}
			$pageTemplate->setProjectpath('themes/' . $theme->getCodename() . '/templates/' . $baseName);
			$pageTemplate->save();
			$pageTemplates[] = $pageTemplate->getId();
		}
		
		$toDelete = array();
		foreach ($theme->getPagetemplateArray() as $pageTemplate) 
		{
			if (!in_array($pageTemplate->getId(), $pageTemplates))
			{
				$toDelete[] =  $pageTemplate->getId();
				$theme->removePagetemplate($pageTemplate);	
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
	 * @param theme_persistentdocument_pagetemplate $pagetemplate
	 * @return array
	 */
	protected function getCompatibilityInfo($pagetemplate)
	{
		$modules = array();
		$doc = $pagetemplate->getDOMContent();
		$templateBlocs = $doc->find('//change:templateblock[@type]');
		foreach ($templateBlocs as $templateBloc) 
		{
			$parts = explode('_', $templateBloc->getAttribute('type'));
			if (count($parts) > 2 && $parts[0] === 'modules')
			{
				$modules[$parts[1]] = true;
			}
		}
		$layoutIds = array();
		$contentLayouts = $doc->find('//change:content[@id]');
		foreach ($contentLayouts as $contentLayout) 
		{
			$layoutIds[$contentLayout->getAttribute('id')] = true;
		}	
		
		return array('modules' => array_keys($modules), 'layout' => array_keys($layoutIds));
	}
	
	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @param string $forModuleName
	 * @param array $allowedSections
	 * @return array
	 */
	public function getResume($document, $forModuleName, $allowedSections = null)
	{
		$resume = parent::getResume($document, $forModuleName, $allowedSections);
		$resume['properties']['codename'] = $document->getCodename();
		$infos = $this->getCompatibilityInfo($document);
		$resume['properties']['layoutids'] = implode(', ', $infos['layout']);
		$resume['properties']['requiredmodules'] = implode(', ', $infos['modules']);
		return $resume;
	}
	
	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @param string $moduleName
	 * @param string $treeType
	 * @param array<string, string> $nodeAttributes
	 */
	public function addTreeAttributes($document, $moduleName, $treeType, &$nodeAttributes)
	{
		$thumbnail = $document->getThumbnail();
		if ($thumbnail)
		{		
			$nodeAttributes['hasPreviewImage'] = true;
			if ($treeType == 'wlist')
			{
	    		$nodeAttributes['thumbnailsrc'] = $thumbnail->getUISrc();
			}
		}
	}
	
	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @param String[] $propertiesName
	 * @param array $datas
	 */
	public function addFormProperties($document, $propertiesName, &$datas)
	{
		if (in_array('editableblocksJSON', $propertiesName))
		{
			$datas['propertyGrids'] = block_BlockService::getInstance()->getBlocksWithPropertyGrid();
		}
	}

	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @return array
	 */
	public function getEditableblocksInfos($document)
	{
		$result = array();
		$doc = $document->getDOMContent();
		$doc->registerNamespace('change', website_PageRessourceService::CHANGE_PAGE_EDITOR_NS);
		foreach ($doc->find('//change:templateblock[@editname]') as $element) 
		{
			if ($element instanceof DOMElement) 
			{
				$name = $element->getAttribute('editname');
				$result[$name] = array('type' => 'empty', 'parameters' => array());
				if ($element->hasAttribute('type'))
				{
					$result[$name]['type'] = $element->getAttribute('type');
					foreach ($element->attributes as $attrNode) 
					{
						if (strpos($attrNode->name, '__') === 0)
						{
							$result[$name]['parameters'][substr($attrNode->name, 2)] = $attrNode->value;
						}
					}
				}
			}
		}
		return $result;
	}
}
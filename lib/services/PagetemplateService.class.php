<?php
/**
 * @package modules.theme
 * @method theme_PagetemplateService getInstance()
 */
class theme_PagetemplateService extends f_persistentdocument_DocumentService
{
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
		return $this->getPersistentProvider()->createQuery('modules_theme/pagetemplate');
	}
	
	/**
	 * Create a query based on 'modules_theme/pagetemplate' model.
	 * Only documents that are strictly instance of modules_theme/pagetemplate
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_theme/pagetemplate', false);
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
				/* @var $module c_Module */
				$scriptPath = change_FileResolver::getNewInstance()->getPath('modules', $module->getName(), 'lib', 'frontoffice.js');
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
			$ss = website_StyleService::getInstance();
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
			$ss = website_StyleService::getInstance();			
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
		$paths = change_FileResolver::getNewInstance()->getPaths('themes', $theme->getCodename(), 'templates');	

		$pageTemplatesPaths = array();
		if (count($paths))
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
	 * @return integer
	 */
	public function getUsageCount($document)
	{
		$tms = theme_ModuleService::getInstance();
		$result = website_PageService::getInstance()->createQuery()
		->setProjection(Projections::count('id', 'pagecount'))
		->add(Restrictions::notin('publicationstatus', $tms->getDeadPageStatuses()))
		->add(Restrictions::eq('template', $document->getCodeName()))
		->find();
		return $result[0]['pagecount'];
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
		$resume['properties']['usageCount'] = $document->getDocumentService()->getUsageCount($document);
		$infos = $this->getCompatibilityInfo($document);
		$resume['properties']['layoutids'] = implode(', ', $infos['layout']);
		$resume['properties']['requiredmodules'] = implode(', ', $infos['modules']);
		return $resume;
	}
	
	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @param array<string, string> $attributes
	 * @param integer $mode
	 * @param string $moduleName
	 */
	public function completeBOAttributes($document, &$attributes, $mode, $moduleName)
	{
		$thumbnail = $document->getThumbnail();
		if ($thumbnail)
		{	
			if ($mode & DocumentHelper::MODE_ITEM)
			{
				$attributes['hasPreviewImage'] = true;
			}
			if ($mode & DocumentHelper::MODE_CUSTOM)
			{
				$attributes['thumbnailsrc'] = $thumbnail->getUISrc();
				$attributes['usageCount'] = $document->getDocumentService()->getUsageCount($document);
			}
		}
	}
	
	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @param string[] $propertiesName
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
	

	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @param integer $parentNodeId
	 */
	protected function preUpdate($document, $parentNodeId)
	{
		$this->syncroniseDeclinations($document);
	}
	
	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 */
	protected function syncroniseDeclinations($document)
	{
		$array = array_intersect($document->getModifiedPropertyNames(), $this->getSynchronizedPropertiesName());
		if (count($array))
		{
			if (Framework::isInfoEnabled())
			{
				Framework::info(__METHOD__ . " Synchronize properties :" . implode(', ', $array));
			}
			$this->touchAllDeclinations($document);
		}
	}
	
	/**
	 * @param theme_persistentdocument_pagetemplate $pagetemplate
	 */
	protected function touchAllDeclinations($pagetemplate)
	{
		$declinations = $pagetemplate->getPagetemplatedeclinationArrayInverse();
		foreach ($declinations as $declination)
		{
			$declination->setModificationdate(null);
			$declination->save();
		}
	}
	
	/**
	 * @return string[]
	 */
	public function getSynchronizedPropertiesName()
	{
		return array('thumbnail', 'projectpath', 'doctype', 'useprojectcss', 'cssscreen', 'cssprint', 'useprojectjs', 'js');
	}
	
	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 */
	protected function preDelete($document)
	{
		// Check that no page is using it.
		$count = $this->getUsageCount($document);
		if ($count > 0)
		{
			throw new BaseException('This template can\'t be deleted, it is used by ' . $count . ' pages.', 'm.theme.bo.errors.uninstall-used-template', array('pageCount' => $count));
		}
	}
	
	/**
	 * @param theme_persistentdocument_pagetemplate $toReplace
	 * @param theme_persistentdocument_pagetemplate $replaceBy
	 */
	public function replacePagetemplate($toReplace, $replaceBy)
	{
		$refreshListTask = task_PlannedtaskService::getInstance()->getNewDocumentInstance();
		$refreshListTask->setSystemtaskclassname('theme_PagetemplateReplacementTask');
		$refreshListTask->setLabel(__METHOD__);
		$refreshListTask->setParameters(serialize(array('toReplaceId' => $toReplace->getId(), 'replaceById' => $replaceBy->getId())));
		$refreshListTask->setUniqueExecutiondate(date_Calendar::getInstance());
		$refreshListTask->save(ModuleService::getInstance()->getSystemFolderId('task', 'theme'));
	}
}
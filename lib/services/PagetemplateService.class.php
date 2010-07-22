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
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
//	protected function preSave($document, $parentNodeId = null)
//	{
//
//	}

	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function preInsert($document, $parentNodeId = null)
//	{
//	}

	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function postInsert($document, $parentNodeId = null)
//	{
//	}

	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function preUpdate($document, $parentNodeId = null)
//	{
//	}

	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function postUpdate($document, $parentNodeId = null)
//	{
//	}

	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function postSave($document, $parentNodeId = null)
//	{
//	}

	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @return void
	 */
//	protected function preDelete($document)
//	{
//	}

	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @return void
	 */
//	protected function preDeleteLocalized($document)
//	{
//	}

	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @return void
	 */
//	protected function postDelete($document)
//	{
//	}

	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @return void
	 */
//	protected function postDeleteLocalized($document)
//	{
//	}

	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @return boolean true if the document is publishable, false if it is not.
	 */
//	public function isPublishable($document)
//	{
//		$result = parent::isPublishable($document);
//		return $result;
//	}


	/**
	 * Methode à surcharger pour effectuer des post traitement apres le changement de status du document
	 * utiliser $document->getPublicationstatus() pour retrouver le nouveau status du document.
	 * @param theme_persistentdocument_pagetemplate $document
	 * @param String $oldPublicationStatus
	 * @param array<"cause" => String, "modifiedPropertyNames" => array, "oldPropertyValues" => array> $params
	 * @return void
	 */
//	protected function publicationStatusChanged($document, $oldPublicationStatus, $params)
//	{
//	}

	/**
	 * Correction document is available via $args['correction'].
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param Array<String=>mixed> $args
	 */
//	protected function onCorrectionActivated($document, $args)
//	{
//	}

	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @param String $tag
	 * @return void
	 */
//	public function tagAdded($document, $tag)
//	{
//	}

	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @param String $tag
	 * @return void
	 */
//	public function tagRemoved($document, $tag)
//	{
//	}

	/**
	 * @param theme_persistentdocument_pagetemplate $fromDocument
	 * @param f_persistentdocument_PersistentDocument $toDocument
	 * @param String $tag
	 * @return void
	 */
//	public function tagMovedFrom($fromDocument, $toDocument, $tag)
//	{
//	}

	/**
	 * @param f_persistentdocument_PersistentDocument $fromDocument
	 * @param theme_persistentdocument_pagetemplate $toDocument
	 * @param String $tag
	 * @return void
	 */
//	public function tagMovedTo($fromDocument, $toDocument, $tag)
//	{
//	}

	/**
	 * Called before the moveToOperation starts. The method is executed INSIDE a
	 * transaction.
	 *
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param Integer $destId
	 */
//	protected function onMoveToStart($document, $destId)
//	{
//	}

	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @param Integer $destId
	 * @return void
	 */
//	protected function onDocumentMoved($document, $destId)
//	{
//	}

	/**
	 * this method is call before saving the duplicate document.
	 * If this method not override in the document service, the document isn't duplicable.
	 * An IllegalOperationException is so launched.
	 *
	 * @param theme_persistentdocument_pagetemplate $newDocument
	 * @param theme_persistentdocument_pagetemplate $originalDocument
	 * @param Integer $parentNodeId
	 *
	 * @throws IllegalOperationException
	 */
//	protected function preDuplicate($newDocument, $originalDocument, $parentNodeId)
//	{
//		throw new IllegalOperationException('This document cannot be duplicated.');
//	}

	/**
	 * this method is call after saving the duplicate document.
	 * $newDocument has an id affected.
	 * Traitment of the children of $originalDocument.
	 *
	 * @param theme_persistentdocument_pagetemplate $newDocument
	 * @param theme_persistentdocument_pagetemplate $originalDocument
	 * @param Integer $parentNodeId
	 *
	 * @throws IllegalOperationException
	 */
//	protected function postDuplicate($newDocument, $originalDocument, $parentNodeId)
//	{
//	}

	/**
	 * Returns the URL of the document if has no URL Rewriting rule.
	 *
	 * @param theme_persistentdocument_pagetemplate $document
	 * @param string $lang
	 * @param array $parameters
	 * @return string
	 */
//	public function generateUrl($document, $lang, $parameters)
//	{
//	}

	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @return integer | null
	 */
//	public function getWebsiteId($document)
//	{
//	}

	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @return website_persistentdocument_page | null
	 */
//	public function getDisplayPage($document)
//	{
//	}

	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 * @param string $bockName
	 * @return array with entries 'module' and 'template'. 
	 */
//	public function getSolrserachResultItemTemplate($document, $bockName)
//	{
//		return array('module' => 'theme', 'template' => 'Theme-Inc-PagetemplateResultDetail');
//	}
}
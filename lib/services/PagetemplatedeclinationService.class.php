<?php
/**
 * theme_PagetemplatedeclinationService
 * @package modules.theme
 */
class theme_PagetemplatedeclinationService extends theme_PagetemplateService
{
	/**
	 * @var theme_PagetemplatedeclinationService
	 */
	private static $instance;

	/**
	 * @return theme_PagetemplatedeclinationService
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
	 * @return theme_persistentdocument_pagetemplatedeclination
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_theme/pagetemplatedeclination');
	}

	/**
	 * Create a query based on 'modules_theme/pagetemplatedeclination' model.
	 * Return document that are instance of modules_theme/pagetemplatedeclination,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_theme/pagetemplatedeclination');
	}
	
	/**
	 * Create a query based on 'modules_theme/pagetemplatedeclination' model.
	 * Only documents that are strictly instance of modules_theme/pagetemplatedeclination
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_theme/pagetemplatedeclination', false);
	}
	
	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
//	protected function preSave($document, $parentNodeId)
//	{
//		parent::preSave($document, $parentNodeId);
//	}

	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function preInsert($document, $parentNodeId)
	{
		parent::preInsert($document, $parentNodeId);
		
		$document->setInsertInTree(false);
		
		$reference = $document->getReference();
		if (!$reference)
		{
			$reference = theme_persistentdocument_pagetemplate::getInstanceById($parentNodeId);
			$document->setReference($reference);
		}
		if (!$document->getCodename())
		{
			$document->setCodename($this->generateCodename($reference));
		}
		$this->synchronizePropertiesByReference($document, $reference);
		if (!$document->getConfiguredBlocks())
		{
			$document->setConfiguredBlocks($reference->getConfiguredBlocks());
		}
	}
	
	/**
	 * @param theme_persistentdocument_pagetemplate $parent
	 * @return string
	 */
	protected function generateCodename($parent)
	{
		$prefix = $parent->getCodename() . '-';
		$codeNames = $this->createQuery()->add(Restrictions::beginsWith('codename', $prefix))->setProjection(Projections::property('codename'))->findColumn('codename');
		$suffix = count($codeNames);
		while (in_array($prefix . $suffix, $codeNames))
		{
			$suffix++;
		}
		return $prefix . $suffix;
	}

	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function postInsert($document, $parentNodeId)
//	{
//		parent::postInsert($document, $parentNodeId);
//	}

	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function preUpdate($document, $parentNodeId)
	{
		parent::preUpdate($document, $parentNodeId);
		$this->synchronizePropertiesByReference($document);
	}

	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function postUpdate($document, $parentNodeId)
//	{
//		parent::postUpdate($document, $parentNodeId);
//	}

	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function postSave($document, $parentNodeId)
//	{
//		parent::postSave($document, $parentNodeId);
//	}

	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @return void
	 */
//	protected function preDelete($document)
//	{
//		parent::preDelete($document);
//	}

	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @return void
	 */
//	protected function preDeleteLocalized($document)
//	{
//		parent::preDeleteLocalized($document);
//	}

	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @return void
	 */
//	protected function postDelete($document)
//	{
//		parent::postDelete($document);
//	}

	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @return void
	 */
//	protected function postDeleteLocalized($document)
//	{
//		parent::postDeleteLocalized($document);
//	}

	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
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
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @param String $oldPublicationStatus
	 * @param array<"cause" => String, "modifiedPropertyNames" => array, "oldPropertyValues" => array> $params
	 * @return void
	 */
//	protected function publicationStatusChanged($document, $oldPublicationStatus, $params)
//	{
//		parent::publicationStatusChanged($document, $oldPublicationStatus, $params);
//	}

	/**
	 * Correction document is available via $args['correction'].
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param Array<String=>mixed> $args
	 */
//	protected function onCorrectionActivated($document, $args)
//	{
//		parent::onCorrectionActivated($document, $args);
//	}

	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @param String $tag
	 * @return void
	 */
//	public function tagAdded($document, $tag)
//	{
//		parent::tagAdded($document, $tag);
//	}

	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @param String $tag
	 * @return void
	 */
//	public function tagRemoved($document, $tag)
//	{
//		parent::tagRemoved($document, $tag);
//	}

	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $fromDocument
	 * @param f_persistentdocument_PersistentDocument $toDocument
	 * @param String $tag
	 * @return void
	 */
//	public function tagMovedFrom($fromDocument, $toDocument, $tag)
//	{
//		parent::tagMovedFrom($fromDocument, $toDocument, $tag);
//	}

	/**
	 * @param f_persistentdocument_PersistentDocument $fromDocument
	 * @param theme_persistentdocument_pagetemplatedeclination $toDocument
	 * @param String $tag
	 * @return void
	 */
//	public function tagMovedTo($fromDocument, $toDocument, $tag)
//	{
//		parent::tagMovedTo($fromDocument, $toDocument, $tag);
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
//		parent::onMoveToStart($document, $destId);
//	}

	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @param Integer $destId
	 * @return void
	 */
//	protected function onDocumentMoved($document, $destId)
//	{
//		parent::onDocumentMoved($document, $destId);
//	}

	/**
	 * this method is call before saving the duplicate document.
	 * @param theme_persistentdocument_pagetemplatedeclination $newDocument
	 * @param theme_persistentdocument_pagetemplatedeclination $originalDocument
	 * @param Integer $parentNodeId
	 */
	protected function preDuplicate($newDocument, $originalDocument, $parentNodeId)
	{
		$newDocument->setCodename(null);
		
		// This doc is not in the tree, so we need to update label manually.
		$reference = $newDocument->getReference();
		$label = $newDocument->getLabel();
		$defaultPrefix = LocaleService::getInstance()->transBO('m.generic.backoffice.duplicate-prefix', array('ucf')) . ' ';
		$number = -1;
		while ($reference)
		{
			$prefix = ($number < 0) ? '' : str_replace('{number}', $number == 0 ? '' : ' ('.$number.')', $defaultPrefix);
			$query = $this->createQuery()
				->add(Restrictions::eq('label', $prefix . $label))
				->add(Restrictions::eq('reference', $reference))
				->setProjection(Projections::rowCount('count'));
			if (f_util_ArrayUtils::firstElement($query->findColumn('count')) == 0)
			{
				break;
			}
			$number += 1;
		}
		$maxSize = $newDocument->getPersistentModel()->getProperty('label')->getMaxSize();
		$newLabel = f_util_StringUtils::shortenString($prefix . $label, $maxSize);
		$newDocument->setLabel($newLabel);
	}

	/**
	 * this method is call after saving the duplicate document.
	 * $newDocument has an id affected.
	 * Traitment of the children of $originalDocument.
	 *
	 * @param theme_persistentdocument_pagetemplatedeclination $newDocument
	 * @param theme_persistentdocument_pagetemplatedeclination $originalDocument
	 * @param Integer $parentNodeId
	 *
	 * @throws IllegalOperationException
	 */
//	protected function postDuplicate($newDocument, $originalDocument, $parentNodeId)
//	{
//	}

	/**
	 * @param website_UrlRewritingService $urlRewritingService
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @param website_persistentdocument_website $website
	 * @param string $lang
	 * @param array $parameters
	 * @return f_web_Link | null
	 */
//	public function getWebLink($urlRewritingService, $document, $website, $lang, $parameters)
//	{
//		return null;
//	}

	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @return integer | null
	 */
//	public function getWebsiteId($document)
//	{
//		return parent::getWebsiteId($document);
//	}

	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @return integer[] | null
	 */
//	public function getWebsiteIds($document)
//	{
//		return parent::getWebsiteIds($document);
//	}

	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @return website_persistentdocument_page | null
	 */
//	public function getDisplayPage($document)
//	{
//		return parent::getDisplayPage($document);
//	}

	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @param string $forModuleName
	 * @param array $allowedSections
	 * @return array
	 */
//	public function getResume($document, $forModuleName, $allowedSections = null)
//	{
//		$resume = parent::getResume($document, $forModuleName, $allowedSections);
//		return $resume;
//	}

	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @param string $bockName
	 * @return array with entries 'module' and 'template'. 
	 */
//	public function getSolrsearchResultItemTemplate($document, $bockName)
//	{
//		return array('module' => 'theme', 'template' => 'Theme-Inc-PagetemplatedeclinationResultDetail');
//	}

	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @param string $moduleName
	 * @param string $treeType
	 * @param array<string, string> $nodeAttributes
	 */
//	public function addTreeAttributes($document, $moduleName, $treeType, &$nodeAttributes)
//	{
//	}
	
	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @param String[] $propertiesName
	 * @param Array $datas
	 */
//	public function addFormProperties($document, $propertiesName, &$datas)
//	{
//	}
		
	/**
	 * Synchronize properties 'thumbnail', 'projectpath', 'doctype', 'useprojectcss', 'cssscreen', 'cssprint', 'useprojectjs', 'js'
	 * @param theme_persistentdocument_pagetemplatedeclination $declination
	 * @param theme_persistentdocument_pagetemplate $pagetemplate
	 * @see theme_PagetemplateService::getSynchronizedPropertiesName()
	 */
	protected function synchronizePropertiesByReference($declination, $pagetemplate = null)
	{
		if ($pagetemplate === null)
		{
			$pagetemplate = $declination->getReference();
		}
		foreach ($pagetemplate->getDocumentService()->getSynchronizedPropertiesName() as $name)
		{
			switch ($name)
			{
				case 'thumbnail': $declination->setThumbnail($pagetemplate->getThumbnail()); break;
				case 'projectpath': $declination->setProjectpath($pagetemplate->getProjectpath()); break;
				case 'doctype': $declination->setDoctype($pagetemplate->getDoctype());  break;
				case 'useprojectcss': $declination->setUseprojectcss($pagetemplate->getUseprojectcss()); break;
				case 'cssscreen': $declination->setCssscreen($pagetemplate->getCssscreen()); break;
				case 'cssprint': $declination->setCssprint($pagetemplate->getCssprint()); break;
				case 'useprojectjs': $declination->setUseprojectjs($pagetemplate->getUseprojectjs()); break;
				case 'js': $declination->setJs($pagetemplate->getJs()); break;
			}
		}
	}
	
	/**
	 * @param theme_persistentdocument_pagetemplate $document
	 */
	protected function syncroniseDeclinations($document)
	{
		// Nothing to do: a declination should not itself have any declination.
	}
}
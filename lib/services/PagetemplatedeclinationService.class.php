<?php
/**
 * @package modules.theme
 * @method theme_PagetemplatedeclinationService getInstance()
 */
class theme_PagetemplatedeclinationService extends theme_PagetemplateService
{
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
		return $this->getPersistentProvider()->createQuery('modules_theme/pagetemplatedeclination');
	}
	
	/**
	 * Create a query based on 'modules_theme/pagetemplatedeclination' model.
	 * Only documents that are strictly instance of modules_theme/pagetemplatedeclination
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_theme/pagetemplatedeclination', false);
	}
	
	/**
	 * @param theme_persistentdocument_pagetemplatedeclination $document
	 * @param integer $parentNodeId Parent node ID where to save the document.
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
	 * @param integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function preUpdate($document, $parentNodeId)
	{
		parent::preUpdate($document, $parentNodeId);
		$this->synchronizePropertiesByReference($document);
	}

	/**
	 * this method is call before saving the duplicate document.
	 * @param theme_persistentdocument_pagetemplatedeclination $newDocument
	 * @param theme_persistentdocument_pagetemplatedeclination $originalDocument
	 * @param integer $parentNodeId
	 */
	protected function preDuplicate($newDocument, $originalDocument, $parentNodeId)
	{
		$newDocument->setCodename(null);
		
		// This doc is not in the tree, so we need to update label manually.
		$reference = $newDocument->getReference();
		$label = $originalDocument->getTreeNodeLabel();
		$defaultPrefix = LocaleService::getInstance()->trans('m.generic.backoffice.duplicate-prefix', array('ucf')) . ' ';
		$number = 0;
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
		$maxSize = $maxSize < 1 ? 250 : $maxSize;
		$newLabel = f_util_StringUtils::shortenString($prefix . $label, $maxSize);
		$newDocument->setLabel($newLabel);
	}
		
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
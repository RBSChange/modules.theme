<?php
/**
 * theme_JavascriptService
 * @package modules.theme
 */
class theme_JavascriptService extends f_persistentdocument_DocumentService
{
	/**
	 * @var theme_JavascriptService
	 */
	private static $instance;

	/**
	 * @return theme_JavascriptService
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
	 * @return theme_persistentdocument_javascript
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_theme/javascript');
	}

	/**
	 * Create a query based on 'modules_theme/javascript' model.
	 * Return document that are instance of modules_theme/javascript,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_theme/javascript');
	}
	
	/**
	 * Create a query based on 'modules_theme/javascript' model.
	 * Only documents that are strictly instance of modules_theme/javascript
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_theme/javascript', false);
	}
	
	/**
	 * @param string $codeName
	 * @return theme_persistentdocument_javascript
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
				->setDirectory('js')
				->getPaths('');	

		$jsPaths = array();
		if (is_array($paths) && count($paths))
		{
			foreach ($paths as $path) 
			{
				$dir = new DirectoryIterator($path);
				foreach ($dir as $fileinfo) 
				{
				    if ($fileinfo->isFile()) 
				    {
				    	$jsParts = explode('.', $fileinfo->getFilename());
				    	if (count($jsParts) == 2 && $jsParts[1] == 'js')
				    	{
				    		$jsPaths[$jsParts[0]] = $fileinfo->getPathname();
				    	}
				    }
				}
			}
		}
		
		$jss = array();
		foreach ($jsPaths as $baseName => $path) 
		{
			$codeName = 'themes.' . $theme->getCodename() . '.' . $baseName;
			$js = $this->getByCodeName($codeName);
			if (!$js)
			{
				$js = $this->getNewDocumentInstance();		
				$js->setCodename($codeName);
				$js->setLabel($baseName);
				$js->setThemeid($theme->getId());
				$js->setProjectpath('themes/' . $theme->getCodename() . '/js/' . $baseName .'.js');
				$js->save();
				$theme->addJavascript($js);
			}
			$jss[] = $js->getId();
		}
		
		$toDelete = array();
		foreach ($theme->getJavascriptArray() as $js) 
		{
			if (!in_array($js->getId(), $jss))
			{
				$toDelete[] =  $js->getId();
				$theme->removeJavascript($js);	
			}
		}
			
		if (count($toDelete))
		{
			$this->createQuery()->add(Restrictions::in('id', $toDelete))
				->delete();
		}
	}
	
	/**
	 * @param theme_persistentdocument_javascript $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
//	protected function preSave($document, $parentNodeId = null)
//	{
//
//	}

	/**
	 * @param theme_persistentdocument_javascript $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function preInsert($document, $parentNodeId = null)
//	{
//	}

	/**
	 * @param theme_persistentdocument_javascript $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function postInsert($document, $parentNodeId = null)
//	{
//	}

	/**
	 * @param theme_persistentdocument_javascript $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function preUpdate($document, $parentNodeId = null)
//	{
//	}

	/**
	 * @param theme_persistentdocument_javascript $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function postUpdate($document, $parentNodeId = null)
//	{
//	}

	/**
	 * @param theme_persistentdocument_javascript $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function postSave($document, $parentNodeId = null)
//	{
//	}

	/**
	 * @param theme_persistentdocument_javascript $document
	 * @return void
	 */
//	protected function preDelete($document)
//	{
//	}

	/**
	 * @param theme_persistentdocument_javascript $document
	 * @return void
	 */
//	protected function preDeleteLocalized($document)
//	{
//	}

	/**
	 * @param theme_persistentdocument_javascript $document
	 * @return void
	 */
//	protected function postDelete($document)
//	{
//	}

	/**
	 * @param theme_persistentdocument_javascript $document
	 * @return void
	 */
//	protected function postDeleteLocalized($document)
//	{
//	}

	/**
	 * @param theme_persistentdocument_javascript $document
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
	 * @param theme_persistentdocument_javascript $document
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
	 * @param theme_persistentdocument_javascript $document
	 * @param String $tag
	 * @return void
	 */
//	public function tagAdded($document, $tag)
//	{
//	}

	/**
	 * @param theme_persistentdocument_javascript $document
	 * @param String $tag
	 * @return void
	 */
//	public function tagRemoved($document, $tag)
//	{
//	}

	/**
	 * @param theme_persistentdocument_javascript $fromDocument
	 * @param f_persistentdocument_PersistentDocument $toDocument
	 * @param String $tag
	 * @return void
	 */
//	public function tagMovedFrom($fromDocument, $toDocument, $tag)
//	{
//	}

	/**
	 * @param f_persistentdocument_PersistentDocument $fromDocument
	 * @param theme_persistentdocument_javascript $toDocument
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
	 * @param theme_persistentdocument_javascript $document
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
	 * @param theme_persistentdocument_javascript $newDocument
	 * @param theme_persistentdocument_javascript $originalDocument
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
	 * @param theme_persistentdocument_javascript $newDocument
	 * @param theme_persistentdocument_javascript $originalDocument
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
	 * @param theme_persistentdocument_javascript $document
	 * @param string $lang
	 * @param array $parameters
	 * @return string
	 */
//	public function generateUrl($document, $lang, $parameters)
//	{
//	}

	/**
	 * @param theme_persistentdocument_javascript $document
	 * @return integer | null
	 */
//	public function getWebsiteId($document)
//	{
//	}

	/**
	 * @param theme_persistentdocument_javascript $document
	 * @return website_persistentdocument_page | null
	 */
//	public function getDisplayPage($document)
//	{
//	}

	/**
	 * @param theme_persistentdocument_javascript $document
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
	 * @param theme_persistentdocument_javascript $document
	 * @param string $bockName
	 * @return array with entries 'module' and 'template'. 
	 */
//	public function getSolrserachResultItemTemplate($document, $bockName)
//	{
//		return array('module' => 'theme', 'template' => 'Theme-Inc-JavascriptResultDetail');
//	}
}
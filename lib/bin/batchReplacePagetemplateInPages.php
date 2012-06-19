<?php
/* @var $arguments array */
$arguments = isset($arguments) ? $arguments : array();

$chunckSize = $arguments[2];
$lang = $arguments[3];
RequestContext::getInstance()->setLang($lang);

echo 'Starting with chunksize: ', $chunckSize, ' and lang: ', $lang, PHP_EOL;
$tm = f_persistentdocument_TransactionManager::getInstance();
$pp = f_persistentdocument_PersistentProvider::getInstance();
try
{
	$tm->beginTransaction();
	$tms = theme_ModuleService::getInstance();
	
	$toReplace = theme_persistentdocument_pagetemplate::getInstanceById($arguments[0]);
	$replaceBy = theme_persistentdocument_pagetemplate::getInstanceById($arguments[1]);
	
	$pages = website_PageService::getInstance()->createQuery()
		->add(Restrictions::notin('publicationstatus', $tms->getDeadPageStatuses()))
		->add(Restrictions::eq('template', $toReplace->getCodename()))
		->addOrder(Order::asc('id'))
		->setMaxResults($chunckSize)
		->find();
	
	foreach ($pages as $page)
	{
		/* @var website_persistentdocument_page */
		echo $page->getId() , ' ';
		$page->setTemplate($replaceBy->getCodename());
		$pp->updateDocument($page);
		f_DataCacheService::getInstance()->clearCacheByDocId(f_DataCachePatternHelper::getIdPattern($page->getId()));
	}
	
	echo PHP_EOL, (count($pages) < $chunckSize) ? 'END' : 'CONTINUE';
	$tm->commit();
}
catch (Exception $e)
{
	$tm->rollBack($e);
	echo PHP_EOL . $e->getMessage(), '.';
}
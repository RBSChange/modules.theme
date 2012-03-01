<?php
$toReplaceCodename = $_POST['argv'][0];
$replaceByCodename = $_POST['argv'][1];
$chunckSize = $_POST['argv'][2];

echo 'Starting with chunksize: ', $chunckSize, PHP_EOL;
$tm = f_persistentdocument_TransactionManager::getInstance();
$pp = f_persistentdocument_PersistentProvider::getInstance();
try
{
	$tm->beginTransaction();
	$tms = theme_ModuleService::getInstance();
	$pages = website_PageService::getInstance()->createQuery()
		->add(Restrictions::notin('publicationstatus', $tms->getDeadPageStatuses()))
		->add(Restrictions::eq('template', $toReplaceCodename))
		->addOrder(Order::asc('id'))
		->setMaxResults($chunckSize)
		->find();
	
	foreach ($pages as $page)
	{
		echo $page->getId() , ' ';
		$page->setTemplate($replaceByCodename);
		$pp->updateDocument($page);
		f_DataCacheService::getInstance()->clearCacheByPattern(f_DataCachePatternHelper::getModelPattern($page->getDocumentModelName()));
		f_DataCacheService::getInstance()->clearCacheByDocId(f_DataCachePatternHelper::getIdPattern($page->getId()));
	}
	
	echo PHP_EOL, (count($pages) != $chunckSize) ? 'END' : 'CONTINUE';
	$tm->commit();
}
catch (Exception $e)
{
	$tm->rollBack($e);
	echo PHP_EOL . $e->getMessage(), '.';
}
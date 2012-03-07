<?php
$chunckSize = $_POST['argv'][2];

echo 'Starting with chunksize: ', $chunckSize, PHP_EOL;
$tm = f_persistentdocument_TransactionManager::getInstance();
$pp = f_persistentdocument_PersistentProvider::getInstance();
try
{
	$tm->beginTransaction();
	$tms = theme_ModuleService::getInstance();
	
	$toReplace = theme_persistentdocument_pagetemplate::getInstanceById($_POST['argv'][0]);
	$replaceBy = theme_persistentdocument_pagetemplate::getInstanceById($_POST['argv'][1]);
	
	$websites = website_WebsiteService::getInstance()->createQuery()
		->add(Restrictions::eq('allowedpagetemplate', $toReplace))
		->addOrder(Order::asc('id'))
		->setMaxResults($chunckSize)
		->find();
	
	foreach ($websites as $website)
	{
		/* @var website_persistentdocument_website */
		echo $website->getId() , ' ';
		$website->removeAllowedpagetemplate($toReplace);
		$website->addAllowedpagetemplate($replaceBy);
		$pp->updateDocument($website);
		f_DataCacheService::getInstance()->clearCacheByPattern(f_DataCachePatternHelper::getModelPattern($website->getDocumentModelName()));
		f_DataCacheService::getInstance()->clearCacheByDocId(f_DataCachePatternHelper::getIdPattern($website->getId()));
	}
	
	echo PHP_EOL, (count($websites) < $chunckSize) ? 'END' : 'CONTINUE';
	$tm->commit();
}
catch (Exception $e)
{
	$tm->rollBack($e);
	echo PHP_EOL . $e->getMessage(), '.';
}
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
	
	$topics = website_TopicService::getInstance()->createQuery()
		->add(Restrictions::eq('allowedpagetemplate', $toReplace))
		->addOrder(Order::asc('id'))
		->setMaxResults($chunckSize)
		->find();
	
	foreach ($topics as $topic)
	{
		/* @var website_persistentdocument_topic */
		echo $topic->getId() , ' ';
		$topic->removeAllowedpagetemplate($toReplace);
		$topic->addAllowedpagetemplate($replaceBy);
		$pp->updateDocument($topic);
	}
	
	echo PHP_EOL, (count($topics) < $chunckSize) ? 'END' : 'CONTINUE';
	$tm->commit();
}
catch (Exception $e)
{
	$tm->rollBack($e);
	echo PHP_EOL . $e->getMessage(), '.';
}
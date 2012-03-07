<?php
class theme_PagetemplateReplacementTask extends task_SimpleSystemTask  
{
	/**
	 * @see task_SimpleSystemTask::execute()
	 */
	protected function execute()
	{
		$chunkSize = Framework::getConfigurationValue('modules/theme/pagetemplateReplacementChunkSize');
		$toReplace = DocumentHelper::getDocumentInstanceIfExists($this->getParameter('toReplaceId'));
		if (!($toReplace instanceof theme_persistentdocument_pagetemplate))
		{
			Framework::error(__METHOD__ . ' Invalid toReplaceId:' . $this->getParameter('toReplaceId'));
		}
		$replaceBy = DocumentHelper::getDocumentInstanceIfExists($this->getParameter('replaceById'));
		if (!($replaceBy instanceof theme_persistentdocument_pagetemplate))
		{
			Framework::error(__METHOD__ . ' Invalid replaceById: ' . $this->getParameter('replaceById'));
		}
		
		// Update websites.
		$batchPath = f_util_FileUtils::buildRelativePath('modules', 'theme', 'lib', 'bin', 'batchReplacePagetemplateInWebsites.php');
		$startId = 0;
		do
		{
			$result = f_util_System::execHTTPScript($batchPath, array($toReplace->getId(), $replaceBy->getId(), $chunkSize));
			if (f_util_StringUtils::endsWith($result, 'END'))
			{
				break;
			}
			elseif (!f_util_StringUtils::endsWith($result, 'CONTINUE'))
			{
				throw new Exception($result);
			}
			$this->plannedTask->ping();
		}
		while (true);
		
		// Update topics.
		$batchPath = f_util_FileUtils::buildRelativePath('modules', 'theme', 'lib', 'bin', 'batchReplacePagetemplateInTopics.php');
		$startId = 0;
		do
		{
			$result = f_util_System::execHTTPScript($batchPath, array($toReplace->getId(), $replaceBy->getId(), $chunkSize));
			if (f_util_StringUtils::endsWith($result, 'END'))
			{
				break;
			}
			elseif (!f_util_StringUtils::endsWith($result, 'CONTINUE'))
			{
				throw new Exception($result);
			}
			$this->plannedTask->ping();
		}
		while (true);
			
		// Update pages.
		$batchPath = f_util_FileUtils::buildRelativePath('modules', 'theme', 'lib', 'bin', 'batchReplacePagetemplateInPages.php');
		$startId = 0;
		do
		{
			$result = f_util_System::execHTTPScript($batchPath, array($toReplace->getCodename(), $replaceBy->getCodename(), $chunkSize));
			if (f_util_StringUtils::endsWith($result, 'END'))
			{
				break;
			}
			elseif (!f_util_StringUtils::endsWith($result, 'CONTINUE'))
			{
				throw new Exception($result);
			}
			$this->plannedTask->ping();
		}
		while (true);
	}
}
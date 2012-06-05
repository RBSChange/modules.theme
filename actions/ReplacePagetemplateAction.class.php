<?php
/**
 * theme_ReplacePagetemplateAction
 * @package modules.theme.actions
 */
class theme_ReplacePagetemplateAction extends change_JSONAction
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		$result = array();

		$toReplace = theme_persistentdocument_pagetemplate::getInstanceById($request->getParameter('toReplaceId'));
		$replaceBy = theme_persistentdocument_pagetemplate::getInstanceById($request->getParameter('replaceById'));
		theme_PagetemplateService::getInstance()->replacePagetemplate($toReplace, $replaceBy);
		
		$actionName = 'replacedBy.pagetemplate';
		$info = array('replacedById' => $replaceBy->getId(), 'replacedByCodename' => $replaceBy->getCodename(), 'replacedByLabel' => $replaceBy->getLabel());
		UserActionLoggerService::getInstance()->addCurrentUserDocumentEntry($actionName, $toReplace, $info, 'theme');
		$actionName = 'replace.pagetemplate';
		$info = array('replacedId' => $toReplace->getId(), 'replacedCodename' => $toReplace->getCodename(), 'replaceLabel' => $toReplace->getLabel());
		UserActionLoggerService::getInstance()->addCurrentUserDocumentEntry($actionName, $replaceBy, $info, 'theme');

		return $this->sendJSON($result);
	}
}
<?php
/**
 * @package modules.theme
 */
class theme_GetBindingAction extends change_Action
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		header("Expires: " . gmdate("D, d M Y H:i:s", time()+28800) . " GMT");
		header('Content-type: text/xml');
	    $rq = RequestContext::getInstance();
	    $rq->setUILangFromParameter($request->getParameter('uilang'));		
	    try 
	    {
        	$rq->beginI18nWork($rq->getUILang());
			$binding = $request->getParameter('binding');
			$xblDom = theme_ModuleService::getInstance()->getEditVariablesBinding($binding);
			echo $xblDom->saveXML();
			$rq->endI18nWork();
	    } 
	    catch (Exception  $e)
	    {
	    	$rq->endI18nWork($e);
	    	f_web_http_Header::setStatus(404);
	    	echo $e->getMessage();
	    }  
		return change_View::NONE;
	}
}

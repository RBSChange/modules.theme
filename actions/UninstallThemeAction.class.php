<?php
/**
 * theme_ImportThemeAction
 * @package modules.theme.actions
 */
class theme_UninstallThemeAction extends f_action_BaseJSONAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		try 
		{
			$theme = $this->getThemeFromRequest($request);
			$codename = $theme->getCodename();
			
			$tms = theme_ModuleService::getInstance();
			$result = website_PageService::getInstance()->createQuery()
				->setProjection(Projections::count('id', 'pagecount'))
				->add(Restrictions::notin('publicationstatus', $tms->getDeadPageStatuses()))
				->add(Restrictions::like('template', $codename.'/', MatchMode::START()))
				->find();
			if ($result[0]['pagecount'] > 0)
			{
				$msg = LocaleService::getInstance()->transBO('m.theme.bo.errors.uninstall-used-theme', array('ucf'), array('pageCount' => $result[0]['pagecount']));
				return $this->sendJSONError($msg , true);
			}
			
			$theme->delete();
			theme_ModuleService::getInstance()->removeThemePaths($codename);
			
			$msg = LocaleService::getInstance()->transBO('m.theme.bo.general.uninstall-succes', array('ucf'), array('codeName' => $codename));
			return $this->sendJSON(array('text' => $msg));
		}
		catch (Exception $e)
		{
			Framework::exception($e);
		}
		return $this->sendJSONError(LocaleService::getInstance()->transBO('m.theme.bo.errors.uninstall-error', array('ucf')));		
	}
		
	/**
	 * @param Request $request
	 * @return theme_persistentdocument_theme
	 */
	private function getThemeFromRequest($request)
	{
		return DocumentHelper::getDocumentInstance($this->getDocumentIdFromRequest($request), 'modules_theme/theme');
	}
}
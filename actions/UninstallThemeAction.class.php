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
			
			$result = website_PageService::getInstance()->createQuery()
				->setProjection(Projections::count('id', 'pagecount'))
				->add(Restrictions::published())
				->add(Restrictions::like('template', $codename.'/', MatchMode::START()))
				->find();
			if ($result[0]['pagecount'] > 0)
			{
				$msg = f_Locale::translate('&modules.theme.bo.errors.Uninstall-used-theme;', array('pageCount' => $result[0]['pagecount']));
				return $this->sendJSONError($msg , true);
			}
			
			$theme->delete();
			theme_ModuleService::getInstance()->removeThemePaths($codename);
			
			$msg = f_Locale::translate('&modules.theme.bo.general.Uninstall-succes;', array('codeName' => $codename));
			return $this->sendJSON(array('text' => $msg));
		}
		catch (Exception $e)
		{
			Framework::exception($e);
		}
		return $this->sendJSONError(f_Locale::translate('&modules.theme.bo.errors.Uninstall-error;', true));		
	}
	
	public function isSecure()
	{
		return true;
	}
	
	/**
	 * @param Request $request
	 * @return theme_persistentdocument_theme
	 */
	private function getThemeFromRequest($request)
	{
		return $this->getDocumentInstanceFromRequest($request);
	}
}
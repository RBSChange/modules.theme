<?php
/**
 * theme_LoadReplacePagetemplateInfosAction
 * @package modules.theme.actions
 */
class theme_LoadReplacePagetemplateInfosAction extends f_action_BaseJSONAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$result = array();

		$query = theme_PagetemplateService::getInstance()->createQuery()->add(Restrictions::published());
		$query->createCriteria('theme'); // Ignore dashboard pagetemplates.
		$excludeId = $request->getParameter('excludeId', null);
		foreach ($query->addOrder(Order::asc('theme.label'))->find() as $template)
		{
			$item = array(
				'label' => $template->getLabel(),
				'codename' => $template->getCodename(),
				'id' => $template->getId(),
				'disabled' => ($template->getId() == $excludeId),
				'hasPreviewImage' => ($template->getThumbnail() !== null)
			);
			$result[] = $item;
			foreach ($template->getPagetemplatedeclinationArrayInverse() as $declination)
			{
				$item = array(
					'label' => '  ' . $declination->getLabel(),
					'codename' => $declination->getCodename(),
					'id' => $declination->getId(),
					'disabled' => ($declination->getId() == $excludeId),
					'hasPreviewImage' => ($declination->getThumbnail() !== null)
				);
				$result[] = $item;
			}
		}
		return $this->sendJSON($result);
	}
}
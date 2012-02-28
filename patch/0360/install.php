<?php
/**
 * theme_patch_0360
 * @package modules.theme
 */
class theme_patch_0360 extends patch_BasePatch
{
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		$this->executeLocalXmlScript('init.xml');
	}
}
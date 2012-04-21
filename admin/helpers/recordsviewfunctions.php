<?php
defined('_JEXEC') or die('Restricted Access');

class ET_RecordsHelper
{
	public function getEasytableMetaItem ($pk = '')
	{
		$jInput = JFactory::getApplication()->input;

		// Make sure we have a pk to work with
		if(empty($pk))
		{
			$pk = $jInput->get('id','');
			if(empty($pk)) return false;
		}

		// Load the table model and get the item
		$model = $this->getModel();
		$item = $model->getItem();

		return $item;
	}
}


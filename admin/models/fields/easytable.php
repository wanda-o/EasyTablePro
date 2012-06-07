<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */
defined('_JEXEC') or die ('Restricted Access');
	

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');

class JFormFieldEasyTable extends JFormFieldList
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	public	$type = 'EasyTable';

	protected function getOptions()
	{
		// Initialise variables.
		$options	= array();
		$db = JFactory::getDBO();
		$result ='';

		// Get array of tables to build each option from...
		$optionsQuery = $db->getQuery(true);
		$optionsQuery->select('id as value, easytablename as text');
		$optionsQuery->from('#__easytables');
		$optionsQuery->where('published = 1');
		$optionsQuery->orderby('easytablename');

		$db->setQuery($optionsQuery);
		$options = $db->loadObjectList();
		// Don't forget to prefix it with a "None Selected" options
		$noneSelected = new stdClass();
		$noneSelected->value = 0;
		$noneSelected->text = '-- '.JText::_('COM_EASYTABLEPRO_LABEL_NONE_SELECTED').' --';
		array_splice($options,0,0,array($noneSelected));

		return $options;
	}
}

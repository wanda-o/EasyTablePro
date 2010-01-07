<?php
	defined('_JEXEC') or die ('Restricted Access');
	
	class JElementEasyTable extends JElement 
	{
		/**
		 * Element name
		 *
		 * @access	protected
		 * @var		string
		 */
		var	$_name = 'EasyTable';
	
		function fetchElement($name, $value, &$node, $control_name)
		{
			$db =& JFactory::getDBO();

			$elementQuery = 'SELECT id, easytablename FROM #__easytables WHERE published = 1 ORDER BY easytablename';

			$db->setQuery($elementQuery);
			$options = $db->loadObjectList();
			$noneSelected = array("text" => JText::_( 'None' ));
			array_splice($options,0,0,$noneSelected);
			
			return JHTML::_('select.genericlist',  $options, $control_name. '[' . $name . ']', 'class="inputbox"', 'id','easytablename', $value, $control_name . $name);
		}
	}
		
	class JElementEasyTableFields extends JElement 
	{
		/**
		 * Element name
		 *
		 * @access	protected
		 * @var		string
		 */
		var	$_name = 'EasyTableFields';
	
		function fetchElement($name, $value, &$node, $control_name)
		{
			$db =& JFactory::getDBO();
			$id = $this->id;

			$elementQuery = "SELECT id, fieldalias FROM #__easytables_table_meta WHERE easytable_id = $id ORDER BY fieldalias";

			$db->setQuery($elementQuery);
			$options = $db->loadObjectList();
			$noneSelected = array("text" => JText::_( 'None' ));
			array_splice($options,0,0,$noneSelected);
			
			return JHTML::_('select.genericlist',  $options, $control_name. '[' . $name . ']', 'class="inputbox"', 'id','fieldalias', $value, $control_name . $name);
		}
	}
<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2010- Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */
	defined('_JEXEC') or die ('Restricted Access');
	
	class JElementEasyTableFields extends JElement 
	{
		/**
		 * Element name
		 *
		 * @access	protected
		 * @var		string
		 */
		var $_name = 'EasyTableFields';
	
		function fetchElement($name, $value, &$node, $control_name)
		{
			$db =& JFactory::getDBO();
			$result ='';

			if($name = 'key_field')
			{
				global $et_current_table_id;
				$id = $et_current_table_id;
				
				if($id)
				{
					$elementQuery = 'SELECT id,label FROM #__easytables_table_meta WHERE easytable_id = '.$id.' ORDER BY position';
	
					$db->setQuery($elementQuery);
					$options = $db->loadObjectList();
					$noneSelected = array();
					$noneSelected[] = array('id' => 0,'label' => '-- '.JText::_( "None Selected" ).' --');
					array_splice($options,0,0,$noneSelected);
	
					$result = JHTML::_('select.genericlist',  $options, $control_name. '[' . $name . ']', 'class="inputbox"', 'id','label', $value, $control_name . $name);
				}
			}

			return $result;
		}
	}

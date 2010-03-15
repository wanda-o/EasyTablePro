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
            $result ='';

            if($name = 'id')
            {
                $elementQuery = 'SELECT id, easytablename FROM #__easytables WHERE published = 1 ORDER BY easytablename';

                $db->setQuery($elementQuery);
                $options = $db->loadObjectList();
                $noneSelected = array("text" => JText::_( 'None' ));
                array_splice($options,0,0,$noneSelected);
                
                $result = JHTML::_('select.genericlist',  $options, $control_name. '[' . $name . ']', 'class="inputbox"', 'id','easytablename', $value, $control_name . $name);
            }
            elseif($name = 'field')
            {
                $id = $this->stid;
                if($id)
                {
                    $elementQuery = 'SELECT label, fieldalias FROM #__easytables_table_meta WHERE easytable_id = '.$id.' ORDER BY id';
    
                    $db->setQuery($elementQuery);
                    $options = $db->loadObjectList();
                    $noneSelected = array("text" => JText::_( 'None' ));
                    array_splice($options,0,0,$noneSelected);
    
                    $result = JHTML::_('select.genericlist',  $options, $control_name. '[' . $name . ']', 'class="inputbox"', 'fieldalias','easytablename', $value, $control_name . $name);
                }
            }

            return $result;
		}
	}

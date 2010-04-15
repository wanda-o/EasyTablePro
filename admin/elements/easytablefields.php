<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2010- Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */
	defined('_JEXEC') or die ('Restricted Access');
	jimport( 'joomla.application.component.view');	
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

			if(($name == 'key_field') || ($name == 'sort_field') || ($name == 'filter_field'))
			{
				global $et_current_table_id;
				$id = $et_current_table_id;

				if(empty( $id )) {
					$theOpt = JRequest::getVar('option','No Table Option');
					if($theOpt == 'com_menus')
					{
						jimport( 'joomla.application.menu' );
						$menuIdArray = JRequest::getVar('cid',0);
						$menuId = $menuIdArray[0];
						$menu       =& JMenu::getInstance('site');
						$menuItem =& $menu->getItem($menuId);
						if($menuItem) {
							$link = $menuItem->link;
							$urlQry = parse_url ( $link, PHP_URL_QUERY );	// get just the qry section of the link
							parse_str ($urlQry, $linkparts);				// convert it to an array
							// echo 'ID from linkparts = '.$linkparts['id'].'<BR />';
	
							$id = (int) isset($linkparts['id'])?$linkparts['id']:0;
						}
						else
						{
							$id = 0;
						}
					}
				}

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
				else
				{
					$result = "A Table must be selected before you can use these settings.";
				}
			}

			return $result;
		}
	}

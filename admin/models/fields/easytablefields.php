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

class JFormFieldEasyTableFields extends JFormFieldList
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	public $type = 'EasyTableFields';

	protected function getOptions()
	{
		$db = JFactory::getDBO();
		$result ='';

		// Get our table ID
		$jinput = JFactory::getApplication()->input;
		$id = $jinput->get('id', null);

		if(empty( $id )) {
			$theOpt = $jinput->get('option','No Table Option');
			if($theOpt == 'com_menus')
			{
				jimport( 'joomla.application.menu' );
				$menuIdArray = JRequest::getVar('cid',0);
				$menuId      = $menuIdArray[0];
				$menu        = JMenu::getInstance('site');
				$menuItem    = $menu->getItem($menuId);
				if($menuItem) {
					$link = $menuItem->link;
					$urlQry = parse_url ( $link, PHP_URL_QUERY );	// get just the qry section of the link
					parse_str ($urlQry, $linkparts);				// convert it to an array

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
			$elementQuery = 'SELECT id as value, label as text FROM #__easytables_table_meta WHERE easytable_id = '.$id.' ORDER BY position';

			$db->setQuery($elementQuery);
			$options = $db->loadObjectList();
			$noneSelected = new stdClass();
			$noneSelected->value = 0;
			$noneSelected->text = '-- '.JText::_( 'COM_EASYTABLEPRO_LABEL_NONE_SELECTED' ).' --';
			array_splice($options,0,0,array($noneSelected));
		}
		else
		{
			$options = array(JText::_("Select A Table first..."));
		}
		return $options;
	}
}

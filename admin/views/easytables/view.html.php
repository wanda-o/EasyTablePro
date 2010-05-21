<?php
/**
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

jimport( 'joomla.application.component.view');

$pvf = ''.JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'viewfunctions.php';
require_once $pvf;

/**
 * HTML View class for the EasyTables Component
 *
 * @package    EasyTables
 * @subpackage Views
 */

class EasyTableViewEasyTables extends JView
{
	function getSearchableTick ($rowId, $flag)
	{
		$btn_title = '';
		$theImageString = '';
		if( $flag == '' )
		{
			$theImageString = 'components'.DS.'com_'._cppl_this_com_name.DS.'assets/images/GlobalIcon16x16.png';
			$btn_title = JText::_( "Click here to prevent access to this table by Joomla's built-in search function." );
		}
		else if($flag)
		{
			$theImageString = 'images/tick.png';
			$btn_title = JText::_( "Click here to prevent access to this table by Joomla's built-in search function." );
		}
		else
		{
			$theImageString = 'images/publish_x.png';
			$btn_title = JText::_( "Click here to allow Joomla's built-in search function to access this table." );
		}

		$theSearchableImage = '<img src="'.$theImageString.'" name="'.$rowId.'_img" border="0" />';
		$theSearchableButton = '<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$rowId.'\',\'toggleSearch\');" title="'.$btn_title.'" >'.$theSearchableImage.'</a>';
		
		return($theSearchableButton);
	}

	/**
	 * EasyTables view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
		//get the document and load the js support file
		$doc =& JFactory::getDocument();
		$doc->addStyleSheet(JURI::base().'components'.DS.'com_'._cppl_this_com_name.DS._cppl_base_com_name.'.css');
		$doc->addScript(JURI::base().'components'.DS.'com_'._cppl_this_com_name.DS._cppl_base_com_name.'.js');
		
		/**
		 *
		 * Let's do a version check - it's always good to use the newest version.
		 *
		**/

		// Get data from the model
		$subscriber_ver_array = ET_VHelpers::et_version('subscriber');
		$rows =& $this->get('data');
		$this->assignRef('rows',$rows);
		$this->assign('et_current_version',ET_VHelpers::current_version());
		$this->assign('et_subscriber_version',$subscriber_ver_array["version"]);
		$this->assign('et_subscriber_tip',$subscriber_ver_array["tip"]);
		parent::display($tpl);
	}
}

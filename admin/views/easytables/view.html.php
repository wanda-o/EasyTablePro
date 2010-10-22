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
	function getEditorLink ($locked, $rowId, $tableName)
	{
		$link_text = JText::_( 'EDIT_PROPERTIES_AND_STRUCTURE_OF' ).' \''.$tableName.'\' '.($locked ? JText::_( 'DISABLED_BECAUSE_THE_TABLE_IS_LOCKED_' ) : '');
		$theEditLink = '<span class="hasTip" title="'.$link_text.'" />'.$tableName.'</span>';

		if( !$locked )
		{
			$theEditLink = '<span class="hasTip" title="'.$link_text.'" />'.'<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$rowId.'\',\'edit\');" title="'.$link_text.'" >'.$tableName.'</a><span />';
		}

		return($theEditLink);
	}

	function publishedIcon ($locked, $row, $i)
	{
		$btn_text = JText::_( ( $row->published ? 'PUBLISHED_BTN':'UNPUBLISHED_BTN') ).' \''.$row->easytablename.'\' '.($locked ? JText::_( 'DISABLED_BECAUSE_THE_TABLE_IS_LOCKED_' ) : '');
		$theImageURL = 'components'.DS.'com_'._cppl_this_com_name.DS.'assets/images/'.( $locked ? 'disabled_' : '' ).'tick.png';
		$theBtn = '<img src="'.$theImageURL.'" class="hasTip" alt="'.$btn_text.'" title="'.$btn_text.'" border="0" >';

		if( !$locked )
		{
			$theBtn = "<span class=\"hasTip\" title=\"$btn_text\">".JHTML::_( 'grid.published',  $row, $i ).'</span>';
		}

		return $theBtn;
	}


	function getDataEditorIcon ($locked, $rowId, $tableName)
	{
		$btn_text = JText::_( 'EDIT_TABLE_DATA_IN_' ).' \''.$tableName.'\' '.($locked ? JText::_( 'DISABLED_BECAUSE_THE_TABLE_IS_LOCKED_' ) : '');
		$theImageURL = 'components'.DS.'com_'._cppl_this_com_name.DS.'assets/images/'.( $locked ? 'disabled_' : '' ).'edit.png';
		$theEditBtn = '<span class="hasTip" title="'.JText::_( 'EDIT_RECORDS' ).'::'.$btn_text.'" /><img src="'.$theImageURL.'" style="text-decoration: none; color: #333;" />';

		if( !$locked )
		{
			$theEditBtn = '<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$rowId.'\',\'editData\');" title="'.$btn_text.'" >'.$theEditBtn.'</a>';
		}

		return($theEditBtn);
	}

	function getSearchableTick ($rowId, $flag, $locked=true)
	{
		$btn_text = '';
		$theImageString = 'components'.DS.'com_'._cppl_this_com_name.DS.'assets/images/'.( $locked ? 'disabled_' : '' );
		if( $flag == '' )
		{
			$theImageString .= 'GlobalIcon16x16.png';
			$btn_text = JText::_( "CLICK_HERE_TO_ALLOW_ACCESS_BY_JOOMLA_S_BUILT_IN_SEARCH_FUNCTION_" ).($locked ? JText::_( 'DISABLED_BECAUSE_THE_TABLE_IS_LOCKED_' ) : '');
		}
		else if($flag)
		{
			$theImageString .= 'tick.png';
			$btn_text = JText::_( "CLICK_HERE_TO_PREVENT_ACCESS_TO_THIS_TABLE_BY_JOOMLA_S_BUILT_IN_SEARCH_FUNCTION_" ).($locked ? JText::_( 'DISABLED_BECAUSE_THE_TABLE_IS_LOCKED_' ) : '');
		}
		else
		{
			$theImageString .= 'publish_x.png';
			$btn_text = JText::_( "CLICK_HERE_TO_USE_THE_GLOBAL_PREFERENCES_TO_CONTROL_JOOMLA_S_BUILT_IN_SEARCH_FUNCTION_TO_ACCESS_THIS_TABLE_" ).($locked ? JText::_( 'DISABLED_BECAUSE_THE_TABLE_IS_LOCKED_' ) : '');
		}

		$theSearchableImage = '<img src="'.$theImageString.'" name="'.$rowId.'_img" border="0" />';
		$theSearchableButton = '<span class="hasTip" title="'.$btn_text.'"><a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$rowId
								.'\',\'toggleSearch\');" title="'.$btn_text.'" >'.$theSearchableImage.'</a></span>';
		
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

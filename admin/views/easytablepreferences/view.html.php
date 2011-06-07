<?php
/**
 * @package	   EasyTables
 * @author	   Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @date	   Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the EasyTables Component
 *
 * @package	   EasyTables
 * @subpackage Views
 */
$pmf = ''.JPATH_COMPONENT_ADMINISTRATOR.'/helpers/managerfunctions.php';
require_once $pmf;

class EasyTableVieweasytablepreferences extends JView
{
	function getCheckbox( $n, $v, $l='', $chkd=false, $disabled=false )
	{
		if($l=='') $l = $v;
		$cb = '<label><input type="checkbox" name="'.$n.'" value="'.$v.'" '.($chkd?'checked':'').' '.($disabled?'disabled':'').'>'.$l.'</label>';
		return $cb;
	}


	function createAccessCheckboxes ($groupName, $initialValuesArray)
	{
		$theCBHTML  = $this->getCheckbox($groupName,'Super Administrator',JText::_( 'Super Administrator' ), in_array( 'Super Administrator', $initialValuesArray ), true).'<br />';
		$theCBHTML .= $this->getCheckbox($groupName,'Administrator',JText::_( 'Administrator' ), in_array( 'Administrator', $initialValuesArray )).'<br />';
		$theCBHTML .= $this->getCheckbox($groupName,'Manager',JText::_( 'Manager' ), in_array( 'Manager', $initialValuesArray ));
		return $theCBHTML;
	}

	/**
	 * View display method
	 * 
	 * @return void
	 **/
	function display($tpl = null)
	{
		$user =& JFactory::getUser();
		$jAp=& JFactory::getApplication();
		//get the document and load the css & js support files
		$doc =& JFactory::getDocument();
		$u = & JURI::getInstance();
		$doc->addStyleSheet(JURI::base().'components/com_'._cppl_this_com_name.'/'._cppl_base_com_name.'.css');
		$doc->addScript(JURI::base().'components/com_'._cppl_this_com_name.'/assets/js/'._cppl_base_com_name.'settings.js');
		JHTML::_('behavior.tooltip');
		JToolBarHelper::title(JText::_( 'EasyTable Pro Settings' ), 'easytables');
		JToolBarHelper::save('savePreferences');
		JToolBarHelper::apply('applyPreferences');
		JToolBarHelper::cancel('cancelPreferences', JText::_( 'Close' ));

		// Get the current user
		$user =& JFactory::getUser();

		// Get the settings meta record
		$settings = ET_MgrHelpers::getSettings();
		// Allow Access settings
		$aaSettings = explode(',', $settings->get('allowAccess'));
		if(!in_array($user->usertype, $aaSettings))
		{
			global $mainframe;
			$url = 'index.php?option=com_'._cppl_this_com_name;
			$mainframe->redirect($url, JText::_( 'YOU_ARE_NOT_AUTH' ));
		}

		$allowAccess = $this->createAccessCheckboxes( 'allowAccess[]',$aaSettings );

		// Linking Access settings
		$laSettings = explode(',', $settings->get('allowLinkingAccess'));
		$allowLinkingAccess = $this->createAccessCheckboxes( 'allowLinkingAccess[]',$laSettings );

		// Allow Table Management settings
		$tmSettings = explode(',', $settings->get('allowTableManagement'));
		$allowTableManagement = $this->createAccessCheckboxes( 'allowTableManagement[]',$tmSettings );

		// Allow Data Upload settings
		$duSettings = explode(',', $settings->get('allowDataUpload'));
		$allowDataUpload = $this->createAccessCheckboxes( 'allowDataUpload[]',$duSettings );

		// Allow Data Editing settings
		$deSettings = explode(',', $settings->get('allowDataEditing'));
		$allowDataEditing = $this->createAccessCheckboxes( 'allowDataEditing[]',$deSettings );

		// List of restricted tables
		$restrictedTables = $settings->get('restrictedTables','');

		// Maximum File upload size
		$maxFileSize = $settings->get('maxFileSize',3000000);

		// Chunk size for file processing
		$chunkSize = $settings->get('chunkSize', 50);

		// Uninstall Type
		$uninstall_type = $settings->get('uninstall_type', 0);

		$this->assign('allowAccess', $allowAccess);
		$this->assign('allowLinkingAccess', $allowLinkingAccess);
		$this->assign('allowTableManagement', $allowTableManagement);
		$this->assign('allowDataUpload', $allowDataUpload);
		$this->assign('allowDataEditing', $allowDataEditing);
		$this->assign('restrictedTables', $restrictedTables);
		$this->assign('maxFileSize', $maxFileSize);
		$this->assign('chunkSize', $chunkSize);
		$this->assign('uninstall_type', $uninstall_type);
		$this->assign('userType',$user->usertype);

		parent::display($tpl);
	}// function
}// class

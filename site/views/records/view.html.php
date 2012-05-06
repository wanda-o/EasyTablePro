<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

defined('_JEXEC') or die('Restricted Access');
jimport('joomla.application.component.view');
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/general.php';
require_once JPATH_COMPONENT_SITE.'/helpers/viewfunctions.php';

class EasyTableProViewRecords extends JView
{
	protected $item;
	protected $params;
	protected $state;
	protected $user;

	function display ($tpl = null)
	{
		// Initialise variables.
		$jAp		= JFactory::getApplication();
		$jInput		= $jAp->input;
		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$dispatcher	= JDispatcher::getInstance();

		$item			= $this->get('Item');
		$this->item		= $item;
		$this->state	= $this->get('State');
		
		$this->user		= $user;

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));

			return false;
		}


		$id = $item->id;

		// Component wide & menu based params
		$GMParams = $jAp->getParams();
		$params = clone $GMParams;

		$tableParams = new JRegistry();
		$tableParams->loadString( $item->params );
		// Merge them with specific table based params
		$params->merge( $tableParams );

		// Check the view access to the article (the model has already computed the values).
		if ($item->params->get('access-view') != true) {
			if($user->guest) {
				// Redirect to login
				$uri		= JFactory::getURI();
				$return		= $uri->toString();

				$url  = 'index.php?option=com_user&amp;view=login&amp;return='.base64_encode($return);

				$jAp->redirect($url, JText::_('COM_EASYTABLEPRO_SITE_RESTRICTED_TABLE') );				
			} else {
				JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
				return;
			}
		}

		// So our column headings pop out :D (Handy for users that want to put a note in about the field or column sorting
		JHTML::_('behavior.tooltip');

		$show_description = $params->get('show_description',1);
		$show_search = $params->get('show_search',1);
		$show_pagination = $params->get('show_pagination',1);
		$show_pagination_header = $params->get('show_pagination_header',0);
		$show_pagination_footer = $params->get('show_pagination_footer',1);
		$show_created_date = $params->get('show_created_date',1);
		$show_modified_date = $params->get('show_modified_date',0);
		$modification_date_label = $params->get('modification_date_label','');
		$show_page_title = $params->get('show_page_title',1);
		$pageclass_sfx = $params->get('pageclass_sfx','');
		$etet = $item->datatablename?TRUE:FALSE;

		// Better breadcrumbs
		$pathway   = $jAp->getPathway();
		$pathway->addItem($item->easytablename, 'index.php?option=easytablepro&amp;view=table&amp;id='.$id);

		// because the application sets a default page title, we need to get it right from the menu item itself
		// Get the menu item object
		$menus =JSite::getMenu();
		$menu  = $menus->getActive();

		if (is_object( $menu ) && isset($menu->query['view']) && $menu->query['view'] == 'table' && isset($menu->query['id']) && $menu->query['id'] == $id) {
			$menu_params = new JRegistry();
			$menu_params->loadString( $menu->params );
			if (!$menu_params->get( 'page_title')) {
				$params->set('page_title',$item->easytablename);
			}
		} else {
			$params->set('page_title',$item->easytablename);
		}
		$page_title = $params->get( 'page_title' );

		// Get the default image directory from the table.
		$imageDir = $item->defaultimagedir;

		//If required get the document and load the js for table sorting
		$doc = JFactory::getDocument();
		$SortableTable = $params->get ( 'make_tables_sortable' );
		if( $SortableTable ) {
			$doc->addScript(JURI::base().'media/com_easytablepro/js/webtoolkit.sortabletable.js');
		}

		$easytables_table_meta = $item->table_meta;
		$etmCount = count($easytables_table_meta); //Make sure at least 1 field is set to display
		// If any of the fields are designated as eMail load the JS file to allow cloaking.
		if(ET_VHelper::hasEmailType($easytables_table_meta))
			$doc->addScript(JURI::base().'media/com_easytablepro/js/easytableprotable_fe.js');

		// Make sure at least 1 field is set to display - how were users managing to save without a field set ?
		if($etmCount)
		{
			// Get paginated table data
			if($show_pagination)
			{
				$paginatedRecords = $this->get('data');
				$paginatedRecordsFNILV = $this->get('dataFieldsNotInListView');
			}
			else
			{
				$paginatedRecords = $this->get('alldata');
				$paginatedRecordsFNILV = $this->get('alldataFieldsNotInListView');
			}

			// Get pagination object
			$pagination = $this->get('pagination');

		}
		else
		{
			$show_pagination_footer = FALSE;
			$show_pagination_header = FALSE;
			$show_search = FALSE;
			$pagination = FALSE;
			$easytables_table_meta = array(array("Warning EasyTable List View Empty","","","","",""));
			$errObj = new stdClass;
			$errObj->id = 0;
			$errObj->Message = "No fields selceted to display in list view for this table";
			$paginatedRecords = array('Error'=>$errObj);
		}
		// Search
		$search = $db->getEscaped($this->get('search'));
		//Get form link
		$paginationLink = JRoute::_('index.php?option=com_easytablepro&amp;view=easytable&amp;id='.$id.':'.$item->easytablealias);

		// Assing these items for use in the tmpl
		$this->assign('show_description', $show_description);
		$this->assign('show_search', $show_search);
		$this->assign('show_pagination', $show_pagination);
		$this->assign('show_pagination_header', $show_pagination_header);
		$this->assign('show_pagination_footer', $show_pagination_footer);

		$this->assign('show_created_date', $show_created_date);
		$this->assign('show_modified_date', $show_modified_date);
		$this->assign('modification_date_label', $modification_date_label);

		$this->assign('show_page_title', $show_page_title);
		$this->assign('page_title', $page_title);
		$this->assign('pageclass_sfx',$pageclass_sfx);
		
		$this->assign('SortableTable', $SortableTable);

		$this->assign('tableId', $id);
		$this->assign('imageDir', $imageDir);
		$this->assignRef('easytable', $easytable);
		$this->assignRef('easytables_table_meta', $easytables_table_meta);
		$this->assignRef('pagination', $pagination);
		$this->assign('paginationLink', $paginationLink);
		$this->assignRef('paginatedRecords', $paginatedRecords);
		$this->assignRef('paginatedRecordsFNILV', $paginatedRecordsFNILV);
		$this->assign('search',$search);
		$this->assign('etmCount', $etmCount);
		parent::display($tpl);
	}
}
?>

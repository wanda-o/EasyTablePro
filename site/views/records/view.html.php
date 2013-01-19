<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.view');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/general.php';
require_once JPATH_COMPONENT_SITE . '/helpers/viewfunctions.php';

/**
 * EasyTableProViewRecords
 *
 * @package     EasyTable_Pro
 *
 * @subpackage  Views
 *
 * @since       1.0
 */
class EasyTableProViewRecords extends JView
{
	/**
	 * @var
	 */
	protected $easytable;

	/**
	 * @var
	 */
	protected $params;

	/**
	 * @var
	 */
	protected $state;

	/**
	 * @var
	 */
	protected $user;

	/**
	 * display()
	 *
	 * @param   null  $tpl  Tpl file
	 *
	 * @return  bool
	 */
	public function display ($tpl = null)
	{
		// Initialise variables.
		$jAp		= JFactory::getApplication();
		$jInput		= $jAp->input;
		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$dispatcher	= JDispatcher::getInstance();

		$easytable		= $this->get('EasyTable');
		$items			= $this->get('Items');
		$this->items	= $items;
		$this->state	= $this->get('State');
		$this->pagination = $this->get('Pagination');

		// Get the user
		$this->user		= $user;

		// Get the active menu
		$theMenus = $jAp->getMenu();
		$active	= $theMenus->getActive();

		/* Is there an active menu item (there may not be if coming from search...
		 * and the user hasn't created a link to the menu item).
		 */
		if (!$active)
		{
			$menuItemId = $jInput->get('Itemid', 0);

			// It's also possible to not have a menu item id passed in because people hand craft url's
			if ($menuItemId)
			{
				$active = $theMenus->getItem($menuItemId);
			}
			// If we still don't have an active menu then just get the default menu
			if (!$active)
			{
				$active = $theMenus->getDefault();
			}
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));

			return false;
		}

		$id = $easytable->id;

		// Component wide & menu based params
		$GMParams = $jAp->getParams();
		$params = clone $GMParams;

		$tableParams = new JRegistry;
		$tableParams->loadString($easytable->params);

		// Merge them with specific table based params
		$params->merge($tableParams);

		// Check the view access to the article (the model has already computed the values).
		if ($easytable->access_view != true)
		{
			if ($user->guest)
			{
				// Redirect to login
				$uri		= JFactory::getURI();
				$return		= $uri->toString();

				$url  = 'index.php?option=com_users&amp;view=login&amp;return=' . urlencode(base64_encode($return));

				$jAp->redirect($url, JText::_('COM_EASYTABLEPRO_SITE_RESTRICTED_TABLE'));
			}
			else
			{
				JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));

				return;
			}
		}
		// Load the right layout...
		// Load layout from active query (in case it is an alternative menu item)
		if ($layout = $active->params->get('records_layout'))
		{
			$this->setLayout($layout);
		}
		else
		{
			if ($layout = $params->get('records_layout'))
			{
				$this->setLayout($layout);
			}
		}

		// So our column headings pop out :D (Handy for users that want to put a note in about the field or column sorting
		JHTML::_('behavior.tooltip');

		$show_description = $params->get('show_description', 1);
		$show_search = $params->get('show_search', 1);
		$show_pagination = $params->get('show_pagination', 1);
		$show_pagination_header = $params->get('show_pagination_header', 0);
		$show_pagination_footer = $params->get('show_pagination_footer', 1);
		$show_created_date = $params->get('show_created_date', 1);
		$show_modified_date = $params->get('show_modified_date', 0);
		$modification_date_label = $params->get('modification_date_label', '');
		$show_page_title = $params->get('show_page_title', 1);
		$pageclass_sfx = $params->get('pageclass_sfx', '');
		$etet = $easytable->datatablename?true:false;
		$title_leaf = $params->get('title_field', '');

		if ($i = strpos($title_leaf, ':'))
		{
			$title_leaf = substr($title_leaf, $i + 1);
		}
		$full_page_title = $easytable->easytablename;

		// Better breadcrumbs
		$pathway   = $jAp->getPathway();
		$pathway->addItem($easytable->easytablename, 'index.php?option=easytablepro&amp;view=table&amp;id=' . $id);

		// Because the application sets a default page title, we need to get it right from the menu item itself
		// Get the menu item object
		$menus = $jAp->getMenu();
		$menu  = $menus->getActive();

		if (is_object($menu) && isset($menu->query['view']) && $menu->query['view'] == 'table' && isset($menu->query['id']) && $menu->query['id'] == $id)
		{
			$menu_params = new JRegistry;
			$menu_params->loadString($menu->params);

			if (!$menu_params->get('page_title'))
			{
				$params->set('page_title', $full_page_title);
			}
		}
		else
		{
			$params->set('page_title', $full_page_title);
		}
		$page_title = $params->get('page_title');

		// Get the default image directory from the table.
		$imageDir = $easytable->defaultimagedir;

		// If required get the document and load the js for table sorting
		$doc = JFactory::getDocument();
		$SortableTable = $params->get('make_tables_sortable');

		if ($SortableTable)
		{
			$doc->addScript(JURI::base() . 'media/com_easytablepro/js/webtoolkit.sortabletable.js');
		}

		$easytables_table_meta = $easytable->table_meta;

		// Make sure at least 1 field is set to display
		$etmCount = count($easytables_table_meta);

		// If any of the fields are designated as eMail load the JS file to allow cloaking.
		if (ET_VHelper::hasEmailType($easytables_table_meta))
		{
			$doc->addScript(JURI::base() . 'media/com_easytablepro/js/easytableprotable_fe.js');
		}

		// Get form link
		$formAction = JURI::current();

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
		$this->assign('pageclass_sfx', $pageclass_sfx);

		$this->assign('SortableTable', $SortableTable);

		$this->assign('tableId', $id);
		$this->assign('imageDir', $imageDir);
		$this->assignRef('easytable', $easytable);
		$this->assignRef('easytables_table_meta', $easytables_table_meta);
		$this->formAction = $formAction;
		$this->assign('etmCount', $etmCount);
		$this->title_leaf = $title_leaf;
		parent::display($tpl);
	}

	/**
	 * getLeafField()
	 *
	 * @param   int    $title_leaf_id  Pk value for field containing leaf string.
	 *
	 * @param   array  $table_meta     Array of current tables meta data
	 *
	 * @return  string
	 *
	 *@todo Is this used anymore? Removal recommended :D
	 */
	private function getLeafField($title_leaf_id, $table_meta)
	{
		foreach ($table_meta as $fieldMeta)
		{
			if ($title_leaf_id == (int) $fieldMeta['id'])
			{
				return $fieldMeta['fieldalias'];
			}
		}
		return 'id';
	}
}
?>

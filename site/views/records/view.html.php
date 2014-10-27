<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('_JEXEC') or die ('Restricted Access');

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
class EasyTableProViewRecords extends JViewLegacy
{
	protected $easytable;

	/**
	 * @var $params JRegistry
	 */
	protected $params;

	protected $items;

	protected $itemCount;

	protected $pagination;

	protected $state;

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
		/* var $jAp JSite */
		$jAp		= JFactory::getApplication();
		$jInput		= $jAp->input;
		$user		= JFactory::getUser();
		// Get our Joomla version tag
		$this->jvtag = ET_General_Helper::getJoomlaVersionTag();

		$easytable  = $this->get('EasyTable');
		$this->easytable = $easytable;

		if (empty($easytable))
		{
			$id = $jInput->get('id', 0);

			// Throw 404 if no table
			$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_SITE_TABLE_NOT_AVAILABLE', $id), 'ERROR');
			return false;
		}

		// Component wide & menu based params
		$this->params = $this->getParams($jAp);

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

		$id = $easytable->id;

		// Check the view access to the table (the model has already computed the values).
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
				$jAp->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'ERROR');

				return false;
			}
		}

		// Load the right layout...
		$layout = $jInput->getCmd('layout', 'default');

		// Load layout from active query if required (in case it is an alternative menu item)
		if (empty($layout))
		{
			if ($layout = $active->params->get('records_layout'))
			{
				$this->setLayout($layout);
			}
			else
			{
				if ($layout = $this->params->get('records_layout'))
				{
					$this->setLayout($layout);
				}
			}
		}

		$ajaxEnabled = $this->params->get('enable_ajax_tables', 0);
		$wereAjaxing = ($ajaxEnabled && ($layout == 'dt'));
        $ajaxLayout = ($layout == 'dt') || ($layout == 'foo') || ($layout == 'ajax');

		if ($ajaxLayout && !$wereAjaxing)
		{
			$mailSent = ET_General_Helper::notifyAdminsOnError('ajax',
				array(
					'ipaddress' => $jInput->get('REMOTE_ADDR'),
					'url' => $jInput->get('REQUEST_URI'),
					'referrer' => $jInput->get('HTTP_REFERER'),
				)
			);

			if ($mailSent)
			{
				JError::raiseWarning(403, JText::_('COM_EASYTABLEPRO_RECORDS_ERROR_AJAX_NOT_ENABLED'));
			}
			else
			{
				JError::raiseWarning(403, JText::_('COM_EASYTABLEPRO_RECORDS_ERROR_AJAX_NOT_ENABLED0'));
			}

			return false;
		}
		elseif($wereAjaxing)
		{
			if ($this->easytable->record_count < $this->params->get('table_small_sized', 500, ''))
			{
				$jAp->input->set('limit', $this->easytable->record_count);
				$jAp->input->set('limitstart', 0);
				$this->items = $this->get('Items');
			}

			// Load jQuery and relevant AJAX library/plugin
            $this->loadJQuery($jAp);
            $this->loadDataTables($jAp);
		}
		else
		{
			$this->items	  = $this->get('Items');
			$this->itemCount  = count($this->items);
			$this->state	  = $this->get('State');
			$this->pagination = $this->get('Pagination');

			// Do we need a No Results Message?
			if ($this->itemCount == 0)
			{
				$searchTerm = $this->state->get('filter.search');
				$this->sro_showTable = $this->params->get('sro_showtable', 1);

                if ($this->params->get('enable_user_filter', 0))
                {
                    if ($user->id == 0)
                    {
                        $this->noResultsMsg = JText::_('COM_EASYTABLEPRO_SITE_RECORDS_THIS_TABLE_FILTERED_BY_USER');
                    }
                        else
                    {
                        $this->noResultsMsg = JText::_('COM_EASYTABLEPRO_SITE_RECORDS_NO_RECORDS_FOUND_FOR_LOGGED_IN_USER');
                    }
                    $tableNoRMsg = $this->params->get('no_results_msg', '');
                    $this->noResultsMsg .= $tableNoRMsg ? $tableNoRMsg : JText::_('COM_EASYTABLEPRO_SITE_RECORDS_NO_MATCHING');
                }
				elseif ($searchTerm == '')
				{
					$tableSORMsg = $this->params->get('sro_msg', '');
					$this->noResultsMsg = $tableSORMsg ? $tableSORMsg : JText::_('COM_EASYTABLEPRO_SITE_RECORDS_NO_SRO_TERM');
				}
				else
				{
					$tableNoRMsg = $this->params->get('no_results_msg', '');
					$this->noResultsMsg = $tableNoRMsg ? $tableNoRMsg : JText::_('COM_EASYTABLEPRO_SITE_RECORDS_NO_MATCHING');
				}
			}
		}

		// So our column headings pop out :D (Handy for users that want to put a note in about the field or column sorting
		JHTML::_('behavior.tooltip');

		$show_description = $this->params->get('show_description', 1);
		$show_search = $this->params->get('show_search', 1);
		$show_pagination = $this->params->get('show_pagination', 1);
		$show_pagination_header = $this->params->get('show_pagination_header', 0);
		$show_pagination_footer = $this->params->get('show_pagination_footer', 1);
		$show_created_date = $this->params->get('show_created_date', 1);
		$show_modified_date = $this->params->get('show_modified_date', 0);
		$modification_date_label = $this->params->get('modification_date_label', '');
		$show_page_title = $this->params->get('show_page_title', 1);
		$pageclass_sfx = $this->params->get('pageclass_sfx', '');
		$title_leaf = $this->params->get('title_field', '');

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
				$this->params->set('page_title', $full_page_title);
			}
		}
		else
		{
			$this->params->set('page_title', $full_page_title);
		}

		$page_title = $this->params->get('page_title');

		// Get the default image directory from the table.
		$imageDir = $easytable->defaultimagedir;

		// If required get the document and load the js for table sorting
		$doc = JFactory::getDocument();
		$easytables_table_meta = $easytable->table_meta;

		if (!$wereAjaxing)
		{
			// Sortable?
			$SortableTable = $this->params->get('make_tables_sortable');
			$this->SortableTable = $SortableTable;

			if ($SortableTable)
			{
				$doc->addScript(JURI::base() . 'media/com_easytablepro/js/webtoolkit.sortabletable.js');
			}
		}

		// Make sure at least 1 field is set to display
		$etmCount = count($easytables_table_meta);

		// If any of the fields are designated as eMail load the JS file to allow cloaking.
		if (ET_VHelper::hasEmailType($easytables_table_meta))
		{
			$doc->addScript(JURI::base() . 'media/com_easytablepro/js/easytableprotable_fe.js');
		}

		// Get form link
		$formAction = JRoute::_(JURI::getInstance()->toString());

		// Assing these items for use in the tmpl
		$this->show_description        = $show_description;
		$this->show_search             = $show_search;
		$this->show_pagination         = $show_pagination;
		$this->show_pagination_header  = $show_pagination_header;
		$this->show_pagination_footer  = $show_pagination_footer;

		$this->show_created_date       = $show_created_date;
		$this->show_modified_date      = $show_modified_date;
		$this->modification_date_label = $modification_date_label;

		$this->show_page_title         = $show_page_title;
		$this->page_title              = $page_title;
		$this->pageclass_sfx           = $pageclass_sfx;

		$this->tableId                 = $id;
		$this->imageDir                = $imageDir;
		$this->easytable               = $easytable;
		$this->easytables_table_meta   = $easytables_table_meta;
		$this->formAction              = $formAction;
		$this->etmCount                = $etmCount;
		$this->title_leaf              = $title_leaf;
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
	 * @todo Is this used anymore? Removal recommended :D
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


    private function loadJQuery($jAp)
    {
        $loadJQ = $this->params->get('load_jquery', 0);
        $loadJQUI = $this->params->get('load_jqueryui', 0);
        $doc = JFactory::getDocument();
        $minOrNot = JDEBUG ? '.min' : '';
        $versionJQ = $this->params->get('load_jquery_version', '1.9.1');
        $versionJQUI = $this->params->get('load_jqueryui_version', '1.10.3');

        // Load JQuery
        if ($this->jvtag == 'j2')
        {
            switch ($loadJQ)
            {
                case 1: // From local
                {
                    $doc->addScript('media/com_easytablepro/js/jquery/jquery-' . $versionJQ . $minOrNot . '.js');
                    break;
                }
                case 2: // From Google
                {
                    $doc->addScript('http://ajax.googleapis.com/ajax/libs/jquery/' . $versionJQ . '/jquery' . $minOrNot . '.js');
                    break;
                }
                case 3: // From MediaTemple http://code.jquery.com/jquery-1.10.2.js
                {
                    $doc->addScript('http://code.jquery.com/jquery-' . $versionJQ . $minOrNot . '.js');
                    break;
                }
                default:
                    // We don't load JQ at all.
            }
        }
        else
        {   // Joomla 3 or higher
            switch ($loadJQ)
            {
                case 1: case 2: case 3: // From the Joomla only CDN options are for J2.x
            {
                if(!$loadJQUI)
                { // Loading UI will load framework automagically
                    JHtml::_('jquery.framework');
                }
                break;
            }
                default:
                    // We don't load JQ at all.
            }
        }

        // Load JQuery UI plugin
        if ($this->jvtag == 'j2')
        {
            switch ($loadJQUI)
            {
                case 1: // From local
                {
                    $doc->addScript('media/com_easytablepro/js/jquery/jquery-ui-' . $versionJQUI . $minOrNot . '.js');
                    break;
                }
                case 2: // From Google
                {
                    $doc->addScript('http://ajax.googleapis.com/ajax/libs/jqueryui/' . $versionJQUI . '/jquery-ui' . $minOrNot . '.js');
                    break;
                }
                case 3: // From MediaTemple
                {
                    $doc->addScript('http://code.jquery.com/ui/' . $versionJQUI . '/jquery-ui' . $minOrNot . '.js');
                    break;
                }
                default:
                    // We don't load JQUI at all.
            }
        }
        else
        {   // Joomla 3 or higher
            switch ($loadJQUI)
            {
                case 1: case 2: case 3: // From the Joomla only CDN options are for J2.x
            {
                JHtml::_('jquery.ui');
                break;
            }
                default:
                    // We don't load JQ at all.
            }
        }
    }

	/**
	 * Load JQuery (if req) and the DataTables plugins.
	 *
	 * @param   JApplication  $jAp  The Joomla instance.
	 *
	 * @return  null
	 */
	private function loadDataTables($jAp)
	{
		$bAutoColumnWidth = $this->params->get('auto_column_width', 0) ? '' : '"bAutoWidth": false,' . "\n";
		$doc = JFactory::getDocument();
		$minOrNot = JDEBUG ? '.min' : '';

		// Load DataTables
		$loadDT = $this->params->get('load_datatables', 1);

		switch ($loadDT)
		{
			case 1: // From local
			{
				$doc->addScript('media/com_easytablepro/js/jquery/jquery.dataTables-1.9.4' . $minOrNot . '.js');
				break;
			}
			case 2: // From Microsoft
			{
				$doc->addScript('http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables' . $minOrNot . '.js');
				break;
			}
			default:
				// We don't load DT at all.
		}

		// Load Plugins — they have to be local
		// Numbers in HTML
		$doc->addScript('media/com_easytablepro/js/jquery/dataTables.numHtmlSort.js');
		$doc->addScript('media/com_easytablepro/js/jquery/dataTables.numHtmlTypeDetect.js');

		// Currency
		$doc->addScript('media/com_easytablepro/js/jquery/dataTables.currencySort.js');
		$doc->addScript('media/com_easytablepro/js/jquery/dataTables.currencyTypeDetect.js');

		// Formatted Numbers
		$doc->addScript('media/com_easytablepro/js/jquery/dataTables.formattedNumSort.js');
		$doc->addScript('media/com_easytablepro/js/jquery/dataTables.formattedNumTypeDetect.js');

		// Create our Table elements ID
		$tableID = '#' . htmlspecialchars($this->easytable->easytablealias);

		/**
		 * We have a few break points that determine the setup of our datatable... this will cause greif amongst users that don't read the docuemtation.
		 * Client-side processing - DOM sourced data: ~5'000 rows. Speed options: bSortClasses
		 * Client-side processing - Ajax sourced data: ~50'000 rows. Speed options: bDeferRender
		 * Server-side processing: millions of rows.
		 */
		// We need to attach our menu item id
		$item_id = $jAp->input->get('Itemid', 0);
        $filter_search = $jAp->input->getString('filter_search', '');
		$ajaxPath = '"' . JURI::base() . 'index.php?option=com_easytablepro&task=records.fetchRecords&view=records&format=json&id='
			. $this->easytable->id . '&'
			. JSession::getFormToken() . '=1&'
            . 'filter_search=' . $filter_search . '&'
			. 'Itemid=' . $item_id . '",';

		// Turn on state saving so DT handles it for all tables
		$bStateSave = '"bStateSave": true,' . "\n";

		if ($this->easytable->record_count < $this->params->get('table_small_sized', 500, ''))
		{
			// Small table get all the records and do the processing client side
			$bProcessing = '';
			$bServerSide = '';
			$sAjaxSource = '';
		}
		elseif ($this->easytable->record_count < $this->params->get('table_small_sized', 1500, ''))
		{
			// Medium sized table mixture of client-side processing but with Ajax sourced data
			$bProcessing = '"bProcessing": true,' . "\n";
			$bServerSide = '"bServerSide": false,' . "\n";
			$sAjaxSource = '"sAjaxSource": ' . $ajaxPath . "\n";
		}
		else
		{
			// Big table, all processing server side all data ajax sourced.
			$bProcessing = '"bProcessing": true,' . "\n";
			$bServerSide = '"bServerSide": true,' . "\n";
			$sAjaxSource = '"sAjaxSource": ' . $ajaxPath . "\n";
		}

		$dt_init_code  = "window.addEvent('domready', function() { jQuery('$tableID').dataTable( {" . $bProcessing . $bServerSide . $sAjaxSource . $bStateSave . $bAutoColumnWidth;

		$list_limit = $jAp->getUserState('com_easytablepro.dtrecords.' . $item_id . '.' . $this->easytable->id . '.list.limit', 0);

		if (!$list_limit)
		{
			$list_limit = $jAp->getCfg('list_limit');
			// @todo When J2.5 support ends replace the previous line with this: $list_limit = $jAp->get('list_limit');
		}

		$dt_init_code .= '"iDisplayLength": ' . $list_limit . ',' . "\n";

		// @todo Answer this question "Do we give users control over these values?" via Global and Table params?
		$dt_init_code .= '"aLengthMenu": [[5, 10, 15, 20, 25, 30, 50, 100, -1], [5, 10, 15, 20, 25, 30, 50, 100, "' . JText::_('JALL') . '"]], ' . "\n";
		$dt_init_code .= '"sPaginationType": "full_numbers", ' . "\n";

		// Hide our ID column
		$dt_init_code .= '"aoColumnDefs": [{ "bSearchable": false, "bVisible": false, "aTargets": [ 0 ] }]} );} );' . "\n";

		$doc->addScriptDeclaration($dt_init_code);

		// Finally we can load the default CSS or any override found in the templates CSS folder.
		$defaultCSSFile = 'jquery.dataTables.css';

		if ($this->params->get('load_datatables_css', 1))
		{
			$pathToCSS = $this->params->get('datatable_css_override_file');

			if ($pathToCSS == '')
			{
				switch ($loadDT)
				{
					case 1:
					{
						$pathToCSS = '/media/com_easytablepro/css/' . $defaultCSSFile;
						break;
					}
					case 2:
					{
						$pathToCSS = 'http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css';
						break;
					}
				}
			}
		}
		else
		{
			$pathToTemplate = 'templates/' . $jAp->getTemplate();
			$pathToCSS = $pathToTemplate . '/css/' . $defaultCSSFile;
		}

		// Check the local file exists or load the remote
		if ($loadDT != 3 && file_exists(JPATH_BASE . '/' . $pathToCSS))
		{
			$doc->addStyleSheet(JURI::root() . $pathToCSS);
		}
		elseif (substr($pathToCSS, 0, 4))
		{
			$doc->addStyleSheet($pathToCSS);
		}
	}

	/**
	 * Get our merged params
	 *
	 * @param   JSite  $jAp  our current app.
	 *
	 * @return  JRegistry
	 */
	private function getParams($jAp)
	{
		// Component wide & menu based params
		$GMParams = $jAp->getParams('com_easytablepro');
		$params = clone $GMParams;

		$tableParams = new JRegistry;
		$tableParams->loadString($this->easytable->params);

		// Merge them with specific table based params
		$params->merge($tableParams);

		return $params;
	}
}
?>

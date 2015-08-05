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

        $this->show_description        = $this->params->get('show_description', 1);
        $this->show_search             = $this->params->get('show_search', 1);
        $this->show_pagination         = $this->params->get('show_pagination', 1);
        $this->show_pagination_header  = $this->params->get('show_pagination_header', 0);
        $this->show_pagination_footer  = $this->params->get('show_pagination_footer', 1);
        $this->paginationEnabled       = $this->show_pagination_header || $this->show_pagination_footer;
        $this->show_created_date       = $this->params->get('show_created_date', 1);
        $this->show_modified_date      = $this->params->get('show_modified_date', 0);
        $this->modification_date_label = $this->params->get('modification_date_label', '');
        $this->show_page_title         = $this->params->get('show_page_title', 1);
        $this->pageclass_sfx           = $this->params->get('pageclass_sfx', '');
        $this->SortableTable           = $this->params->get('make_tables_sortable');

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
        $ajaxLayout = ($layout == 'dt') || ($layout == 'foo') || ($layout == 'ajax');
        $wereAjaxing = ($ajaxEnabled && $ajaxLayout);

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

            // Setup our default pagination list limit array (as AJAX solutions won't be using Joomla's inaccessible list)
            $limitOptionsListValues = array(5, 10, 15, 20, 25, 30, 50, 100, -1);
            $limitOptionsListLabels = array(5, 10, 15, 20, 25, 30, 50, 100, JText::_('JALL'));
            $this->limitOptionsList = array($limitOptionsListValues, $limitOptionsListLabels);

			// Load jQuery and relevant AJAX library/plugin
            $this->loadJQuery();
            $this->loadAJAXTableJS($layout, $jAp);
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

        // Assigning these values for use in the tmpl and elsewhere
        $this->tableId                 = $easytable->id;
        $this->easytablename           = $easytable->easytablename;
        $this->imageDir                = $easytable->defaultimagedir;
        $this->easytables_table_meta   = $easytable->table_meta;
        $this->etmCount                = count($easytable->table_meta);

		$title_leaf = $this->params->get('title_field', '');

		if ($i = strpos($title_leaf, ':'))
		{
			$title_leaf = substr($title_leaf, $i + 1);
		}
        $this->title_leaf = $title_leaf;

        $full_page_title = $this->easytablename;

		// Better breadcrumbs
		$pathway   = $jAp->getPathway();
		$pathway->addItem($this->easytablename, 'index.php?option=easytablepro&amp;view=table&amp;id=' . $this->tableId);

		// Because the application sets a default page title, we need to get it right from the menu item itself
		// Get the menu item object
		$menus = $jAp->getMenu();
		$menu  = $menus->getActive();

		if (is_object($menu) && isset($menu->query['view']) && $menu->query['view'] == 'table' && isset($menu->query['id']) && $menu->query['id'] == $this->tableId)
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

        $this->page_title = $this->params->get('page_title');

		// If required get the document and load the js for table sorting
		$doc = JFactory::getDocument();

		if (!$wereAjaxing && $this->SortableTable)
        {
            $doc->addScript(JURI::base() . 'media/com_easytablepro/js/webtoolkit.sortabletable.js');
		}

		// If any of the fields are designated as eMail load the JS file to allow cloaking.
		if (ET_VHelper::hasEmailType($this->easytables_table_meta))
		{
			$doc->addScript(JURI::base() . 'media/com_easytablepro/js/easytableprotable_fe.js');
		}

		// Get form link
		$formAction = JRoute::_(JURI::getInstance()->toString());

		$this->formAction              = $formAction;
		parent::display($tpl);
	}

	/**
	 * getLeafField() is called from the view's tmpl file in the processing of each records detail link
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

    private function loadAJAXTableJS($layout, $jAp)
    {
        // Load our jQuery required files
        $this->loadJQuery($jAp);

        // Load the right table library/plugin
        switch ($layout)
        {
            case 'dt':
            {
                $this->loadDataTables($jAp);
            }
            case 'foo':
            {
                $this->loadFooTable($jAp);
            }
        }
    }

    /**
     * Handles the loading of jQuery and jQuery UI
     *
     * @param   JApplicationCMS  $jAp  The Joomla CMS application.
     *
     * @return  null
     */
    private function loadJQuery()
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
     * Loads FooTable Plugin and configures the settings.
     *
     * @param $jAp
     */
    private function loadFooTable($jAp)
    {
        // Get the settings
        $doc = JFactory::getDocument();
        $minOrNot = JDEBUG ? '.min' : '';
        $loadFooTableJS = $this->params->get('load_footables', '1');
        $loadAllInOneFooTableJS = $this->params->get('load_footables_all_in_one', '1');
        $loadAllInOneFooTableJS = JDEBUG ? 0 : $loadAllInOneFooTableJS;
        $fooTheme = $this->params->get('load_footables_theme', '');
        $fooCSSOverride = $this->params->get('footable_css_override_file', '');
        $fooPaginationControls = $this->params->get('footables_pagination_pages', 0);

        // Load FooTable & plugins
        if ($loadFooTableJS)
        {
//            ### DISABLED AS ALL IN ONE MIN FILE BREAKS
            if ($loadAllInOneFooTableJS)
            {
                $doc->addScript('media/com_easytablepro/js/jquery/footable.all.min.js');
            }
            else
            {
                $doc->addScript('media/com_easytablepro/js/jquery/footable' . $minOrNot . '.js');
                $doc->addScript('media/com_easytablepro/js/jquery/footable.bookmarkable' . $minOrNot . '.js');
                $doc->addScript('media/com_easytablepro/js/jquery/footable.filter' . $minOrNot . '.js');
                $doc->addScript('media/com_easytablepro/js/jquery/footable.grid' . $minOrNot . '.js');
                $doc->addScript('media/com_easytablepro/js/jquery/footable.memory' . $minOrNot . '.js');
                $doc->addScript('media/com_easytablepro/js/jquery/footable.paginate' . $minOrNot . '.js');
                $doc->addScript('media/com_easytablepro/js/jquery/footable.sort' . $minOrNot . '.js');
                $doc->addScript('media/com_easytablepro/js/jquery/footable.striping' . $minOrNot . '.js');
            }
        }

        // Load CSS
        $coreCSS = 'media/com_easytablepro/css/footable.core' . $minOrNot . '.css';
        $doc->addStyleSheet($coreCSS);

        // And any theme specified
        switch ($fooTheme)
        {
            case 'standalone':
                $doc->addStyleSheet('media/com_easytablepro/css/footable.standalone' . $minOrNot . '.css');
            case 'metro':
                $doc->addStyleSheet('media/com_easytablepro/css/footable.metro' . $minOrNot . '.css');
            default:
                // Do nothing.
        }

        // And any custom CSS
        if ($fooCSSOverride != '')
        {
            $doc->addStyleSheet($$fooCSSOverride);
        }

        // Setup the table options for Toggle Style and Size
        $toggleStyle = $this->params->get('toggleStyle', '');
        $toggleSize = $this->params->get('toggleSize', '');
        $this->fooTableClassOptions = $toggleStyle . ' ' . $toggleSize;

        // Pagination Control Options
        if ($fooPaginationControls && $this->paginationEnabled)
        {
            $this->paginationDataAttributes = 'data-limit-navigation="' . $fooPaginationControls . '"';
            $this->paginationDataAttributes .= ' data-page-size="' . $jAp->getCfg('list_limit') . '"';
            $fooLimitSelectorWatcher = <<<fooselect
jQuery('#limit').change(function (e) {
    e.preventDefault();
    var pageSize = jQuery(this).val();
    jQuery('.footable').data('page-size', pageSize);
    jQuery('.footable').trigger('footable_initialized');
});
fooselect;
;
        }
        else
        {
            $this->paginationDataAttributes = '';
            $fooLimitSelectorWatcher = '';
        }

        // Search filter?
        if ($this->show_search)
        {
            $this->dataFilter = 'data-filter="#filter_search"';
        }
        // Finally add the FooTable options for this view
        $fooOptions = json_decode($this->params->get('field_hidden_options_values', false));
        if ($fooOptions)
        {
            $fooSettings = array();
            foreach ($fooOptions as $option)
            {
                $fooSettings[$option->id] = $option;
            }
            $this->fooSettings = $fooSettings;
        }

        // Create and insert init code
        $fooInitDomReady = <<<fooDR
window.addEvent('domready', function() {
fooDR;
        $fooInitCloseDR = '});';

        $fooInit = <<<fooInit
jQuery('.footable').footable();
fooInit;
        $fooInit .= $fooLimitSelectorWatcher;

        $doc->addScriptDeclaration($fooInitDomReady . $fooInit . $fooInitCloseDR);
    }

	/**
	 * Load DataTables and it's plugins and configure the settings.
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
		$dt_init_code .= '"aLengthMenu": [[' . implode(', ', $this->limitOptionsList[0]) . '], [' . implode(', ', $this->limitOptionsList[1]) . ']], ' . "\n";
		$dt_init_code .= '"sPaginationType": "full_numbers", ' . "\n";
        $dtLanguageStrings = $this->languageBlock() . ",\n";


        $dt_init_code .= $dtLanguageStrings;

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

    /**
     * Generates the language block options for DT.
     *
     * @return string
     */
    private function languageBlock()
    {
// Language block
        $COM_EASYTABLEPRO_SITE_EMPTYTABLE          = JText::_('COM_EASYTABLEPRO_SITE_EMPTYTABLE');
        $COM_EASYTABLEPRO_SITE_INFO                = JText::_('COM_EASYTABLEPRO_SITE_INFO');
        $COM_EASYTABLEPRO_SITE_INFOEMPTY           = JText::_('COM_EASYTABLEPRO_SITE_INFOEMPTY');
        $COM_EASYTABLEPRO_SITE_INFOFILTERED        = JText::_('COM_EASYTABLEPRO_SITE_INFOFILTERED');
        $COM_EASYTABLEPRO_SITE_INFOPOSTFIX         = JText::_('COM_EASYTABLEPRO_SITE_INFOPOSTFIX');
        $COM_EASYTABLEPRO_SITE_THOUSANDS           = JText::_('COM_EASYTABLEPRO_SITE_THOUSANDS');
        $COM_EASYTABLEPRO_SITE_LENGTHMENU          = JText::_('COM_EASYTABLEPRO_SITE_LENGTHMENU');
        $COM_EASYTABLEPRO_SITE_LOADINGRECORDS      = JText::_('COM_EASYTABLEPRO_SITE_LOADINGRECORDS');
        $COM_EASYTABLEPRO_SITE_PROCESSING          = JText::_('COM_EASYTABLEPRO_SITE_PROCESSING');
        $COM_EASYTABLEPRO_SITE_SEARCH              = JText::_('COM_EASYTABLEPRO_SITE_SEARCH');
        $COM_EASYTABLEPRO_SITE_ZERORECORDS         = JText::_('COM_EASYTABLEPRO_SITE_ZERORECORDS');
        $COM_EASYTABLEPRO_SITE_PAGINATE_FIRST      = JText::_('COM_EASYTABLEPRO_SITE_PAGINATE_FIRST');
        $COM_EASYTABLEPRO_SITE_PAGINATE_LAST       = JText::_('COM_EASYTABLEPRO_SITE_PAGINATE_LAST');
        $COM_EASYTABLEPRO_SITE_PAGINATE_NEXT       = JText::_('COM_EASYTABLEPRO_SITE_PAGINATE_NEXT');
        $COM_EASYTABLEPRO_SITE_PAGINATE_PREVIOUS   = JText::_('COM_EASYTABLEPRO_SITE_PAGINATE_PREVIOUS');
        $COM_EASYTABLEPRO_SITE_ARIA_SORTASCENDING  = JText::_('COM_EASYTABLEPRO_SITE_ARIA_SORTASCENDING');
        $COM_EASYTABLEPRO_SITE_ARIA_SORTDESCENDING = JText::_('COM_EASYTABLEPRO_SITE_ARIA_SORTDESCENDING');


        $dtLanguageStrings = <<<dtls
"oLanguage":
{
    "sEmptyTable":     "$COM_EASYTABLEPRO_SITE_EMPTYTABLE",
    "sInfo":           "$COM_EASYTABLEPRO_SITE_INFO",
    "sInfoEmpty":      "$COM_EASYTABLEPRO_SITE_INFOEMPTY",
    "sInfoFiltered":   "$COM_EASYTABLEPRO_SITE_INFOFILTERED",
    "sInfoPostFix":    "$COM_EASYTABLEPRO_SITE_INFOPOSTFIX",
    "sThousands":      "$COM_EASYTABLEPRO_SITE_THOUSANDS",
    "sLengthMenu":     "$COM_EASYTABLEPRO_SITE_LENGTHMENU",
    "sLoadingRecords": "$COM_EASYTABLEPRO_SITE_LOADINGRECORDS",
    "sProcessing":     "$COM_EASYTABLEPRO_SITE_PROCESSING",
    "sSearch":         "$COM_EASYTABLEPRO_SITE_SEARCH",
    "sZeroRecords":    "$COM_EASYTABLEPRO_SITE_ZERORECORDS",
    "oPaginate": {
        "sFirst":      "$COM_EASYTABLEPRO_SITE_PAGINATE_FIRST",
        "sLast":       "$COM_EASYTABLEPRO_SITE_PAGINATE_LAST",
        "sNext":       "$COM_EASYTABLEPRO_SITE_PAGINATE_NEXT",
        "sPrevious":   "$COM_EASYTABLEPRO_SITE_PAGINATE_PREVIOUS"
    },
    "oAria": {
        "sSortAscending":  "$COM_EASYTABLEPRO_SITE_ARIA_SORTASCENDING",
        "sSortDescending": "$COM_EASYTABLEPRO_SITE_ARIA_SORTDESCENDING"
    }
}
dtls;
        return $dtLanguageStrings;
    }
}
?>

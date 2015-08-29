<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('_JEXEC') or die('Restricted Access');

require_once JPATH_COMPONENT_SITE . '/helpers/viewfunctions.php';

/**
 * EasyTableProViewRecord
 *
 * @package     EasyTable_Pro
 *
 * @subpackage  Views
 *
 * @since       1.0
 */
class EasyTableProViewRecord extends JViewLegacy
{
    protected $item;

    protected $state;

    protected $show_next_prev_record_links;

    protected $prevrecord;

    protected $nextrecord;

    protected $tableId;

    protected $recordId;

    protected $trid;

    protected $imageDir;

    protected $easytable;

    protected $et_meta;

    protected $et_record;

    protected $show_linked_table;

    protected $pageclass_sfx;

    protected $linked_table;

    protected $linked_records;

    protected $pt;

    /**
     * display()
     *
     * @param   null  $tpl  Our main view controller
     *
     * @return bool
     */
    public function display($tpl = null)
    {
        // Get Joomla
        $jAp = JFactory::getApplication();
        $user		= JFactory::getUser();

        // Get the Data
        $this->item = $this->get('Item');
        $easytable = $this->item->easytable;
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

        $tableKey = $easytable->key_name;

        // Check we have a real table
        if ($id == 0) {
            $jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_MGR_TABLE_ID_ZERO_ERROR', $id), 'NOTICE');
            return false;
        }

        // Get the state info
        $this->state = $this->get('State');

        // Is there a title suffix from the record
        $title_field_raw = $easytable->params->get('title_field', 0);

        if (!empty($title_field_raw)) {
            $title_field_data = explode(':', $title_field_raw);
            $title_field_label = $title_field_data[1];
            $titleSuffix = $this->item->record->$title_field_label;
        } else {
            $titleSuffix = '';
        }

        // Generate Page title
        if ($titleSuffix) {
            $page_title = JText::sprintf(
                'COM_EASYTABLEPRO_SITE_RECORD_PAGE_TITLE',
                $easytable->easytablename,
                $titleSuffix
            );
        } else {
            $page_title = JText::sprintf('COM_EASYTABLEPRO_SITE_RECORD_PAGE_TITLE_NO_LEAF', $easytable->easytablename);
        }

        $pt = htmlspecialchars($page_title);

        if ($easytable->params->get('title_links_to_table')) {
            // Create a backlink
            $backlink = 'index.php?option=com_easytablepro&amp;view=records&amp;id=' . $easytable->id;
            $backlink = JRoute::_($backlink);
            $pt = '<a href="' . $backlink . '">' . $pt . '</a>';
        }

        // Generate Prev and Next Records
        $this->show_next_prev_record_links = $easytable->params->get('show_next_prev_record_links');

        if ($this->show_next_prev_record_links) {
            $this->prevrecord = '';

            if (isset($this->item->prevRecordId) && isset($this->item->prevRecordId[0])) {
                $recURL = 'index.php?option=com_easytablepro&view=record&id=' .
                    $easytable->id .
                    '&rid=' .
                    $this->item->prevRecordId[0];

                if (isset($this->item->prevRecordId[1]) && ($this->item->prevRecordId[1] != '')) {
                    $recURL .= '&rllabel=' . $this->item->prevRecordId[1];
                }

                $this->prevrecord = JRoute::_($recURL);
            }

            $this->nextrecord = '';

            if (isset($this->item->nextRecordId) && isset($this->item->nextRecordId[0])) {
                $recURL = 'index.php?option=com_easytablepro&view=record&id=' .
                    $easytable->id .
                    '&rid=' . $this->item->nextRecordId[0];

                if (isset($this->item->nextRecordId[1]) && ($this->item->nextRecordId[1] != '')) {
                    $recURL .= '&rllabel=' . $this->item->nextRecordId[1];
                }

                $this->nextrecord = JRoute::_($recURL);
            }
        } else {
            $this->prevrecord = '';
            $this->nextrecord = '';
        }

        // Assigning these items for use in the tmpl
        $this->tableId = $id;
        $this->recordId = $this->item->record->$tableKey;
        $this->trid = $id . '.' . $this->item->record->$tableKey;
        $this->imageDir = $easytable->defaultimagedir;
        $this->easytable = $easytable;
        $this->et_meta = $easytable->table_meta;
        $this->et_record = JArrayHelper::fromObject($this->item->record);
        $this->show_linked_table = $easytable->params->get('show_linked_table', '');
        $this->pageclass_sfx = $easytable->params->get('pageclass_sfx', '');
        $this->linked_table = $this->item->linked_table;
        $this->linked_records = $this->item->linked_records;
        $this->pt = $pt;

        // Load the doc bits
        $this->addCSSEtc();

        parent::display($tpl);
    }

    /**
     * addCSSEtc() loads any CSS, JS for the view and causes the
     *
     * @return  void
     *
     * @since   1.1
     */
    private function addCSSEtc()
    {
        // Get the document
        $doc = JFactory::getDocument();

        // Get the document object
        $document = JFactory::getDocument();

        // Load the defaults first so that our script loads after them
        JHtml::_('behavior.framework', true);
        JHtml::_('behavior.tooltip');
        JHtml::_('behavior.multiselect');

        // Then add JS to the document‚ - make sure all JS comes after CSS
        // Tools first
        $jsFile = ('media/com_easytablepro/js/atools.js');
        $document->addScript(JURI::root() . $jsFile);
        ET_General_Helper::loadJSLanguageKeys('/' . $jsFile);

        // Component view specific next...
        $jsFile = ('media/com_easytablepro/js/easytableprotable_fe.js');
        $document->addScript(JURI::root() . $jsFile);
        ET_General_Helper::loadJSLanguageKeys('/' . $jsFile);
    }
}

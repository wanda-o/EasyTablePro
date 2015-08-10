<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('_JEXEC') or die;

jimport('joomla.application.categories');

/**
 * Build the route for the EasyStaging Pro component
 *
 * @param   array  &$query  An array of URL arguments
 *
 * @return  array  The URL arguments to use to assemble the subsequent URL.
 */
function easyTableProBuildRoute(&$query)
{
    $segments = array();

    if (isset($query['view'])) {
        // Store the view for later
        $targetView = $query['view'];
        $targetTableID = isset($query['id']) ? $query['id'] : 0;

        if (strpos($targetTableID, ':') != false) {
            list($targetTableID, $targetTableAlias) = explode(':', $targetTableID);
        } else {
            $targetTableAlias = '';
        }

        // We need a few things, so, get a menu item based on Itemid or currently active
        $app  = JFactory::getApplication();
        $db   = JFactory::getDbo();
        $menu = $app->getMenu();

        // We need a menu item.  Either the one specified in the query, or the current active one if none specified
        if (empty($query['Itemid'])) {
            $menuItem = $menu->getActive();
        } else {
            $menuItem = $menu->getItem($query['Itemid']);
        }

        // Get the current menuItem's settings
        if (isset($menuItem->query['view'])) {
            $menusCurrentView = $menuItem->query['view'];
        } else {
            $menusCurrentView = '';
        }

        // Get the menuItem's current component
        if (isset($menuItem->query['option'])) {
            $menusCurrentOption = $menuItem->query['option'];
        } else {
            $menusCurrentOption = '';
        }

        // If we have an option it's likely we have a data ID
        if ($menusCurrentOption && isset($menuItem->query['id'])) {
            $menusCurrentID = $menuItem->query['id'];
        } else {
            $menusCurrentID = 0;
        }

        unset($query['view']);

        switch ($targetView) {
            case 'records':
                // We need the id of the target table, otherwise it's all moot.
                if (isset($query['id'])) {
                    // If we're in a `records` view is the target within the same table?
                    if ($menusCurrentView == 'records' && $targetTableID == $menusCurrentID) {
                        unset($query['id']);
                    } else {
                        // OK we may need to convert a numeric id to an alias id
                        if (empty($targetTableAlias) && is_numeric($targetTableID)) {
                            $targetTableAlias = etp_getTableAliasByID($targetTableID, $db);
                        }

                        // Make sure we have a result.
                        if ($targetTableAlias != '') {
                            unset($query['id']);
                            $segments[] = $targetTableAlias;
                        }
                    }
                }
                break;

            // Link to Record Detail view requested
            case 'record':
                $newItemid = false;
                // See if you current menus table matches our target table
                if (isset($query['Itemid']) && ($targetTableID != $menusCurrentID)) {
                    // It doesn't lets try and update the Itemid to an existing published menu for our target table ID.
                    if ($newItemid = etp_getMenuForTableID($targetTableID)) {
                        $query['Itemid'] = $newItemid;
                    }

                }

                if (isset($query['id'])) {
                    // So, is our current view already showing the table of the target URL?
                    if ((($menusCurrentOption == 'com_easytablepro') &&
                            ($targetTableID != $menusCurrentID)) || $menusCurrentOption == '') {
                        // We'll need to add an alias to point to the right table...
                        if (empty($targetTableAlias)) {
                            $targetTableAlias = etp_getTableAliasByID($targetTableID, $db);
                        }

                        // Set the next segment to the alias
                        if (!$newItemid) {
                            $segments[] = $targetTableAlias;
                        }
                    }

                    unset($query['id']);
                } else {
                    return array();
                }

                if (isset($query['rid'])) {
                    $segments[] = $query['rid'];
                    unset($query['rid']);
                } else {
                    return array();
                }

                if (isset($query['rllabel'])) {
                    $segments[] = $query['rllabel'];
                    unset($query['rllabel']);
                }

                break;
            default:
                break;
        }
    }

    // Return segments here, of course it could be empty if no view etc... e.g. a core menu.
    return $segments;
}

/**
 * Parse the segments of a URL.
 *
 * @param   array  $segments  The segments of the URL to parse.
 *
 * @return  array   The URL attributes to be used by the application.
 *
 * @since   1.0
 */
function easyTableProParseRoute($segments)
{
    $vars = array();

    // Count route segments
    $count = count($segments);

    // Time to bail
    if ($count == 0) {
        $vars['view'] = 'tables';

        return $vars;
    }

    // OK, we have work to do, lets get the active menu item.
    $app      = JFactory::getApplication();
    $menu     = $app->getMenu();
    $menuItem = $menu->getActive();

    // It's possible there's no active menu item... e.g. a search result link or
    // a direct component path e.g. component/easytablepro/table_name/99/leaf
    if (!$menuItem) {
        $defaultMenu = true;
        $menuItem = $menu->getDefault();
    } else {
        $defaultMenu = false;
    }

    // And we'll need the component params
    $db = JFactory::getDbo();

    if ($count == 1 && ($menuItem->query['view'] != 'records')) {
        $segments[0] = preg_replace('/:/', '-', $segments[0], 1);
        $vars['view'] = 'records';

        // Convert the easy table alias to its actual id
        $id = etp_getTableIDFromAlias($segments[0], $db);
        $vars['id'] = $id;

        return $vars;
    } elseif ($count == 1 && ($menuItem->query['view'] == 'records')) {
        $count = 2;
    }

    $vars['view'] = 'record';

    if ($count == 2) {
        $rid = 0;

        if (!$defaultMenu && isset($menuItem->query['view'])) {
            if ($menuItem->query['view'] == 'tables') {
                // Remove the stupid colon
                $segments[0] = preg_replace('/:/', '-', $segments[0], 1);
                $id = etp_getTableIDFromAlias($segments[0], $db);
                $vars['id'] = $id;

                $rid = $segments[1];
            } elseif ($menuItem->query['view'] == 'records') {
                // Convert the easy table alias to it actual id
                if (isset($menuItem->query['id'])) {
                    $vars['id'] = $menuItem->query['id'];
                } else {
                    $vars['id'] = 0;
                    $app->enqueueMessage(
                        JText::_('COM_EASYTABLEPRO_SITE_ROUTER_PARSEROUTE_COULDNT_FIND_TABLE_ID'),
                        'Warning'
                    );
                }

                $rid = $segments[0];
            } elseif ($menuItem->query['view'] == 'record') {
                $vars['id'] = $segments[0];
                $rid = $segments[1];
            }
        } else {
            // Handle the 2 part url without a menu — most likely joomla created link
            // of the component/easytablepro/tablename/99 form.
            $tableID = etp_getTableIDFromAlias(preg_replace('/:/', '-', $segments[0], 1), $db);
            $tableID = empty($tableID) ? 0 : $tableID;

            if (is_numeric($tableID) && $tableID > 0) {
                // Lets see if we have a valid record ID
                $rid = is_numeric($segments[1]) ? (int)$segments[1] : 0;
            } else {
                $tableID = 0;
                $rid = 0;
            }

            // Set our table ID
            $vars['id'] = $tableID;
        }

        $vars['rid']  = $rid;
    }

    // Three => id, rid, leaf
    if ($count == 3) {
        list($seg_id, $seg_alias) = explode(':', $segments[0], 2);

        if (is_numeric($seg_id)) {
            $segments[0] = $seg_alias;
        } else {
            $segments[0] = preg_replace('/:/', '-', $segments[0], 1);
        }

        // Convert the easy table alias to it actual id
        if (!empty($segments[0])) {
            $vars['id'] = etp_getTableIDFromAlias($segments[0], $db);
        } else {
            $vars['id'] = 0;
            $app->enqueueMessage(JText::_('COM_EASYTABLEPRO_SITE_ROUTER_PARSEROUTE_COULDNT_FIND_TABLE_ID'), 'Warning');
        }

        // Remove the stupid colon that J! core inserts...
        $segments[2] = preg_replace('/:/', '-', $segments[2], 1);
        $rid = $segments[1];
        $leaf = $segments[2];

        $vars['rid']  = $rid;
        $vars['leaf'] = $leaf;
    }

    return $vars;
}


/**
 * For those situations where we get a table ID but no alias
 *
 * Namespaced by etp_ because the whole router system is stupid
 *
 * @param  int               $targetTableID  The id of the table
 * @param  JDatabaseDriver   $db             The Joomla Database obj.
 *
 * @return mixed
 */
function etp_getTableAliasByID($targetTableID, $db)
{
    // We'll need to add an alias to point to the right table...
    $SQLquery = $db->getQuery(true);

    // Search for the alias of the table of this id
    $SQLquery->select($db->quoteName('easytablealias'));
    $SQLquery->from($db->quoteName('#__easytables'));
    $SQLquery->where($db->quoteName('id') . ' = ' . (int)$targetTableID);
    $db->setQuery($SQLquery);
    $idAlias = $db->loadResult();

    return $idAlias;
}

/**
 * Getting a table ID from the Alias
 *
 * Namespaced by etp_ because the whole router system is stupid
 *
 * @param  string            $alias  The alias of the table
 * @param  JDatabaseDriver   $db     The Joomla Database obj.
 *
 * @return mixed
 */
function etp_getTableIDFromAlias($alias, $db)
{
    $query = $db->getQuery(true);
    $query->select($db->quoteName('id'));
    $query->from($db->quoteName('#__easytables'));
    $query->where($db->quoteName('easytablealias') . '=' . $db->quote($alias));
    $db->setQuery($query);
    $id = $db->loadResult();

    return $id;
}

/**
 * Get's the first match from the menu system that points to a records view (i.e. a table)
 * whose ID matches our target table.
 *
 * @param  int               $id  The table ID
 * @param  JDatabaseDriver   $db  The Joomla DB Obj.
 */
function etp_getMenuForTableID($id)
{
    $menu = JMenu::getInstance('site');

    $matches = $menu->getItems(
        array('menutype'),
        array('easytable')
    );

    $theMenuItemid = false;

    foreach ($matches as $match) {
        if (isset($match->query['view']) && $match->query['view'] == 'records' &&
            isset($match->query['id']) && $match->query['id'] == $id ) {
            $theMenuItemid = $match->id;
            break;
        }
    }

    return $theMenuItemid;
}

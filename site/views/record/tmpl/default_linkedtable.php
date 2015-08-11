<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('_JEXEC') or die ('Restricted Access');
$tableID = htmlspecialchars($this->easytable->easytablealias);
$tableDesc = $this->easytable->description;
$tableSummary = htmlspecialchars(strip_tags($tableDesc));
?>
<div class="contentpaneopen<?php echo $this->pageclass_sfx ?>" >
    <table id="<?php echo $tableID; ?>"
           summary="<?php echo $tableSummary; ?>" width="100%">
        <thead>
            <tr>
                <?php
                foreach ($this->linked_table->table_meta as $metaRec) {
                    if ($metaRec['list_view']) {
                        echo '<th class="sectiontableheader ' .
                            $metaRec['fieldalias'] . '">' .
                            $metaRec['label'] .
                            '</th>';
                    }
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            $currentImageDir = $this->linked_table->defaultimagedir;
            $leaf = $this->linked_table->params->get('title_field');

            if ($i = strpos($leaf, ':')) {
                $leaf = substr($leaf, $i + 1);
            }

            $rowNum = 0;

            // Looping through the rows of data
            foreach ($this->linked_records as $prow) {
            // Open the row
                echo '<tr class="row' . $rowNum . '" >';

                // Looping through each field of the row
                foreach ($prow as $fieldalias => $fieldValue) {
                    reset($this->linked_table->table_meta);

                    foreach ($this->linked_table->table_meta as $metaRec) {
                        if ($metaRec['list_view'] && $metaRec['fieldalias'] != 'id' &&
                                $metaRec['fieldalias'] == $fieldalias) {
                            $cellAlias      = $metaRec['fieldalias'];
                            $cellType       = (int) $metaRec['type'];
                            $cellOptions    = $metaRec['params'];
                            $cellDetailLink = (int) $metaRec['detail_link'];
                            $cellData       = ET_VHelper::getFWO(
                                $fieldValue,
                                $cellType,
                                $cellOptions,
                                $prow,
                                $currentImageDir
                            );

                            // As a precaution we make sure the detail link cell is not a URL field
                            if ($cellDetailLink && ($cellType != 2)) {
                                $linkToDetail = 'index.php?option=com_easytablepro&view=record&id=' .
                                    $this->linked_table->id . ':' . $this->linked_table->easytablealias . '&rid=' .
                                    $prow['id'];

                                // If there is a define label field a the URL leaf.
                                $linkToDetail .= $leaf ? '&rllabel=' . JFilterOutput::stringURLSafe(substr($prow[$leaf], 0, 100)) : '';
                                $linkToDetail = JRoute::_($linkToDetail);
                                $cellData = '<a href="' . $linkToDetail . '">' . $cellData . '</a>';
                                $cellDetailLink = '';
                            }

                            // Finally we can echo the cell string.
                            echo "<td class='colfld " . $cellAlias . "'>" . trim($cellData) . '</td>';
                            break;
                        }
                    }
                        // End of row stuff should follow after this.
                }
                // Close the Row
                echo '</tr>';
                $k = '';

                // Clear the rowId to prevent any issues.
                $rowNum = (int) !$rowNum;
            }
        ?>
        </tbody>
    </table>
</div>

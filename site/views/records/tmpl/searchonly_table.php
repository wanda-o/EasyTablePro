<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

defined('_JEXEC') or die ('Restricted Access');

if ($this->show_pagination_header && !$this->show_pagination_footer)
{
    // Only if pagination is enabled
    if ($this->show_pagination && $this->etmCount)
    {
        echo '<div class="pagination">';
        echo $this->pagination->getListFooter();
        echo '</div>';
    }
}
?>
<table id="<?php echo htmlspecialchars($this->easytable->easytablealias); ?>" summary="<?php echo htmlspecialchars(strip_tags($this->easytable->description)); ?>" width="100%">
    <thead>
    <tr>
        <?php
        $headingCount = 0;
        foreach ($this->easytables_table_meta as $heading )
        {
            if (!$heading['list_view'])
            {
                continue;
            }

            $titleString = '';

            if (strlen($heading['description']))
            {
                $titleString = 'class="hasTip" title="' . htmlspecialchars($heading['description']) . '" ';
            }

            $headingClass = 'sectiontableheader ' . $heading['fieldalias'];
            echo '<th class="' . $headingClass . '" ><span ' . $titleString . ' >' . $heading['label'] . '</span></th>';
            $headingCount++;
        }
        ?>
    </tr>
    </thead>
    <?php
    if ($this->itemCount)
    {
        echo $this->loadTemplate('tablebody');
    }
    else
    {
        echo "<tbody><tr id='et_no_records'><td colspan='$headingCount'>$this->noResultsMsg</td></tr></tbody>";
    }
    ?>
</table>
<?php
if ($this->SortableTable)
{ ?>
    <script type="text/javascript">
        var t = new SortableTable(document.getElementById('<?php echo htmlspecialchars($this->easytable->easytablealias); ?>'), 'etAscending', 'etDescending');
    </script>
<?php
}
if ($this->show_pagination && $this->show_pagination_footer && $this->etmCount && $this->itemCount) // If pagination is enabled show the controls
{
    echo '<div class="pagination">';
    echo $this->pagination->getListFooter();
    echo '</div>';
}
?>

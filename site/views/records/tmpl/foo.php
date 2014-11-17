<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

	defined('_JEXEC') or die ('Restricted Access');
	$leaf = $this->title_leaf;
?>
<div class="contentpaneopen<?php echo $this->pageclass_sfx; ?>" id="et_table_page">
<?php
	if ($this->show_page_title)
	{
		echo '<h2 class="contentheading">' . htmlspecialchars($this->page_title) . '</h2>';
	}

	echo ($this->show_created_date ? '<p class="createdate">' . JHTML::_('date', $this->easytable->created_, JText::_('DATE_FORMAT_LC2')) . '</p>' : '');

	if ($this->modification_date_label === '')
	{
		$mod_dl = JText::sprintf('COM_EASYTABLEPRO_SITE_LAST_UPDATED', JHTML::_('date', $this->easytable->modified_, JText::_('DATE_FORMAT_LC2')));
	}
	else
	{
		$mod_dl = $this->modification_date_label . ' ' . JHTML::_('date', $this->easytable->modified_, JText::_('DATE_FORMAT_LC2'));
	}

	echo ($this->show_modified_date ? '<p class="modifydate">' . $mod_dl . '</p>' : '');
	echo ($this->show_description ? '<div class="et_description">' . $this->easytable->description . '</div>' : '');

?>
    <div class="et_search_result">
        <?php
        // If search is enabled for this table, show the search box.
        $dataFilter = '';

        if ($this->show_search && $this->etmCount)
        {
            ?>
            <label for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label> <input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" class="text_area" />
            <button onclick="jQuery('table').trigger('footable_clear_filter');"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
        <?php

        // Assemble table's data-* attributes
            $dataAttributes = $this->paginationDataAttributes . ' ' . $this->dataFilter;
        }
        ?>
    </div>

    <div id="easytable-<?php echo htmlspecialchars($this->easytable->easytablealias); ?>">
        <table id="<?php echo htmlspecialchars($this->easytable->easytablealias); ?>" class="footable table <?php echo $this->fooTableClassOptions; ?>" <?php echo $dataAttributes; ?> >
			<thead>
            <?php echo ET_General_Helper::fooTopPagination($this->show_pagination, $this->show_pagination_header, $this->fooSettings, $this->easytables_table_meta, $this->limitOptionsList); ?>
			<tr>
				<?php
				$headingCount = 1;
                $dataToggleSet = false;

                foreach ($this->easytables_table_meta as $heading )
                {
                    // Get FooTable Options
                    $fooOptions = $this->fooSettings[$heading['id']];

					if (!$fooOptions->list)
					{
						continue;
					}

					$titleString = '';

					if (strlen($heading['description']))
					{
						$titleString = '<span class="hasTip" title="' . htmlspecialchars($heading['description']) . '" >';
                        $titleStringClose = '</span>';
					}
                    else
                    {
                        $titleString = '';
                        $titleStringClose = '';
                    }

					$headingClass = 'sectiontableheader ' . $heading['fieldalias'];

                    // Set data-hide
                    if ($fooOptions->hidden != '')
                    {
                        $datahide = 'data-hide="' . implode(',', explode(' ',trim($fooOptions->hidden))) . '"';
                    }
                    else
                    {
                        $datahide = '';
                    }

                    // Set our data toggle on the first column that is visible on all
                    if (!$dataToggleSet && $datahide == '')
                    {
                        $dataToggle = 'data-toggle="true"';
                        $dataToggleSet = true;
                    }
                    else
                    {
                        $dataToggle = '';
                    }

                    // Compile our data-* attributes
                    $dataAttributes = trim($datahide . ' ' . $dataToggle);

                    // Finally echo out heading
					echo '<th class="' . $headingClass . '" ' . $dataAttributes . '>' . $titleString . $heading['label'] . $titleStringClose . '</th>';
					$headingCount++;
				}
				?>
			</tr>
			</thead>
			<?php
			$this->headingCount = $headingCount;
			echo $this->loadTemplate('body');
			?>
            <tfoot>
            <?php echo ET_General_Helper::fooFooterPagination($this->show_pagination && $this->show_pagination_footer, $headingCount); ?>
            </tfoot>
        </table>
	</div>
</div>
<!-- contentpaneclosed -->

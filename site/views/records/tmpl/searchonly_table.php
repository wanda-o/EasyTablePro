		<?php
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
				}
				?>
			</tr>
			</thead>
			<tbody>
			<?php
			$this->assign('currentImageDir', $this->imageDir);
			$alt_rv = 0;
			$rowNumber = 0;

			// Looping through the rows of paginated data
			foreach ($this->items as $rowIndex => $prow)
			{
				if (is_object($prow))
				{
					// Open the row
					if ($this->pagination && ($this->pagination->total == $prow->id))
					{
						echo "<tr class='row$alt_rv et_last_row' id='row-$prow->id'>";
					}
					else
					{
						echo '<tr class=\'row' . $alt_rv . '\' id=\'row-' . $prow->id . '\'>';
					}

					$columnNumber = 0;
					$numberOfListFields = count($this->easytable->filv);

					// Looping through the fields of the row
					foreach ($prow as $k => $f)
					{
						// We skip the row id which is in position 0
						if (!($k == 'id'))
						{
							if ($columnNumber >= $numberOfListFields)
							{
								continue;
							}

							if (isset($this->easytable->table_meta[$k]))
							{
								$labels			= $this->easytable->table_meta[$k];
							}

							// Is this field shown in the list view?
							if (!$labels['list_view'])
							{
								continue;
							}

							// Make sure cellData is empty before we start this cell.
							$cellData		= '';
							$cellClass		= $labels['fieldalias'];
							$cellType		= (int) $labels['type'];
							$cellDetailLink = (int) $labels['detail_link'];
							$cellOptions	= $labels['params'];

							// We increment labelnumber for next pass.
							$columnNumber++;
							$cellData		= ET_VHelper::getFWO($f, $cellType, $cellOptions, $prow, $this->currentImageDir);

							// As a precaution we make sure the detail link cell is not a URL field
							if ($cellDetailLink && ($cellType != 2))
							{
								$linkToDetail = 'index.php?option=com_easytablepro&view=record&id=' . $this->easytable->id . '&rid=' . $rowId;

								// There is a define label field a the URL leaf.
								$leaf = $this->title_leaf;
								$linkToDetail .= $leaf ? '&rllabel=' . JFilterOutput::stringURLSafe(substr($prow->$leaf, 0, 100)) : '';
								$linkToDetail = JRoute::_($linkToDetail);
								$cellData = '<a href="' . $linkToDetail . '">' . $cellData . '</a>';
								$cellDetailLink = '';
							}

							// Finally we can echo the cell string.
							echo "<td class='colfld " . $cellClass . "'>" . trim($cellData) . '</td>';
						}
						else // We store the rowID for possible use in a detaillink
						{
							$rowId = $f;
						}

						// End of row stuff should follow after this.
						unset($f);
					}

					// Close the Row
					echo '</tr>';
					$alt_rv = (int) !$alt_rv;
					$k = '';

					// Clear the rowId to prevent any issues.
					$rowId = '';
					unset($prow);
				}
			}	// End of foreach for rows
			?>
			</tbody>
		</table>
		<?php
		if ($this->SortableTable)
		{ ?>
			<script type="text/javascript">
				var t = new SortableTable(document.getElementById('<?php echo htmlspecialchars($this->easytable->easytablealias); ?>'), 'etAscending', 'etDescending');
			</script>
		<?php
		}
		if ($this->show_pagination && $this->show_pagination_footer && $this->etmCount) // If pagination is enabled show the controls
		{
			echo '<div class="pagination">';
			echo $this->pagination->getListFooter();
			echo '</div>';
		}
		?>

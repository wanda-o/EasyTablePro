<?php
/**
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');
	$et_tableName = $this->easytable->easytablename;
	JHTML::_('behavior.tooltip');
	if($this->recordId == 0) {
		JToolBarHelper::title(JText::_( 'CREATING_NEW_DESC' ).' '.JText::_( 'IN' ).' '.$et_tableName, 'easytableeditrecords');
		JToolBarHelper::save('saveNewRecord');
		JToolBarHelper::apply('applyNewRecord');
		JToolBarHelper::divider();
		JToolBarHelper::cancel('cancelNewRecord', JText::_( 'Cancel' ));
	} else {
		JToolBarHelper::title(JText::_( 'EDITING_RECORD__' ).' '.$this->recordId .' '.JText::_( 'IN' ).' '.$et_tableName, 'easytableeditrecords');
		JToolBarHelper::save('saveRecord');
		JToolBarHelper::apply('applyRecord');
		JToolBarHelper::divider();
		JToolBarHelper::cancel('cancelRecord', JText::_( 'Cancel' ));
	}
?>

<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div class="col100">
		<table width="100%">
			<tr>
				<td>
					<fieldset class="adminform " >
						<legend><?php echo $et_tableName.' - '.JText::_( 'DATA_RECORDS_' ); ?></legend>
						<table class="adminlist" id="et_fieldList">
						<thead>
							<tr valign="top">
								<th width= "100px"><?php echo (JText::_( 'LABEL' )); ?></th>
								<th ><?php echo (JText::_( 'VALUE' )); ?></th>
								<th ><?php echo (JText::_( 'PREVIEW' )); ?></th>
							</tr>
						</thead>
						<tbody id='et_data_table_rows'>
<?php
	$alt_rv = 0;
	$flds = array();
	foreach ( $this->et_meta as $label_row )
	{
		$label = $label_row['label'];
		$fld_alias = $label_row['fieldalias'];
		$flds[] = $fld_alias;
		$f_params = $label_row['params'];
		$value = ($this->recordId == 0)? '' : htmlentities ( $this->et_record[$fld_alias] );
		$type = $label_row['type'];
		echo '<tr valign="top" class="row'.$alt_rv.'" >'."\r";
		echo '<td>'.$label.'</td>';
		echo('<td>'.$this->getFieldInputType($fld_alias, $type, $value).'<input name="et_fld_orig_'.$fld_alias.'" type="hidden" value="'.$value.'" /></td>');
		echo('<td>'.($value == '' ? '<em>'.JText::_( 'CLICK__APPL_DESC' ).'</em>' : ($type == '1' ? $this->getImageTag($value,''):ET_VHelper::getFWO(html_entity_decode( $value ), $type, $f_params, $this->et_record, $this->et_record))).'</td>');
		echo "</tr>\r";
		$alt_rv = (int)!$alt_rv;
	}
?>
						</tbody>
						</table>
					</fieldset>
				</td>
			</tr>
		</table>
</div>
<div class="clr"></div>

<input type="hidden" name="rid" value="<?php echo $this->recordId; ?>" />
<input type="hidden" name="et_flds" value="<?php echo implode(',',$flds); ?>" />
<input type="hidden" name="option" value="<?php echo JRequest::getCmd('option'); ?>" />
<input type="hidden" name="id" value="<?php echo $this->easytable->id; ?>" />
<input type="hidden" name="task" value="" />
<?php echo JHTML::_('form.token'); ?>
</form>
<?php
/**
 * @version $Id$
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

		JToolBarHelper::title(JText::_( 'Easy Tables' ), 'generic.png');
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::editList();
		JToolBarHelper::deleteList('Are you sure you to delete the table(s)?');
		JToolBarHelper::addNew();
		JToolBarHelper::preferences( 'com_easytable' );
?>
<div id="et-versionCheck" style="text-size:0.9em;text-align:center; color:grey;position:relative;z-index:10000;" >
	Installed EasyTable version: <?php echo ( $this->et_current_version ); ?> | 
	<!-- Current Public Release is: <?php echo ( $this->et_public_version ); ?> -->
	<span id="et-pubverinfo" onmouseover="ShowTip('et-pubverinfo-panel'); return false;" onMouseOut="HideTip('et-pubverinfo-panel'); return false;">
		Current Public Release is: <?php echo ( $this->et_public_version ); ?>
		<div id="et-pubverinfo-panel" class="info-tip" style="left:50%;">
			<?php echo ( $this->et_public_tip ); ?>
		</div>
	</span>
	 | 
	<span id="et-subverinfo" onmouseover="ShowTip('et-subverinfo-panel'); return false;" onMouseOut="HideTip('et-subverinfo-panel'); return false;">
		Current Subscribers Release is: <?php echo ( $this->et_subscriber_version ); ?>
		<div id="et-subverinfo-panel" class="info-tip" style="left:60%;">
			<?php echo ( $this->et_subscriber_tip ); ?>
		</div>
	</span>
</div>
<form action="index.php" method="post" name="adminForm">
<div id="editcell">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="5">
				<?php echo JText::_( 'ID' ); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->rows ); ?>);" />
			</th>			
			<th>
				<?php echo JText::_( 'Table' ); ?>
			</th>
			<th>
				<?php echo JText::_( 'Description' ); ?>
			</th>
			<th width="20">
				<?php echo JText::_( 'Published' ); ?>
			</th>			
			<th>
				&nbsp;-&nbsp;
			</th>			
		</tr>			
	</thead>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->rows ); $i < $n; $i++)
	{
		$row = &$this->rows[$i];
		$checked 	= JHTML::_('grid.checkedout', $row,   $i);
		$published  = JHTML::_('grid.published',   $row,  $i );
		$link 		= JRoute::_( 'index.php?option=com_easytable&task=edit&cid[]='. $row->id );

		?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php echo $row->id; ?>
			</td>
			<td>
				<?php echo $checked; ?>
			</td>
			<td>
				<?php 
					$user = JFactory::getUser();
					if($row->checked_out && ($row->checked_out != $user->id))
					{
						echo $row->easytablename;
					}
					else
					{
						echo '<a href="'.$link.'">'.$row->easytablename.'</a>';
					}
				?>
			</td>
			<td>
				<?php echo $row->description; ?>
			</td>
			<td>
				<?php echo $published; ?>
			</td>
			<td>
				-
			</td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	</table>
</div>
<?php echo JHTML::_('form.token'); ?>
<input type="hidden" name="option" value="<?php echo $option ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<!--<input type="hidden" name="controller" value="easytable" /> -->
</form>

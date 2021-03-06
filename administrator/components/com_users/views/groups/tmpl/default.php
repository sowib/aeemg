<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user       = JFactory::getUser();
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));

JText::script('COM_USERS_GROUPS_CONFIRM_DELETE');

$groupsWithUsers = array();

foreach ($this->items as $i => $item)
{
	if ($item->user_count > 0)
	{
		array_push($groupsWithUsers, $i);
	}
}

JText::script('COM_USERS_GROUPS_CONFIRM_DELETE');

JFactory::getDocument()->addScriptDeclaration('
		Joomla.submitbutton = function(task) {
			if (task == "groups.delete") {
				var f = document.adminForm;
				var cb = "";
				var groupsWithUsers = [' . implode(',', $groupsWithUsers) . '];
				for (index = 0; index < groupsWithUsers.length; ++index) {
					cb = f["cb" + groupsWithUsers[index]];
					if (cb && cb.checked) {
						if (confirm(Joomla.JText._("COM_USERS_GROUPS_CONFIRM_DELETE"))) {
							Joomla.submitform(task);
						}
						return;
					}
				}
			}
			Joomla.submitform(task);
		};
');
?>
<form action="<?php echo JRoute::_('index.php?option=com_users&view=groups');?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('filterButton' => false))); ?>
		<div class="clearfix"> </div>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="groupList">
				<thead>
					<tr>
						<th width="1%">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th>
							<?php echo JHtml::_('searchtools.sort', 'COM_USERS_HEADING_GROUP_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th width="20%" class="center">
							<?php echo JText::_('COM_USERS_HEADING_USERS_IN_GROUP'); ?>
						</th>
						<th width="5%">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="4">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$canCreate = $user->authorise('core.create', 'com_users');
					$canEdit   = $user->authorise('core.edit', 'com_users');

					// If this group is super admin and this user is not super admin, $canEdit is false
					if (!$user->authorise('core.admin') && (JAccess::checkGroup($item->id, 'core.admin')))
					{
						$canEdit = false;
					}
					$canChange = $user->authorise('core.edit.state', 'com_users');
				?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center">
							<?php if ($canEdit) : ?>
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							<?php endif; ?>
						</td>
						<td>
							<?php echo str_repeat('<span class="gi">|&mdash;</span>', $item->level) ?>
							<?php if ($canEdit) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_users&task=group.edit&id=' . $item->id);?>">
								<?php echo $this->escape($item->title); ?></a>
							<?php else : ?>
								<?php echo $this->escape($item->title); ?>
							<?php endif; ?>
							<?php if (JDEBUG) : ?>
								<div class="small"><a href="<?php echo JRoute::_('index.php?option=com_users&view=debuggroup&group_id=' . (int) $item->id);?>">
								<?php echo JText::_('COM_USERS_DEBUG_GROUP');?></a></div>
							<?php endif; ?>
						</td>
						<td class="center">
							<?php echo $item->user_count ? '<a class="badge badge-success" href="' . JRoute::_('index.php?option=com_users&view=users&filter[group_id]=' . $item->id) . '">' . $item->user_count . '</a>' : '<span class="badge">0</span>'; ?>
						</td>
						<td>
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif;?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

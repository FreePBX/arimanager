<?php if(!$httpenabled) {?>
	<div class="alert alert-warning"><?php echo _('The Asterisk mini-HTTP Server is Currently Disabled in Advanced Settings')?></div>
<?php } else if(!$arienabled) {?>
	<div class="alert alert-warning"><?php echo _('The Asterisk REST Interface is Currently Disabled in Advanced Settings')?></div>
<?php } ?>

<div class="rnav">
	<ul>
		<li><a href="?display=arimanager"><?php echo _("Add User")?></a></li>
		<li><hr></li>
		<?php foreach($users as $u) { ?>
			<li><a id="current" href="?display=arimanager&amp;user=<?php echo $u['id']?>"><?php echo $u['name']?></a></li>
		<?php } ?>
	<ul>
</div>
<?php if(isset($user['id'])) {?>
	<a href="?display=arimanager&amp;user=<?php echo $user['id']?>&amp;action=delete"><span><img width="16" height="16" border="0" title="<?php echo sprintf(_('Delete User %s'),$user['name'])?>" alt="" src="images/core_delete.png">&nbsp;<?php echo sprintf(_('Delete User %s'),$user['name'])?></span></a>
<?php } ?>
<form autocomplete="off" id="ariform" method="post">
	<input type="hidden" name="id" value="<?php echo (isset($user['id']) ? $user['id'] : ''); ?>">
	<table>
		<tr><td colspan="2"><h5><?php echo (isset($user['id']) ? _("Edit Asterisk REST Interface User") : _("Add Asterisk REST Interface User")) ?><hr></h5></td></tr>
		<tr>
			<td colspan="2"><?php if(!empty($message)) {?><div class="alert alert-<?php echo $message['type']?>"><?php echo $message['message']?></div><?php } ?></td>
		</tr>
		<tr>
			<td><a href="#" class="info"><?php echo _("REST Interface User Name:")?><span><?php echo _("Asterisk REST Interface User Name")?></span></a></td>
			<td><input type="text" name="name" value="<?php echo (isset($user['name']) ? $user['name'] : ''); ?>"></td>
		</tr>
		<tr>
			<td><a href="#" class="info"><?php echo _("REST Interface User Password:")?><span><?php echo _("Asterisk REST Inferface Password.")?></span></a></td>
			<td><input type="password" name="password" value="<?php echo (isset($user['password']) ? '******' : ''); ?>"></td>
		</tr>
		<tr>
			<td><a href="#" class="info"><?php echo _("Password Type:")?><span><?php echo _("For the security concious, you probably don't want to put plaintext passwords in the configuration file. ARI supports the use of crypt(3) for password storage.")?></span></a></td>
			<td>
				<select name="password_type">
					<option value="crypt"><?php echo _('Crypt')?></option>
					<option value="plain" <?php echo (isset($user['password_format']) && $user['password_format'] == 'plain') ? 'selected' : '' ?>><?php echo _('Plain Text')?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td><a href="#" class="info"><?php echo _("Read Only:")?><span><?php echo _("Set to True for read-only applications.")?></span></a></td>
			<td>
				<span class="radioset">
					<input id="readonly-true" type="radio" name="readonly" value="yes" <?php echo (!isset($user['read_only']) || $user['read_only'] == 1) ? 'checked' : ''?>>
					<label for="readonly-true">True</label>
					<input id="readonly-false" type="radio" name="readonly" value="no" <?php echo (isset($user['read_only']) && $user['read_only'] == 0) ? 'checked' : ''?>>
					<label for="readonly-false">False</label>
				</span>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="submit" value="<?php echo _('Submit')?>" name="submit">
			</td>
		</tr>
	</table>
</form>
<script>var users = <?php echo json_encode($usernames)?></script>

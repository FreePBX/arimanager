<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
// Xavier Ourciere xourciere[at]propolys[dot]com
//
//This program is free software; you can redistribute it and/or
//modify it under the terms of the GNU General Public License
//as published by the Free Software Foundation; either version 2
//of the License, or (at your option) any later version.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.


$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
//the extension we are currently displaying
$managerdisplay = isset($_REQUEST['managerdisplay'])?$_REQUEST['managerdisplay']:'';
$name = isset($_REQUEST['name'])?$_REQUEST['name']:'';
$secret = isset($_REQUEST['secret'])?$_REQUEST['secret']:'';
$deny = isset($_REQUEST['deny'])?$_REQUEST['deny']:'0.0.0.0/0.0.0.0';
$permit = isset($_REQUEST['permit'])?$_REQUEST['permit']:'127.0.0.1/255.255.255.0';
$dispnum = "manager"; //used for switch on config.php

$engineinfo = engine_getinfo();
$astver =  $engineinfo['version'];
$ast_ge_16 = version_compare($astver, '1.6', 'ge');

//if submitting form, update database
global $amp_conf;
if($action == 'add' || $action == 'delete') {
	$ampuser = $amp_conf['AMPMGRUSER'];
	if($ampuser == $name) {
		$action = 'conflict';
	}
}
switch ($action) {
	case "add":
		$rights = manager_format_in($_REQUEST);
		manager_add($name,$secret,$deny,$permit,$rights['read'],$rights['write']);
		needreload();
	break;
	case "delete":
		manager_del($managerdisplay);
		needreload();
	break;
	case "edit":  //just delete and re-add
		manager_del($name);
		$rights = manager_format_in($_REQUEST);
		manager_add($name,$secret,$deny,$permit,$rights['read'],$rights['write']);
		needreload();
	break;
	case "conflict":
		//do nothing we are conflicting with the FreePBX Asterisk Manager User
	break;
}

$managers = manager_list();
?>
<div class="rnav"><ul>
    <li><a id="<?php echo ($managerdisplay=='' ? 'current':'') ?>" href="config.php?type=tool&amp;display=<?php echo urlencode($dispnum)?>"><?php echo _("Add Manager")?></a></li>
<?php
if (isset($managers)) {
	foreach ($managers as $manager) {
		echo "<li><a id=\"".($managerdisplay==$manager['name'] ? 'current':'')."\" href=\"config.php?type=tool&amp;display=".urlencode($dispnum)."&managerdisplay=".$manager['name']."\">{$manager['name']}</a></li>";
	}
}
?>
<ul></div>
<?php
if ($action == 'delete') {
	echo '<br><h3>'._("Manager").' '.$managerdisplay.' '._("deleted").'!</h3><br><br><br><br><br><br><br><br>';
} elseif($action == 'conflict') {
	echo '<br><h3>'.sprintf(_("Conflicting FreePBX Manager of %s has not been added"),$name).'!</h3><br><br><br><br><br><br><br><br>';
} else {
	if ($managerdisplay){ 
		//get details for this manager
		$thisManager = manager_get($managerdisplay);
		//create variables
		extract(manager_format_out($thisManager));
	}

	$delURL = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&action=delete';
?>

	
<?php		if ($managerdisplay){ ?>
	<h2><?php echo _("Manager:")." ". $managerdisplay; ?></h2>
	<p><a href="<?php echo $delURL ?>"><?php echo _("Delete Manager")?> <?php echo $managerdisplay; ?></a></p>
<?php		} else { ?>
	<h2><?php echo _("Add Manager"); ?></h2>
<?php		} 
			$tabindex = 0;
?>
	<form autocomplete="off" name="editMan" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return checkConf();">
	<input type="hidden" name="display" value="<?php echo $dispnum?>">
	<input type="hidden" name="action" value="<?php echo ($managerdisplay ? 'edit' : 'add') ?>">
	<table>
	<tr><td colspan="2"><h5><?php echo ($managerdisplay ? _("Edit Manager") : _("Add Manager")) ?><hr></h5></td></tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Manager name:")?><span><?php echo _("Name of the manager without space.")?></span></a></td>
		<td><input type="text" name="name" value="<?php echo (isset($name) ? $name : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Manager secret:")?><span><?php echo _("Password for the manager.")?></span></a></td>
		<td><input type="text" name="secret" value="<?php echo (isset($secret) ? $secret : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Deny:")?><span><?php echo _("If you want to deny many hosts or networks, use & char as separator.<br/><br/>Example: 192.168.1.0/255.255.255.0&10.0.0.0/255.0.0.0")?></span></a></td>
		<td><input size="56" type="text" name="deny" value="<?php echo (isset($deny) ? $deny : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Permit:")?><span><?php echo _("If you want to permit many hosts or networks, use & char as separator. Look at deny example.")?></span></a></td>
		<td><input size="56" type="text" name="permit" value="<?php echo (isset($permit) ? $permit : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td colspan="2"><h5><?php echo _("Rights")?><hr></h5></td>
	</tr>
	<tr>
		<td colspan="2">
		<table>
			<tr><th></th><th><?php echo _("Read")?></th><th><?php echo _("Write")?></th></tr>
			<tr>
				<td><a href="#" class="info">system<span><?php echo _("Check Asterisk documentation.")?></span></a></td>
				<td><input type="checkbox" name="rsystem" <?php echo (isset($rsystem)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
				<td><input type="checkbox" name="wsystem" <?php echo (isset($wsystem)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
			</tr>
			<tr>
				<td><a href="#" class="info">call<span><?php echo _("Check Asterisk documentation.")?></span></a></td>
				<td><input type="checkbox" name="rcall" <?php echo (isset($rcall)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
				<td><input type="checkbox" name="wcall" <?php echo (isset($wcall)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
			</tr>
			<tr>
				<td><a href="#" class="info">log<span><?php echo _("Check Asterisk documentation.")?></span></a></td>
				<td><input type="checkbox" name="rlog" <?php echo (isset($rlog)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
				<td><input type="checkbox" name="wlog" <?php echo (isset($wlog)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
			</tr>
			<tr>
				<td><a href="#" class="info">verbose<span><?php echo _("Check Asterisk documentation.")?></span></a></td>
				<td><input type="checkbox" name="rverbose" <?php echo (isset($rverbose)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
				<td><input type="checkbox" name="wverbose" <?php echo (isset($wverbose)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
			</tr>
			<tr>
				<td><a href="#" class="info">command<span><?php echo _("Check Asterisk documentation.")?></span></a></td>
				<td><input type="checkbox" name="rcommand" <?php echo (isset($rcommand)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
				<td><input type="checkbox" name="wcommand" <?php echo (isset($wcommand)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
			</tr>
			<tr>
				<td><a href="#" class="info">agent<span><?php echo _("Check Asterisk documentation.")?></span></a></td>
				<td><input type="checkbox" name="ragent" <?php echo (isset($ragent)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
				<td><input type="checkbox" name="wagent" <?php echo (isset($wagent)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
			</tr>
			<tr>
				<td><a href="#" class="info">user<span><?php echo _("Check Asterisk documentation.")?></span></a></td>
				<td><input type="checkbox" name="ruser" <?php echo (isset($ruser)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
				<td><input type="checkbox" name="wuser" <?php echo (isset($wuser)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
			</tr>
<?php
  if ($ast_ge_16) {
?>
			<tr>
				<td><a href="#" class="info">config<span><?php echo _("Check Asterisk documentation.")?></span></a></td>
				<td><input type="checkbox" name="rconfig" <?php echo (isset($rconfig)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
				<td><input type="checkbox" name="wconfig" <?php echo (isset($wconfig)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
			</tr>
			<tr>
				<td><a href="#" class="info">dtmf<span><?php echo _("Check Asterisk documentation.")?></span></a></td>
				<td><input type="checkbox" name="rdtmf" <?php echo (isset($rdtmf)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
				<td><input type="checkbox" name="wdtmf" <?php echo (isset($wdtmf)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
			</tr>
			<tr>
				<td><a href="#" class="info">reporting<span><?php echo _("Check Asterisk documentation.")?></span></a></td>
				<td><input type="checkbox" name="rreporting" <?php echo (isset($rreporting)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
				<td><input type="checkbox" name="wreporting" <?php echo (isset($wreporting)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
			</tr>
			<tr>
				<td><a href="#" class="info">cdr<span><?php echo _("Check Asterisk documentation.")?></span></a></td>
				<td><input type="checkbox" name="rcdr" <?php echo (isset($rcdr)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
				<td><input type="checkbox" name="wcdr" <?php echo (isset($wcdr)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
			</tr>
			<tr>
				<td><a href="#" class="info">dialplan<span><?php echo _("Check Asterisk documentation.")?></span></a></td>
				<td><input type="checkbox" name="rdialplan" <?php echo (isset($rdialplan)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
				<td><input type="checkbox" name="wdialplan" <?php echo (isset($wdialplan)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
			</tr>
			<tr>
				<td><a href="#" class="info">originate<span><?php echo _("Check Asterisk documentation.")?></span></a></td>
				<td><input type="checkbox" name="roriginate" <?php echo (isset($roriginate)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
				<td><input type="checkbox" name="woriginate" <?php echo (isset($woriginate)?"checked":'');?> tabindex="<?php echo ++$tabindex;?>"></td>
			</tr>

<?php // if 1.6 add these
  }
?>
			<tr>
				<td><a href="#" class="info">ALL<span><?php echo _("Check All/None.")?></span></a></td>
				<td><input type="checkbox" name="rallnone" onclick="readCheck();"></td>
				<td><input type="checkbox" name="wallnone" onclick="writeCheck();"></td>
			</tr>
		</table>
		</td>
	</tr>
						   
	<tr>
		<td colspan="2"><br><h6><input name="Submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>"></h6></td>		
	</tr>
	</table>
<script language="javascript">
<!--

var theForm = document.editMan;

theForm.name.focus();

function writeCheck() {
	if (theForm.wallnone.checked) {
		theForm.wsystem.checked = true;
		theForm.wcall.checked = true;
		theForm.wlog.checked = true;
		theForm.wverbose.checked = true;
		theForm.wcommand.checked = true;
		theForm.wagent.checked = true;
		theForm.wuser.checked = true;
<?php
  if ($ast_ge_16) {
?>
		theForm.wconfig.checked = true;
		theForm.wdtmf.checked = true;
		theForm.wreporting.checked = true;
		theForm.wcdr.checked = true;
		theForm.wdialplan.checked = true;
		theForm.woriginate.checked = true;
<?php
  }
?>
	} else {
		theForm.wsystem.checked = false;
		theForm.wcall.checked = false;
		theForm.wlog.checked = false;
		theForm.wverbose.checked = false;
		theForm.wcommand.checked = false;
		theForm.wagent.checked = false;
		theForm.wuser.checked = false;

<?php
  if ($ast_ge_16) {
?>
		theForm.wconfig.checked = false;
		theForm.wdtmf.checked = false;
		theForm.wreporting.checked = false;
		theForm.wcdr.checked = false;
		theForm.wdialplan.checked = false;
		theForm.woriginate.checked = false;
<?php
  }
?>
	}
}

function readCheck() {
	if (theForm.rallnone.checked) {
		theForm.rsystem.checked = true;
		theForm.rcall.checked = true;
		theForm.rlog.checked = true;
		theForm.rverbose.checked = true;
		theForm.rcommand.checked = true;
		theForm.ragent.checked = true;
		theForm.ruser.checked = true;
<?php
  if ($ast_ge_16) {
?>
		theForm.rconfig.checked = true;
		theForm.rdtmf.checked = true;
		theForm.rreporting.checked = true;
		theForm.rcdr.checked = true;
		theForm.rdialplan.checked = true;
		theForm.roriginate.checked = true;
<?php
  }
?>
	} else {
		theForm.rsystem.checked = false;
		theForm.rcall.checked = false;
		theForm.rlog.checked = false;
		theForm.rverbose.checked = false;
		theForm.rcommand.checked = false;
		theForm.ragent.checked = false;
		theForm.ruser.checked = false;
<?php
  if ($ast_ge_16) {
?>
		theForm.rconfig.checked = false;
		theForm.rdtmf.checked = false;
		theForm.rreporting.checked = false;
		theForm.rcdr.checked = false;
		theForm.rdialplan.checked = false;
		theForm.roriginate.checked = false;
<?php
  }
?>
	}
}

function checkConf()
{
	var errName = "<?php echo _('The manager name cannot be empty or may not have any space in it.'); ?>";
	var errSecret = "<?php echo _('The manager secret cannot be empty.'); ?>";
	var errDeny = "<?php echo _('The manager deny is not well formatted.'); ?>";
	var errPermit = "<?php echo _('The manager permit is not well formatted.'); ?>";
	var errRead = "<?php echo _('The manager read field is not well formatted.'); ?>";
	var errWrite = "<?php echo _('The manager write field is not well formatted.'); ?>";

	defaultEmptyOK = false;
	if ((theForm.name.value.search(/\s/) >= 0) || (theForm.name.value.length == 0))
		return warnInvalid(theForm.name, errName);
	if (theForm.secret.value.length == 0)
		return warnInvalid(theForm.name, errSecret);
	// Only IP/MASK format are checked
	if (theForm.deny.value.search(/\b(?:\d{1,3}\.){3}\d{1,3}\b\/\b(?:\d{1,3}\.){3}\d{1,3}\b(&\b(?:\d{1,3}\.){3}\d{1,3}\b\/\b(?:\d{1,3}\.){3}\d{1,3}\b)*$/))
		return warnInvalid(theForm.name, errDeny);
	if (theForm.permit.value.search(/\b(?:\d{1,3}\.){3}\d{1,3}\b\/\b(?:\d{1,3}\.){3}\d{1,3}\b(&\b(?:\d{1,3}\.){3}\d{1,3}\b\/\b(?:\d{1,3}\.){3}\d{1,3}\b)*$/))
		return warnInvalid(theForm.name, errPermit);
	return true;
}

//-->
</script>
	</form>
<?php		
} //end if action == delGRP
?>

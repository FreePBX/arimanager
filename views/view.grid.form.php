<?php 
    if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
?>

<!--Add Modal -->
<div class="modal fade" id="userForm" tabindex="-1" role="dialog" aria-labelledby="userForm" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo _("Loading...") ?></h4>
            </div>
            <div class="modal-body">
                <form class="fpbx-submit" name="editManager" id="editManager" action="" autocomplete="off" role="form">
                    <input type="hidden" id="idUser" value="" />

                    <!--REST Interface User Name-->
                    <div class="element-container">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="form-group">
                                        <div class="col-md-3">
                                            <label class="control-label" for="nameUser"><?php echo _("User Name") ?></label>
                                            <i class="fa fa-question-circle fpbx-help-icon" data-for="nameUser"></i>
                                        </div>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" id="nameUser" name="nameUser" value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <span id="nameUser-help" class="help-block fpbx-help-block"><?php echo _("Asterisk REST Interface User Name")?></span>
                            </div>
                        </div>
                    </div>
                    <!--END REST Interface User Name-->
                    <!--REST Interface User Password-->
                    <div class="element-container">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                <div class="form-group">
                                    <div class="col-md-3">
                                        <label class="control-label" for="passwordUser"><?php echo _("User Password") ?></label>
                                            <i class="fa fa-question-circle fpbx-help-icon" data-for="passwordUser"></i>
                                        </div>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control password-meter confidential" id="passwordUser" name="passwordUser" value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <span id="passwordUser-help" class="help-block fpbx-help-block"><?php echo _("Asterisk REST Inferface Password.")?></span>
                            </div>
                        </div>
                    </div>
                    <!--END REST Interface User Password-->
                    <!--Password Type-->
                    <div class="element-container">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="form-group">
                                        <div class="col-md-3">
                                            <label class="control-label" for="password_formatUser"><?php echo _("Password Type") ?></label>
                                            <i class="fa fa-question-circle fpbx-help-icon" data-for="password_formatUser"></i>
                                        </div>
                                        <div class="col-md-9 radioset">
                                            <input type="hidden" id="oldPasswordType" value="">
                                            <input type="radio" name="password_formatUser" id="password_formatUserCrypt" value="crypt">
                                            <label for="password_formatUserCrypt"><?php echo _("Crypt");?></label>
                                            <input type="radio" name="password_formatUser" id="password_formatUserPlain" value="plain">
                                            <label for="password_formatUserPlain"><?php echo _("Plain Text");?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <span id="password_formatUser-help" class="help-block fpbx-help-block"><?php echo _("For the security consious, you probably don't want to put plaintext passwords in the configuration file. ARI supports the use of crypt(3) for password storage.")?></span>
                            </div>
                        </div>
                    </div>
                    <!--END Password Type-->
                    <!--Read Only-->
                    <div class="element-container">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="form-group">
                                        <div class="col-md-3">
                                            <label class="control-label" for="read_onlyUser"><?php echo _("Read Only") ?></label>
                                            <i class="fa fa-question-circle fpbx-help-icon" data-for="read_onlyUser"></i>
                                        </div>
                                        <div class="col-md-9 radioset">
                                            <input type="radio" name="read_onlyUser" id="read_onlyUserYes" value="yes">
                                            <label for="read_onlyUserYes"><?php echo _("Yes");?></label>
                                            <input type="radio" name="read_onlyUser" id="read_onlyUserNo" value="no">
                                            <label for="read_onlyUserNo"><?php echo _("No");?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <span id="read_onlyUser-help" class="help-block fpbx-help-block"><?php echo _("Set to Yes for read-only applications.")?></span>
                            </div>
                        </div>
                    </div>
                    <!--END Read Only-->

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _("Close") ?></button>
                <button type="button" class="btn btn-success" id="submitForm"><?php echo _("Save Changes") ?></button>
            </div>
        </div>
    </div>
</div>
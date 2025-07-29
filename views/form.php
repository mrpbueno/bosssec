<form autocomplete="off" class="fpbx-submit" name="cs_form" action="" method="post">
    <input type="hidden" name="action" value="<?php echo empty($id) ? 'add' : 'edit' ?>">
    <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">

    <div class="element-container">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="form-group">
                        <div class="col-md-3">
                            <label class="control-label" for="boss_name"><?php echo _("Boss's Name") ?></label>
                            <i class="fa fa-question-circle fpbx-help-icon" data-for="boss_name"></i>
                        </div>
                        <div class="col-md-9">
                            <input type="text" class="form-control" id="boss_name" name="boss_name" value="<?php echo isset($boss_name) ? htmlspecialchars($boss_name, ENT_QUOTES, 'UTF-8') : '' ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row"><div class="col-md-12"><span id="boss_name-help" class="help-block fpbx-help-block"><?php echo _("A descriptive name for the boss.")?></span></div></div>
    </div>
    <div class="element-container">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="form-group">
                        <div class="col-md-3">
                            <label class="control-label" for="boss_extension"><?php echo _("Boss's Extension") ?></label>
                            <i class="fa fa-question-circle fpbx-help-icon" data-for="boss_extension"></i>
                        </div>
                        <div class="col-md-9">
                            <select class="form-control" id="boss_extension" name="boss_extension" style="width: 100%;" required>
                                <option value=""><?php echo _("Select an extension") ?></option>
                                <?php if (!empty($devices)): ?>
                                    <?php foreach ($devices as $device): ?>
                                        <option value="<?php echo $device['id'] ?>" <?php echo (isset($boss_extension) && $boss_extension == $device['id']) ? 'selected' : '' ?>>
                                            <?php echo htmlspecialchars($device['description'] . ' <' . $device['id'] . '>', ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row"><div class="col-md-12"><span id="boss_extension-help" class="help-block fpbx-help-block"><?php echo _("The boss's extension whose calls will be forwarded to the secretary.")?></span></div></div>
    </div>
    <div class="element-container">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="form-group">
                        <div class="col-md-3">
                            <label class="control-label" for="secretary_extension"><?php echo _("Secretary's Extension") ?></label>
                            <i class="fa fa-question-circle fpbx-help-icon" data-for="secretary_extension"></i>
                        </div>
                        <div class="col-md-9">
                            <select class="form-control" id="secretary_extension" name="secretary_extension" style="width: 100%;" required>
                                <option value=""><?php echo _("Select an extension") ?></option>
                                <?php if (!empty($devices)): ?>
                                    <?php foreach ($devices as $device): ?>
                                        <option value="<?php echo $device['id'] ?>" <?php echo (isset($secretary_extension) && $secretary_extension == $device['id']) ? 'selected' : '' ?>>
                                            <?php echo htmlspecialchars($device['description'] . ' <' . $device['id'] . '>', ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row"><div class="col-md-12"><span id="secretary_extension-help" class="help-block fpbx-help-block"><?php echo _("The extension where calls will be forwarded.")?></span></div></div>
    </div>
    <div class="element-container">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="form-group">
                        <div class="col-md-3">
                            <label class="control-label" for="whitelist"><?php echo _("Whitelist Numbers") ?></label>
                            <i class="fa fa-question-circle fpbx-help-icon" data-for="whitelist"></i>
                        </div>
                        <div class="col-md-9">
                            <textarea class="form-control" id="whitelist" name="whitelist" rows="5"><?php echo isset($whitelist) ? htmlspecialchars($whitelist, ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row"><div class="col-md-12"><span id="whitelist-help" class="help-block fpbx-help-block"><?php echo _("Numbers that can call the boss directly. Add one number per line.")?></span></div></div>
    </div>
    <div class="element-container">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="form-group">
                        <div class="col-md-3">
                            <label for="enabled"><?php echo _('Rule Enabled')?></label>
                            <i class="fa fa-question-circle fpbx-help-icon" data-for="enabled"></i>
                        </div>
                        <div class="col-md-9">
                            <span class="radioset">
                                <input type="radio" id="enabled_yes" name="enabled" value="1" <?php echo (!isset($enabled) || $enabled == 1) ? 'checked' : '' ?>>
                                <label for="enabled_yes"><?php echo _('Yes')?></label>
                                <input type="radio" id="enabled_no" name="enabled" value="0" <?php echo (isset($enabled) && $enabled == 0) ? 'checked' : '' ?>>
                                <label for="enabled_no"><?php echo _('No')?></label>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row"><div class="col-md-12"><span class="help-block fpbx-help-block" id="enabled-help"><?php echo _('Disable the rule without deleting it.')?></span></div></div>
    </div>
    </form>

<script>
    $(document).ready(function() {
        $('#boss_extension').select2();
        $('#secretary_extension').select2();
    });
</script>
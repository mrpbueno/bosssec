<div id="buttons-toolbar">
    <div class="btn-group" role="group">
        <button type="button"
                title="<?php echo _("Add Boss-Secretary Rule")?>"
                class="btn btn-default"
                onclick="location.href='?display=bosssec&view=form';">
            <i class="fa fa-plus"></i> <?php echo _("Add"); ?>
        </button>
    </div>
</div>

<table id="cs_grid"
       class="table table-striped"
       data-toggle="table"
       data-url="ajax.php?module=bosssec&command=getjson&jdata=grid"
       data-cache="false"
       data-show-refresh="true"
       data-search="true"
       data-toolbar="#buttons-toolbar">
    <thead>
        <tr>
            <th data-field="boss_name"><?php echo _("Boss's Name")?></th>
            <th data-field="boss_extension"><?php echo _("Boss's Extension")?></th>
            <th data-field="secretary_extension"><?php echo _("Secretary's Extension")?></th>
            <th data-field="enabled" data-formatter="statusFormatter"><?php echo _("Status")?></th>
            <th data-field="id" data-formatter="actionFormatter" data-width="80px"><?php echo _("Actions")?></th>
        </tr>
    </thead>
</table>

<script>
var cs_translations = {
    edit: "<?php echo _('Edit') ?>",
    delete: "<?php echo _('Delete') ?>",
    activated: "<?php echo _('Activated') ?>",
    deactivated: "<?php echo _('Deactivated') ?>"
};

function actionFormatter(value, row) {
    let html = '';
    html += '<a href="?display=bosssec&view=form&id=' + value + '"><i class="fa fa-edit text-success" title="' + cs_translations.edit + '"></i></a>&nbsp;';
    html += '<a href="?display=bosssec&action=delete&id=' + value + '" class="delAction"><i class="fa fa-trash text-danger" title="' + cs_translations.delete + '"></i></a>';
    return html;
}

function statusFormatter(value, row) {
    if (value == 1) {
        return '<i class="fa fa-check-circle text-success" title="' + cs_translations.activated + '"></i>';
    } else {
        return '<i class="fa fa-times-circle text-danger" title="' + cs_translations.deactivated + '"></i>';
    }
}

(function($) {
    $(function() {
        var gridContainer = $('#grid-container');
        var toastDataJson = gridContainer.data('toast');
        if (toastDataJson) {
            fpbxToast(toastDataJson.message, toastDataJson.title, toastDataJson.level);
            gridContainer.removeAttr('data-toast');
        }
    });
})(jQuery);
</script>
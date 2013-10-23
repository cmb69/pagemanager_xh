<!-- Pagemanager_XH: widget -->
<form id="pagemanager-form" action="<?php echo $actionUrl;?>" method="post" accept-charset="UTF-8" onsubmit="PAGEMANAGER.beforeSubmit()">
<?php if ($isIrregular):?>
    <div id="pagemanager-structure-warning" class="cmsimplecore_warning">
        <p><?php echo $structureWarning;?></p>
        <p><button type="button" onclick="PAGEMANAGER.confirmStructureWarning()"><?php echo $structureConfirmation;?></button></p>
    </div>
<?php endif;?>
    <!-- toolbar -->
<?php if ($showToolbar):?>
    <div id="pagemanager-toolbar" class="<?php echo $toolbarClass;?>">
<?php foreach ($tools as $tool):?>
        <?php echo Pagemanager_tool($tool);?>
<?php endforeach;?>
        <div style="clear"></div>
    </div>
<?php endif;?>
    <div id="pagemanager" ondblclick="jQuery('#pagemanager').jstree('toggle_node')">
        <?php echo Pagemanager_pages();?>
    </div>
    <input type="hidden" name="admin" value=""/>
    <input type="hidden" name="action" value="plugin_save"/>
    <input type="hidden" name="xml" id="pagemanager-xml" value=""/>
    <input id="pagemanager-submit" type="submit" class="submit" value="<?php echo $saveButton;?>" style="display: none"/>
</form>
<div id="pagemanager-footer"></div>
<div id="pagemanager-confirmation" title="<?php echo $titleConfirm;?>"></div>
<div id="pagemanager-alert" title="<?php echo $titleInfo;?>"></div>
<script type="text/javascript">
    /* <![CDATA[ */
    var PAGEMANAGER = {};
    PAGEMANAGER.config = <?php echo $config;?>;
    /* ]]> */
</script>
<script type="text/javascript" src="<?php echo $script;?>"></script>

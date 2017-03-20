<h1>Pagemanager – <?=$this->text('menu_main')?></h1>
<form id="pagemanager-form" action="<?=$this->submissionUrl()?>"
      method="post" accept-charset="UTF-8">
<?php if ($this->isIrregular):?>
    <div id="pagemanager-structure-warning" class="cmsimplecore_warning">
        <p><?=$this->text('error_structure_warning')?></p>
        <p>
            <button type="button">
                <?=$this->text('error_structure_confirmation')?>
            </button>
        </p>
    </div>
<?php endif?>
    <p class="pagemanager-status" style="display:none">
        <img src="<?=$this->ajaxLoaderPath()?>" alt="Loading">
    </p>
<?php if ($this->hasToolbar):?>
    <div id="pagemanager-toolbar" class="<?=$this->toolbarClass()?>">
<?php   foreach ($this->tools as $tool):?>
        <?=$this->escape($tool)?>
<?php   endforeach?>
        <div style="clear: both"></div>
    </div>
<?php endif?>
    <div id="pagemanager" class="<?=$this->toolbarClass()?>"></div>
    <input type="hidden" name="admin" value="plugin_main">
    <input type="hidden" name="action" value="plugin_save">
    <input type="hidden" name="json" id="pagemanager-json" value="">
    <input id="pagemanager-submit" type="submit" class="submit"
           value="<?=$this->text('button_save')?>" style="display: none">
    <?=$this->csrfTokenInput()?>
    <p class="pagemanager-status" style="display:none">
        <img src="<?=$this->ajaxLoaderPath()?>" alt="Loading">
    </p>
</form>
<div id="pagemanager-footer"></div>
<div id="pagemanager-confirmation" title="<?=$this->text('message_confirm')?>"></div>
<div id="pagemanager-alert" title="<?=$this->text('message_information')?>"></div>
<script type="text/javascript">var PAGEMANAGER = <?=$this->jsConfig()?>;</script>
<script type="text/javascript" src="<?=$this->jsScriptPath()?>"></script>

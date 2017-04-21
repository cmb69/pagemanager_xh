<h1>Pagemanager – <?=$this->text('menu_info')?></h1>
<img src="<?=$this->logoPath()?>" style="float: left; margin-right: 10px" alt="Plugin Icon">
<p>
    Version: <?=$this->version()?>
</p>
<p>
    Copyright &copy; 2011-2017 <a href="http://3-magi.net">Christoph M. Becker</a>
</p>
<p>
    Pagemanager_XH is powered by <a
    href="http://www.cmsimple-xh.org/wiki/doku.php/extend:jquery4cmsimple">
    jQuery4CMSimple</a> and <a href="http://www.jstree.com/">jsTree</a>.
</p>
<p style="text-align: justify">
    Pagemanager_XH is free software: you can redistribute it and/or modify it
    under the terms of the GNU General Public License as published by the Free
    Software Foundation, either version 3 of the License, or (at your option)
    any later version.
</p>
<p style="text-align: justify">
    Pagemanager_XH is distributed in the hope that it will be useful, but
    <em>without any warranty</em>; without even the implied warranty of
    <em>merchantability</em> or <em>fitness for a particular purpose</em>. See
    the GNU General Public License for more details.
</p>
<p style="text-align: justify">
    You should have received a copy of the GNU General Public License along with
    this program. If not, see <a
    href="http://www.gnu.org/licenses/">http://www.gnu.org/licenses/</a>.
</p>
<div class="pagemanager_syscheck">
    <h2><?=$this->text('syscheck_title')?></h2>
<?php foreach ($this->checks as $check):?>
    <p class="xh_<?=$this->escape($check->state)?>"><?=$this->text('syscheck_message', $check->label, $check->stateLabel)?></p>
<?php endforeach?>
</div>

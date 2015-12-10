<?php
/**
 * Sample layout
 */

use Core\Language;

$user = $data['current_user'];

?>
<div class="dropdown">
  <button class="btn btn-default dropdown-toggle" type="button" id="select-span" data-toggle="dropdown" aria-haspopup="true">
    Select timespan
    <span class="caret"></span>
  </button>
  <ul class="dropdown-menu" aria-labelledby="select-span">
    <li><a href="<?=DIR?>audit/<?=strtotime('-1 day', $data['since'])?>-<?=$data['until']?>">1 Day</a></li>
    <li><a href="<?=DIR?>audit/<?=strtotime('-1 week', $data['since'])?>-<?=$data['until']?>">1 Week</a></li>
    <li><a href="<?=DIR?>audit/<?=strtotime('-1 month', $data['since'])?>-<?=$data['until']?>">1 Month</a></li>
    <li role="separator" class="divider"></li>
    <li><a href="<?=DIR?>audit/0-0">All</a></li>
  </ul>
</div>
<ul class="pager">
    <li class="previous"><a href="<?=DIR?>audit/<?=$data['since']-$data['span']?>-<?=$data['until']-$data['span']?>"><span aria-hidden="true">&larr;</span> Older</a></li>
    <li class="next"><a href="<?=DIR?>audit/<?=$data['since']+$data['span']?>-<?=$data['until']+$data['span']?>">Newer <span aria-hidden="true">&rarr;</span></a></li>
</ul>
<div class="panel panel-default table-responsive">
    <div class="panel-heading">
        <h3 class="panel-title">
        Audit log from <?=date('c', $data['since'])?> to <?=date('c', $data['until'])?>
        </h3>
    </div>
    <table class="table table-striped table-condensed table-hover">
        <thead>
        <tr>
            <th>When</th>
            <th>Who</th>
            <th>What</th>
            <th>Context</th>
        </tr>
        </thead>
        <tbody>
<?php foreach ($data['logs'] as $log) { ?>
        <tr>
            <td>
                <?=date('c', $log->_when)?>
            </td>
            <td>
                <?if (($who_id = $log->who_id()) != NULL) {?>
                    <a href='<?=DIR?>users/<?=$log->who_id()?>'><?=$log->who_login()?></a>
                <?} else {?>
                    <?=$log->who_login()?>
                <?}?>
            </td>
            <td>
                <?=$log->_what?>
            </td>
            <td>
                <?if (($extra = $log->pretty_extra()) != NULL) {?>
                    <pre><?=$log->pretty_extra()?></pre>
                <?} else {?>
                    &nbsp;
                <?}?>
            </td>
        </tr>
<?php } ?>
        </tbody>
    </table>
</div>


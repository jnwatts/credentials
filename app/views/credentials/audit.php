<?php
/**
 * Sample layout
 */

use Core\Language;

$user = $data['current_user'];

function dfmt($start, $end) {
    return 'start='.date('Y-m-d', $start).'&end='.date('Y-m-d', $end);
}

?>
<div class="dropdown">
  <button class="btn btn-default dropdown-toggle" type="button" id="select-span" data-toggle="dropdown" aria-haspopup="true">
    Select timespan
    <span class="caret"></span>
  </button>
  <ul class="dropdown-menu" aria-labelledby="select-span">
    <li><a href="<?=DIR?>audit/?<?=dfmt(strtotime('-1 day', $data['until']),$data['until'])?>">1 Day</a></li>
    <li><a href="<?=DIR?>audit/?<?=dfmt(strtotime('-1 week', $data['until']),$data['until'])?>">1 Week</a></li>
    <li><a href="<?=DIR?>audit/?<?=dfmt(strtotime('-1 month', $data['until']),$data['until'])?>">1 Month</a></li>
  </ul>
</div>
<ul class="pager">
    <li class="previous"><a href="<?=DIR?>audit/?<?=dfmt($data['since']-$data['span'], $data['until']-$data['span'])?>"><span aria-hidden="true">&larr;</span> Older</a></li>
    <li class="next"><a href="<?=DIR?>audit/?<?=dfmt($data['since']+$data['span'], $data['until']+$data['span']+1)?>">Newer <span aria-hidden="true">&rarr;</span></a></li>
</ul>
<div class="panel panel-default table-responsive">
    <div class="panel-heading">
        <h3 class="panel-title">
        Audit log from <?=date('Y-m-d', $data['since'])?> to <?=date('Y-m-d', $data['until'])?>
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


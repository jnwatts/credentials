<?php
/**
 * Sample layout
 */

use Core\Language;

$current_user = $data['current_user'];
$user = $data['user'];
?>
<div class="panel panel-default">
    <div class="panel-heading" data-toggle="collapse" data-target="#user-info">
        <h3 class="panel-title">
            <span id="user-info-collapse-icon" class="glyphicon glyphicon-collapse-up"></span>
            User info
        </h3>
    </div>
    <div class="panel-body panel-collapse in" id="user-info">
        Login: <?=$user->login?><br>
        Name: <?=$user->fullname?><br>
        Email: <?=$user->email?><br>
        <?if ($current_user->isAdmin()) {?>
        Admin: <?=($user->isAdmin() ? "true" : "false")?> <button class="btn btn-xs" id="toggle-admin">Toggle</button>
        <?}?>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading" data-toggle="collapse" data-target="#add-key-panel">
        <h3 class="panel-title">
            <span id="add-key-collapse-icon" class="glyphicon glyphicon-collapse-down"></span>
            Add key
        </h3>
    </div>
    <div class="panel-body collapse" id="add-key-panel">
        <form id="add-key">
            <input-sshkey host="" hash=""></input-sshkey><br />
            <input type="file" name="" style="display: none">
            <button class="btn btn-primary" type="submit">
                Add
            </button>
            <button class="from-file btn" type="button">
                From file
            </button>
            <button class="cancel btn" type="button">
                Reset
            </button>
        </form>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading" data-toggle="collapse" data-target="#ssh-keys">
        <h3 class="panel-title">
            <span id="ssh-keys-collapse-icon" class="glyphicon glyphicon-collapse-up"></span>
            SSH keys
        </h3>
    </div>
    <div class="panel-body panel-collapse in" id="ssh-keys">
<ul class="list-group">
<?if (count($data['keys']) > 0) {?>
<?foreach ($data['keys'] as $key) { ?>
<li class="list-group-item">
    <button class="delete-key btn btn-sm btn-danger pull-right" style="margin-bottom: 10px" type="button" data-key-id="<?=$key->id?>">Delete</button>
    <strong><?=$key->host?></strong><br />
    <div class="well well-sm" style="word-wrap: break-word; clear: both"><?=$key->hash?></div>
</li>
<?}?>
<?} else {?>
<li class="list-group-item">
    None
</li>
<?}?>
</ul>
    </div>
</div>

<?if ($current_user->isAdmin()) {?>
<button class="btn btn-danger" id="delete-user">Delete user</button>
<?}?>

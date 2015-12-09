<?php
/**
 * Sample layout
 */

use Core\Language;

$user = $data['current_user'];

?>
<ol class="breadcrumb">
    <li><a href="<?=DIR?>">Credentials</a></li>
    <li class="active">Users</li>
</ol>

<div class="panel panel-default">
    <div class="panel-heading" data-toggle="collapse" data-target="#add-user-panel">
        <h3 class="panel-title">
        <span id="add-user-collapse-icon" class="glyphicon glyphicon-collapse-down"></span>
        Add user
        </h3>
    </div>
    <div class="panel-body collapse" id="add-user-panel">
        <form id="add-user">
            <label for="login">Username:</label>
            <input type="text" name="login" value="">
        </form>
        <button class="btn btn-primary" id="add-single-user">
            Add single user
        </button>
        <input type="file" style="display: none" id="add-many-keys" multiple>
        <button class="btn" id="add-many-keys">From multiple keys</button>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            Users
        </h3>
    </div>
<div class="list-group">
<?php foreach ($data['users'] as $user) { ?>
<a href="<?=DIR?>users/<?=$user->id?>" class="list-group-item">
    <span class="badge"><?=$user->numKeys?></span>
    <?=$user->login?><?=($user->isAdmin() ? ' <span class="label label-info">admin</span>' : '')?>
</a>
<?php } ?>
</div>
</div>


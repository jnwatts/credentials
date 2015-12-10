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
    <table class="table table-striped table-condensed table-hover">
        <thead>
        <tr>
            <th>Login</th>
            <th>Fullname</th>
            <th>Email</th>
            <th>Flags</th>
            <th>Keys</th>
        </tr>
        </thead>
        <tbody>
<?php foreach ($data['users'] as $user) { ?>
        <tr data-href="<?=DIR?>users/<?=$user->id?>">
            <td><a href="<?=DIR?>users/<?=$user->id?>"><?=$user->login?></a></td>
            <td><?=$user->fullname?></td>
            <td><?=$user->email?></td>
            <td>
                <?=(($user->ldap == 1) ? ' <span class="label label-info">ldap</span>' : '')?>
                <?=(($user->admin == 1) ? ' <span class="label label-info">admin</span>' : '')?>
            </td>
            <td><span class="badge"><?=$user->numKeys?></span></td>
        </tr>
<?php } ?>
        </tbody>
    </table>
</div>


<?php
/**
 * Sample layout
 */

use Core\Language;

$user = $data['current_user'];

?>
<div class="list-group">
    <a href="<?=DIR?>users" class="list-group-item">Users</a>
    <a href="<?=DIR?>audit" class="list-group-item">Audit log</a>
</div>

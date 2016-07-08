<?
use Helpers\Url;
?>

<script>
$('#toggle-admin').click(function() {
    var url = '<?=DIR?>users/<?=$data['user']->id?>';
console.log("Hello");
    $.ajax({
        type: 'post',
        url: url,
        data: JSON.stringify({
            admin: <?=($data['user']->isAdmin() ? 0 : 1)?>,
        }),
        contentType: "application/json; charset=utf-8",
    }).done(function(data) {
        //TODO: Dynamic rebuild of list
        location.reload();
    }).fail(function(data) {
        console.log(data);
        showError(data.responseText);
    });

});

$('#delete-user').click(function() {
    var url = '<?=DIR?>users/<?=$data['user']->id?>';
    $.ajax({
        type: 'delete',
        url: url,
    }).done(function(data) {
        //TODO: Dynamic rebuild of list
        location.href = '<?=DIR?>';
    }).fail(function(data) {
        console.log(data);
        showError(data.responseText);
    });
});

$('.delete-key').click(function() {
    var key_id = $(this).data('key-id');
    var url = '<?=DIR?>keys/' + key_id;
    $.ajax({
        type: 'delete',
        url: url,
    }).done(function(data) {
        //TODO: Dynamic rebuild of list
        location.reload();
    }).fail(function(data) {
        console.log(data);
        showError(data.responseText);
    });
});

$('#add-key > button[type=submit]').click(function(event) {
    event.preventDefault();
    var host = $('#add-key > input[name="host"]').val();
    var hash = $('#add-key > textarea[name="hash"]').val();
    $.ajax({
        type: 'post',
        url: '<?=DIR?>keys',
        data: JSON.stringify({
            user_id: <?=$data['user']->id?>,
            host: host,
            hash: hash
        }),
        contentType: "application/json; charset=utf-8",
    }).done(function(data) {
        //TODO: Dynamic rebuild of list
        location.reload();
    }).fail(function(data) {
        console.log(data);
        showError(data.responseText);
    });
});

$('#add-key > .cancel').click(function() {
    $('#add-key').trigger('reset');
});

$('#add-key > .from-file').click(function () {
    $('input[type=file]').trigger('click');
});

$('input[type=file]').change(function () {
    var f = this.files[0];
    if (f) {
        var reader = new FileReader();
        reader.onload = (function(f) {
            return function(e) {
                var filename = f.name;
                var parts = filename.match(/\S+@(\S+).pub/);
                var host;
                var hash = e.target.result;
                if (parts) {
                    host = parts[1];
                } else {
                    host = filename;
                }
                $('#add-key > input[name="host"]').val(host);
                $('#add-key > textarea[name="hash"]').val(hash);
            };
        })(f);
        reader.readAsText(f);
    }
});

enable_collapse_icon('#user-info', '#user-info-collapse-icon');
enable_collapse_icon('#add-key-panel', '#add-key-collapse-icon');
enable_collapse_icon('#ssh-keys', '#ssh-keys-collapse-icon');

</script>

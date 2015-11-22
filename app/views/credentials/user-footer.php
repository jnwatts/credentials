<?
use Helpers\Url;
?>

<link rel="import" href="<?=Url::templatePath()?>input-sshkey.html">

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

$('#add-key > button[type=submit]').click(function() {
    event.preventDefault();
    var sshkey = $('#add-key > input-sshkey');
    $.ajax({
        type: 'post',
        url: '<?=DIR?>keys',
        data: JSON.stringify({
            user_id: <?=$data['user']->id?>,
            host: sshkey.attr('host'),
            hash: sshkey.attr('hash')
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
    $('#add-key > input-sshkey').trigger('reset');
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
                var sshkey = $('#add-key > input-sshkey');
                var filename = f.name;
                var parts = filename.match(/\S+@(\S+).pub/);
                if (parts) {
                    sshkey.attr("host", parts[1]);
                } else {
                    sshkey.attr("host", filename);
                }
                sshkey.attr("hash", e.target.result);
            };
        })(f);
        reader.readAsText(f);
    }
});

</script>

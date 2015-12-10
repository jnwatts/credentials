<?
use Helpers\Url;
?>

<script>
$(function(){
    $('.table tr[data-href]').each(function(){
        $(this).css('cursor','pointer').click( function(){
            document.location = $(this).attr('data-href');
        });
    });
});

$('#add-single-user').click(function() {
    var form = $('form#add-user');
    $.ajax({
        type: 'post',
        url: '<?=DIR?>users',
        data: JSON.stringify({
            login: form.children('input[name=login]').val(),
            email: form.children('input[name=email]').val()
        }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
    }).done(function(data) {
        location.href = '<?=DIR?>users/' + data.id;
    }).fail(function(data) {
        console.log(data);
        showError(data.responseText);
    });
});

$('button#add-many-keys').click(function () {
    $('input[type=file]').trigger('click');
});

var readKeyFile = function(f) {
    var reader = new FileReader();
    var deferred = $.Deferred();

    reader.onload = (function(f) {
        return function(e) {
            var key = {};
            var filename = f.name;
            var parts = filename.match(/(\S+)@(\S+).pub/);
            if (parts) {
                key.user = parts[1];
                key.host = parts[2];
            } else {
                key.host = filename;
            }
            key.hash = e.target.result;
            if (/PRIVATE/.test(key.hash)) {
                deferred.reject({
                    message: 'Refuse to upload private key',
                    key: key
                });
            } else {
                deferred.resolve(key);
            }
        };
    })(f);

    reader.onerror = (function (f) {
        return function() {
            deferred.reject({message: 'Failed to read file', file: f});
        };
    })(f);

    reader.readAsText(f);

    return deferred.promise();
}

$('input[type=file]#add-many-keys').change(function () {
    var promises = [];
    for (var i = 0; i < this.files.length; i++) {
        promises.push(
            readKeyFile(this.files.item(i)).fail(
                function(e) {
                    console.log(e);
                    showError(e.message);
                }
            )
        );
    }
    $.when.apply($, promises).then(function () {
        var keys = $.map(arguments, function(v,i) { return [v]; });
        $.ajax({
            type: 'post',
            url: '<?=DIR?>keys',
            data: JSON.stringify(keys),
            contentType: "application/json; charset=utf-8",
        }).done(function(data) {
            var errors = 0;
            var successes = 0;
            data = JSON.parse(data);
            for (var i = 0; i < data.length; i++) {
                var result = data[i];
                if (result.status != 200) {
                    errors++;
                    showError('Key ' + result.user + '@' + result.host + ', error ' + result.status + ': ' + result.message);
                    console.log(result);
                } else {
                    successes++;
                }
            }
            if (successes > 0)
                showSuccess('Added ' + successes + ' keys');
        }).fail(function(data) {
            console.log(data);
            showError(data.responseText);
        });
    });
});

enable_collapse_icon('#add-user-panel', '#add-user-collapse-icon');
</script>

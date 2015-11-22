var showError = function(message) {
    $('.container').prepend('<div class="alert alert-danger"><strong>Failure:</strong> ' + message + ' <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
};
var showSuccess = function(message) {
    $('.container').prepend('<div class="alert alert-success"><strong>Success:</strong> ' + message + ' <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
};

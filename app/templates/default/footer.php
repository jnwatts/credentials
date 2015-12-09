<?php
/**
 * Sample layout
 */

use Helpers\Assets;
use Helpers\Url;
use Helpers\Hooks;
use Core\View;

//initialise hooks
$hooks = Hooks::get();
?>

</div>

<!-- JS -->
<?php
Assets::js(array(
    '//code.jquery.com/jquery-2.1.4.min.js',
	'//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js',
    Url::templatePath() . 'js/error-helper.js',
    Url::templatePath() . 'js/collapse-icon.js'
));

//hook for plugging in javascript
$hooks->run('js');

//hook for plugging in code into the footer
$hooks->run('footer');

if (isset($data['footer-logic'])) {
    View::render($data['footer-logic'], $data);
}    
?>

</body>
</html>

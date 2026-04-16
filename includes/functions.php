<?php
function base_url($path = '') {
    return 'http://localhost/foodorder/' . $path;
}

function is_logged_in() {
    return isset($_SESSION['user']);
}
?>

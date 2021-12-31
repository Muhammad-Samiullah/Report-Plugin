<?php
add_action('admin_post_submit-form', '_handle_form_action');
function _handle_form_action(){
    echo "done";
    die();


} 

?>
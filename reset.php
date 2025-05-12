<?php
// Reset the session to see the sample products
session_start();
session_destroy();
header('Location: /Product/list');
exit();
?>

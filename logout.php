<?php
session_start();
session_unset();
session_destroy();
header("Location: index%20.php");
exit();
?>

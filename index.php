<?php

require __DIR__.'/src/settings/Configuration.php';

session_start();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title>Propay</title>

        <script src="./js/jquery-3.6.0.js"></script>

        <link rel="stylesheet" href="./css/bootstrap.css"></link>
        <link rel="stylesheet" href="./css/main.css"></link>
    </head>
    <body>
        <div class="container-fluid">
<?php
if (isset($_SESSION[Configuration::get('VAR_USER_SESSION')])) {
    include __DIR__.'/view/partial/dashboard.php';
} else {
    include __DIR__.'/view/partial/login.php';
}
?>
        </div>

        <script src="./js/bootstrap.bundle.js"></script>
    </body>
</html>
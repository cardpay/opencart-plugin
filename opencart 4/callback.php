<?php
$actual_link = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]" . str_replace(
        'extension/unlimit/callback.php',
        'index.php',
        $_SERVER['REQUEST_URI']
    );

header('Refresh: 0; url=' . $actual_link);
die;

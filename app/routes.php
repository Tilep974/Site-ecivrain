<?php
$app->mount('/', new Livre\Controller\BlogControllerProvider());

$app->mount('/admin', new Livre\Controller\AdminControllerProvider());

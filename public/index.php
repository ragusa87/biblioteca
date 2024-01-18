<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

if($_SERVER['HTTP_HOST'] === "books-proxy.while.ch"){
    echo "<pre>";
    var_dump(getallheaders());
    echo file_get_contents('php://input');
    exit;
}

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};

<?php
session_start();
/**
 * Загружаем классы
 */
spl_autoload_register(function($name) {
    $fileController = 'controller/' . $name . '.php';
    if (file_exists($fileController)) {
        require $fileController;
    }
    $fileController = 'model/' . $name . '.php';
    if (file_exists($fileController)) {
        require $fileController;
    }
});
require_once 'lib/functions.php';
require_once "vendor/autoload.php";
$pathList = preg_split('/\//', $_SERVER['QUERY_STRING'], -1, PREG_SPLIT_NO_EMPTY);
// Значения по умолчанию
if (empty($_SESSION['user'])) {
    $pathList = ['user', 'add'];
} elseif (count($pathList) < 2) {
    if (!empty(getParam('add'))) {
        $pathList = ['task', 'add'];
    } elseif (!empty(getParam('assign'))) {
        $pathList = ['task', 'assign'];
    } elseif (!empty(getParam('sort'))) {
        $pathList = ['task', 'sort'];
    } else {
        $pathList = ['task', 'list'];
    }
}
if (count($pathList) >= 2) {
    $controller = array_shift($pathList);
    $action = array_shift($pathList);
    $params = [];
    foreach ($pathList as $i => $value) {
        if ($i % 2 == 0 && isset($pathList[$i + 1])) {
            $params[$pathList[$i]] = $pathList[$i + 1];
        }
    }
    $controllerText = $controller . 'Controller';
    $controllerFile = 'controller/' . ucfirst($controllerText) . '.php';
    if (is_file($controllerFile)) {
        include $controllerFile;
        if (class_exists($controllerText)) {
            $controller = new $controllerText($db);
            $action = ($_SERVER['REQUEST_METHOD'] == 'POST' ? 'post' : 'get') . ucfirst($action);
            if (method_exists($controller, $action)) {
                $controller->$action($params, $_POST);
            } else {
                redirect('index');
            }
        }
    }
}
<?php
abstract class BaseController
{
    protected $model = null;
    protected $modelName = 'Base';
    protected $template;
    public function __construct($db)
    {
        include 'model/' . $this->modelName . '.php';
        $this->model = new $this->modelName($db);
    }
    public abstract function getAdd();
    /**
     * Отображаем шаблон
     * @param $template
     * @param $params
     */
    protected function render($template, $params = [])
    {
        // Где лежат шаблоны
        $loader = new Twig_Loader_Filesystem('template/');
        // Где будут хранится файлы кэша (php файлы)
        $twig = new Twig_Environment($loader, array(
            'cache' => './tmp/cache',
            'auto_reload' => true,
        ));
        echo $twig->render($template, $params);
    }
    abstract protected function postAdd($params, $post);
    abstract protected function getThisModel();
}

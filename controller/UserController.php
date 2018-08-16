<?php
class UserController extends BaseController
{
    protected $modelName = 'User';
    protected $template = 'userRegister.twig';
    /**
     * Форма входа пользователя
     */
    public function getAdd()
    {
        $params = [
            'login_errors' => $this->getThisModel()->getLoginErrors()
        ];
        $this->render($this->template, $params);
    }
    /**
     * Выполняет авторизацию или регистрацию
     * @param $params
     * @param $post
     */
    public function postAdd($params, $post)
    {
        if (isPost()) {
            if ((getParam('sign_in') && $this->getThisModel()->checkForLogin(getParam('login'), getParam('password'))) OR
                (getParam('register') && $this->getThisModel()->register(getParam('login'), getParam('password')))) {
                redirect('index');
            } else {
                $this->getAdd();
            }
        }
    }
    /**
     * Возвращает текущую модель
     * @return User
     */
    protected function getThisModel()
    {
        return $this->model;
    }
    /**
     * Выход из пользователя
     */
    public function getLogout()
    {
        $this->getThisModel()->logout();
    }
}

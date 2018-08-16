<?php
class User extends BaseModel
{
    protected $user;
    function __construct($db)
    {
        $this->db = $db;
        if (!empty($_SESSION['user']['login'])) {
            $this->user = $this->getUser($_SESSION['user']['login']);
        }
    }
    /**
     * Ищет пользователя по логину
     * @param $login
     * @return mixed|null
     */
    protected function getUser($login)
    {
        $sql = "SELECT * FROM user WHERE login = ? LIMIT 1";
        $statement = $this->getDB()->prepare($sql);
        $statement->execute([$login]);
        return $statement->fetch(PDO::FETCH_ASSOC);
    }
    /**
     * Добавляет пользователя в БД (если пользователя с таким именем в базе нет)
     * @param $login
     * @param $password
     * @return bool
     */
    function setUser($login, $password)
    {
        if ($this->getUser($login)) {
            return false;
        }
        $sqlAdd = "INSERT INTO user (login, password) VALUES (?, ?)";
        $statement = $this->getDB()->prepare($sqlAdd);
        $statement->execute([$login, $password]);
        return true;
    }
    /**
     * Реализует механизм регистрации и последующей авторизации
     * @param $login
     * @param $password
     * @return bool
     */
    public function register($login, $password)
    {
        $_SESSION['loginErrors'] = [];
        if (!$this->setUser($login, $this->getHash($password))) {
            $_SESSION['loginErrors'][] = 'Регистрация не удалась: такой пользователь уже есть';
            return false;
        }
        return $this->checkForLogin($login, $password);
    }
    /**
     * Возвращает хеш md5 от полученного параметра
     * @param $password
     * @return string
     */
    function getHash($password)
    {
        return md5($password);
    }
    /**
     * Реализует механизм проверок при авторизации
     * @param $login
     * @param $password
     * @return bool
     */
    public function checkForLogin($login, $password)
    {
        $_SESSION['loginErrors'] = [];
        if (!$this->login($login, $password)) {
            $_SESSION['loginErrors'][] =
                'Авторизация не удалась: не найден пользователь, неправильный логин или неправильный пароль';
            return false;
        }
        return true;
    }
    /**
     * Реализует механизм авторизации
     * @param $login
     * @param $password
     * @return bool
     */
    protected function login($login, $password)
    {
        $user = !empty($login) && !empty($password) ? $this->getUser($login) : null;
        /* Ищем пользователя по логину */
        if ($user !== null && $user['password'] === $this->getHash($password)) {
            $_SESSION['user'] = $user;
            $this->user = $user;
            $_SESSION['user_id'] = $this->user['id']; // Создаем ID в сессиии
            return true;
        }
        return false;
    }
    /**
     * Уничтожает сессию и переадресует на страницу входа
     */
    public function logout()
    {
        session_destroy();
        redirect('index');
    }
    /**
     * Возвращает список ошибок, произошедших во время входа
     * @return mixed
     */
    public function getLoginErrors()
    {
        return !empty($_SESSION['loginErrors']) ? $_SESSION['loginErrors'] : '';
    }
}
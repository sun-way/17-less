<?php
class Task extends BaseModel
{
    const TASK_STATE_COMPLETE = 2;
    const TASK_STATE_IN_PROGRESS = 1;
    protected $user;
    function __construct($db)
    {
        $this->db = $db;
        $this->user = $_SESSION['user'];
    }
    /**
     * Возвращает массив задач созданных пользователем (когда $owner === true)
     * или назначенных пользователю (когда $owner === false)
     * @param $owner bool
     * @return array
     */
    public function getTasks($owner = true)
    {
        if ($owner === true) {
            $where = ' WHERE owner_user.login = :login ';
        } else {
            $where = ' WHERE owner_user.login <> :login AND assigned_user.login = :login ';
        }
        $sort = $this->getSortType();
        $sth = $this->getDB()->prepare('
            SELECT task.id, task.user_id, task.assigned_user_id, task.description, task.is_done, task.date_added, 
              owner_user.login AS owner_user_login, assigned_user.login AS assigned_user_login
            FROM task
            JOIN user AS owner_user ON owner_user.id=task.user_id
            JOIN user AS assigned_user ON assigned_user.id=task.assigned_user_id'
            . $where .
            "ORDER BY $sort ASC;");
        $sth->bindValue(':login', $this->getUserName(), PDO::PARAM_STR);
        if ($owner === false) {
            // во втором случае два параметра :login
            $sth->bindValue(':login', $this->getUserName(), PDO::PARAM_STR);
        }
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * Извлекает тип сортировки
     * @return string
     */
    public function getSortType()
    {
        return !empty($_SESSION['sort']) ? $_SESSION['sort'] : 'date_added';
    }
    /**
     * Возвращает имя пользователя (логин)
     * @return null
     */
    public function getUserName()
    {
        return $this->getCurrentUser('login');
    }
    /**
     * Возвращает текущего пользователя (если есть) или его параметр при наличии $param
     * @param null $param
     * @return null
     */
    public function getCurrentUser($param = null)
    {
        if (isset($param)) {
            return isset($this->user[$param]) ? $this->user[$param] : null;
        }
        return isset($this->user) ? $this->user : null;
    }
    /**
     * Задает тип сортировки
     * @param $sort
     * @return bool
     */
    public function setSortType($sort)
    {
        $_SESSION['sort'] = in_array($sort, ['date_added', 'is_done', 'description']) ? $sort : 'date_added';
        return true;
    }
    /**
     * Возвращает список пользователей из БД
     */
    public function getUserList()
    {
        $sql = "SELECT id, login FROM user ORDER BY login;";
        $statement = $this->getDB()->prepare($sql);
        $statement->execute([]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * Создает / изменяет задачу по ID
     * @param $taskID
     * @param $action
     * @param null $taskDescription
     * @param null $assignedUserID
     * @return bool
     */
    public function changeTask($taskID, $action, $taskDescription = null, $assignedUserID = null)
    {
        $pdoParameters = [];
        switch ($action) {
            case 'add':
                $userID = $this->getCurrentUser('id');
                $sql = "INSERT INTO task (description, is_done, date_added, user_id, assigned_user_id) 
                VALUES (?, ?,  NOW(), ?, ?);";
                $pdoParameters = [$taskDescription, self::TASK_STATE_IN_PROGRESS, $userID, $userID];
                break;
            case 'edit':
                if (!empty($taskDescription)) {
                    $sql = "UPDATE task SET description = ? WHERE id = ?";
                    $pdoParameters = [$taskDescription, $taskID];
                }
                break;
            case 'done':
                $sql = "UPDATE task SET is_done = ? WHERE id = ?";
                $pdoParameters = [self::TASK_STATE_COMPLETE, $taskID];
                break;
            case 'delete':
                $sql = "DELETE FROM task WHERE id = ?";
                $pdoParameters = [$taskID];
                break;
            case 'set_assigned_user':
                $sql = "UPDATE task SET assigned_user_id = ? WHERE id = ?";
                $pdoParameters = [$assignedUserID, $taskID];
                break;
        }
        if (!empty($sql)) {
            $statement = $this->getDB()->prepare($sql);
            return $statement->execute($pdoParameters);
        }
        return false;
    }
    /**
     * Возвращает название статуса задачи
     * @param $id
     * @return string
     */
    public function getStatusName($id)
    {
        switch ($id) {
            case self::TASK_STATE_IN_PROGRESS:
                return 'В процессе';
                break;
            case self::TASK_STATE_COMPLETE:
                return 'Завершено';
                break;
            default:
                return '';
                break;
        }
    }
    /**
     * Возвращает цвет для выделения статуса задачи
     * @param $id
     * @return string
     */
    public function getStatusColor($id)
    {
        switch ($id) {
            case self::TASK_STATE_IN_PROGRESS:
                return 'orange';
                break;
            case self::TASK_STATE_COMPLETE:
                return 'green';
                break;
            default:
                return 'red';
                break;
        }
    }
    /**
     * Возвращает строку вида user_1-task_10 для генерации названия вариантов селектора
     * или массив из ID пользователя и ID задачи (если на входе передали строку вида user_1-task_10)
     * @param $user_id
     * @param $task_id
     * @param null $nameOption
     * @return array|string
     */
    public function getNameOptionList($user_id, $task_id, $nameOption = null)
    {
        if (!empty($nameOption)) {
            // для случая когда нужно разобрать строку на
            $str = explode('-', $nameOption);
            $assigned_user_id = (int)str_replace('user_', '', $str[0]);
            $task_id = (int)str_replace('task_', '', $str[1]);
            return [
                'assigned_user_id' => $assigned_user_id,
                'task_id' => $task_id
            ];
        }
        return !empty($user_id) && !empty($task_id) ? 'user_' . $user_id . '-task_' . $task_id : '';
    }
    /**
     * Извлекает из БД описание задачи по $taskID
     * @param $taskID
     * @return string
     */
    public function getDescriptionForTask($taskID)
    {
        if (empty($taskID)) return '';
        $statement = $this->getDB()->prepare("SELECT description FROM task WHERE id = ?");
        $statement->execute([$taskID]);
        return $statement->fetch(PDO::FETCH_ASSOC)['description'];
    }
}

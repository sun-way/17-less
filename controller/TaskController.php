<?php
class TaskController extends BaseController
{
    protected $modelName = 'Task';
    protected $template = 'tasks.twig';
    /**
     * Форма вывода списка задач
     */
    public function getAdd()
    {
        $this->getList();
    }
    /**
     * Вывод всех задач (режим GET)
     * @param string $action
     * @param string $taskID
     */
    public function getList($action = 'list', $taskID = '')
    {
        $params = [
            'user' => $this->getThisModel()->getUserName(),
            'action' => $action,
            'name_edit_task' => $this->getThisModel()->getDescriptionForTask((int)$taskID),
            'sort_by' => $this->getThisModel()->getSortType(),
            'owner_tasks' => $this->getThisModel()->getTasks(),
            'user_list' => $this->getThisModel()->getUserList(),
            'other_tasks' => $this->getThisModel()->getTasks(false)
        ];
        foreach ($params['owner_tasks'] as $i => $task) {
            $params['owner_tasks'][$i]['state_color'] = $this->getThisModel()->getStatusColor($task['is_done']);
            $params['owner_tasks'][$i]['state_name'] = $this->getThisModel()->getStatusName($task['is_done']);
        }
        foreach ($params['other_tasks'] as $i => $task) {
            $params['other_tasks'][$i]['state_color'] = $this->getThisModel()->getStatusColor($task['is_done']);
            $params['other_tasks'][$i]['state_name'] = $this->getThisModel()->getStatusName($task['is_done']);
        }
        $this->render($this->template, $params);
    }
    /**
     * @return Task
     */
    protected function getThisModel()
    {
        return $this->model;
    }
    /**
     * Добавление задачи
     * @param $params
     * @param $post
     */
    public function postAdd($params, $post)
    {
        if (!empty($post['description'])) {
            $idAdd = $this->getThisModel()->changeTask(null, 'add', $post['description']);
            if ($idAdd) {
                redirect('index');
            }
        }
    }
    /**
     * Удаление задачи
     * @param $params
     */
    public function getDelete($params)
    {
        if (isset($params['id']) && is_numeric($params['id'])) {
            $isDelete = $this->getThisModel()->changeTask($params['id'], 'delete');
            if ($isDelete) {
                redirect('index');
            }
        }
    }
    /**
     * Переключение статуса задачи на выполнено
     * @param $params
     */
    public function getDone($params)
    {
        if (isset($params['id']) && is_numeric($params['id'])) {
            $isDone = $this->getThisModel()->changeTask($params['id'], 'done');
            if ($isDone) {
                redirect('index');
            }
        }
    }
    /**
     * Вывод формы изменения имени задачи
     * @param $params
     */
    public function getEdit($params)
    {
        if (isset($params['id']) && is_numeric($params['id'])) {
            $this->getList('edit', $params['id']);
        }
    }
    /**
     * Изменение имени задачи
     * @param $params
     * @param $post
     */
    public function postEdit($params, $post)
    {
        if (isset($params['id']) && is_numeric($params['id'])) {
            $isEdited = false;
            if (!empty($post['description'])) {
                $isEdited = $this->getThisModel()->changeTask($params['id'], 'edit', $post['description']);
            }
            if ($isEdited) {
                redirect('index');
            }
        }
    }
    /**
     * Назначение задачи другому пользователю (при нажатии Переложить ответственность)
     * @param $params
     * @param $post
     */
    public function postAssign($params, $post)
    {
        if (!empty($post['assigned_user_id'])) {
            /* формат assigned_user_id - user_x-task_y */
            $user_task = $this->getThisModel()->getNameOptionList(null, null, $post['assigned_user_id']);
            $idAssign = $this->getThisModel()->changeTask(
                $user_task['task_id'],
                'set_assigned_user',
                null,
                $user_task['assigned_user_id']
            );
            if ($idAssign) {
                redirect('index');
            }
        }
    }
    /**
     * Установка типа сортировки задач
     * @param $params
     * @param $post
     */
    public function postSort($params, $post)
    {
        if (!empty($post['sort_by'])) {
            $idAdd = $this->getThisModel()->setSortType($post['sort_by']);
            if ($idAdd) {
                redirect('index');
            }
        }
    }
    /**
     * Вывод всех задач (режим POST)
     */
    public function postList()
    {
        $this->getList();
    }
}
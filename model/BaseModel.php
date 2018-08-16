<?php
abstract class BaseModel
{
    protected $db = null;
    /**
     * Возвращает подключение в БД
     * @return PDO
     */
    protected function getDB()
    {
        return $this->db;
    }
}
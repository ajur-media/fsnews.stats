<?php

namespace AJUR\FSNews\NViews;

class Logger
{
    private $pdo;
    private $table;
    private $extra_fields;

    public string $last_sql_query = '';
    public array $last_sql_conditions = [];

    /**
     * @param $pdo_connection
     * @param string $table
     * @param array $extra_fields
     */
    public function __construct($pdo_connection, string $table = '', array $extra_fields = [])
    {
        $this->pdo = $pdo_connection;
        $this->table = $table;
        $this->extra_fields = $extra_fields;

        if (is_null($pdo_connection)) {
            throw new \RuntimeException(__CLASS__ . '->' . __METHOD__ . " can't use NULL PDO Connection");
        }

        if (empty($table)) {
            throw new \RuntimeException(__CLASS__ . '->' . __METHOD__ . " can't use empty table");
        }
    }

    /**
     * @param $item_id
     * @param string $date
     * @param array $extra_fields
     * @return mixed
     */
    public function addEvent($item_id, string $date = 'NOW()', array $extra_fields = [])
    {
        if (empty($item_id)) {
            throw new \RuntimeException(__CLASS__ . '->' . __METHOD__ . " can't store data for empty item_id");
        }
        if ($date != 'NOW()') {
            if (false === \strtotime($date)) {
                throw new \RuntimeException(__CLASS__ . '->' . __METHOD__ . " incorrect date: {$date}");
            }
        }

        //@todo: добавить обработку extra_fields из конструктора и из этого метода

        $set = [
            "item_id = :item_id",
            "event_count = 1",
            "event_date = {$date}"
        ];
        $set = \array_merge($set, $this->extra_fields);

        $this->last_sql_query
            = " INSERT INTO {$this->table} SET "
            . implode(', ', $set)
            . " ON DUPLICATE KEY UPDATE "
            . " event_count = event_count + 1 ;";

        $this->last_sql_conditions = [
            'item_id'   =>  $item_id,
        ];

        $sth = $this->pdo->prepare($this->last_sql_query);
        return $sth->execute($this->last_sql_conditions);
    }

    /**
     * Получает данные из таблицы событий по ID, с сортировкой по умолчанию event_date DESC
     * Возможно, с доп.условиями отбора (помним про индексы!)
     *
     * @param int $item_id
     * @param string $order_by = 'event_date DESC'
     * @param array $extra_conditions = []
     * @param string|int $limit = 0
     * @return array
     *
     * Extra conditions ПОКА задаются так:
     * [ 'is_external' => ["=", 1] , ... ]
     */
    public function getEvents(int $item_id, string $order_by = 'event_date DESC', array $extra_conditions = [], $limit = 0):array
    {
        $this->last_sql_conditions = [
            'item_id'   =>  $item_id
        ];
        $where = [
            'item_id'   =>  'item_id = :item_id'
        ];

        if (!empty($extra_conditions)) {
            foreach ($extra_conditions as $key => $value) {
                $where[ $key ] = "{$key} {$value[0]} :{$key}";
                $this->last_sql_conditions [ $key ] = $value[1];
            }
        }
        $this->last_sql_query = " SELECT * FROM {$this->table} ";
        $this->last_sql_query.= " WHERE " . \implode(" AND ", $where);

        if (!empty($order_by)) {
            $this->last_sql_query.= " ORDER BY {$order_by}";
        }

        $limit = (int)$limit;

        if ($limit > 0) {
            $this->last_sql_query .= " LIMIT {$limit}";
        }

        $this->last_sql_query .= " ;";

        $sth = $this->pdo->prepare($this->last_sql_query);
        $sth->execute( $this->last_sql_conditions );
        $data = $sth->fetchAll();

        return !empty($data) ? $data : [];
    }

    /**
     * @return array[
     * 'query'  => string,
     * 'conditions' => array
     * ]
     */
    public function debug():array
    {
        return [
            'query'     =>  $this->last_sql_query,
            'conditions'=>  $this->last_sql_conditions
        ];
    }

}
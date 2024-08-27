<?php

namespace AJUR\FSNews;

use AJUR\FSNews\NViews\Logger;
use RuntimeException;
use PDO;

/**
 *
 */
class NViews
{
    /**
     * @var array<Logger>
     */
    private static $streams = [];

    /**
     * @var
     */
    private static $default_pdo_connection;

    /**
     * @var string
     */
    private static string $default_table;

    private static bool $allow_lazy_stream_creation = false;

    private static bool $is_enabled = true;

    /**
     * Инициализирует значения по-умолчанию для системы статистики
     *
     * @param $default_pdo_connection
     * @param string $default_table
     * @param bool $allow_lazy_stream_creation
     * @param bool $is_enabled (global is_enabled)
     * @return void
     */
    public static function init($default_pdo_connection = null, string $default_table = '', bool $allow_lazy_stream_creation = true, bool $is_enabled = true)
    {
        self::$default_pdo_connection = $default_pdo_connection;
        self::$default_table = $default_table;
        self::$allow_lazy_stream_creation = $allow_lazy_stream_creation;
        self::$is_enabled = $is_enabled;
    }

    /**
     * Создает "поток" логгирования в определенную таблицу и PDO-коннекшен.
     *
     * @param string $name - имя потока (по умолчанию 'default')
     * @param null $pdo_connection - БД
     * @param string $table
     * @param array $extra_fields
     * @param bool $is_enabled
     * @return void
     */
    public static function addStream(string $name = 'default', $pdo_connection = null, string $table = '', array $extra_fields = [], bool $is_enabled = true)
    {
        if (is_null($pdo_connection)) {
            $pdo_connection = self::$default_pdo_connection;
        }

        if (!in_array($name, self::$streams)) {
            self::$streams[ $name ] = new Logger($pdo_connection, $table, $extra_fields, $is_enabled);
        }
    }

    /**
     * Возвращает экземпляр класса потока логгирования, привязанный к имени
     *
     * @param string $name
     * @return Logger
     */
    public static function stream(string $name = 'default'): Logger
    {
        if (!array_key_exists($name, self::$streams)) {
            if (self::$allow_lazy_stream_creation) {
                self::addStream($name, self::$default_pdo_connection, self::$default_table, [], self::$is_enabled);
            } else{
                throw new RuntimeException(__CLASS__ . '->' . __METHOD__ . " reports: {$name} stream not created before and lazy creation disabled");
            }
        }

        return self::$streams[ $name ];
    }

    /**
     * prepare Exported data for Morris DataView library
     *
     * @param array $data
     * @return array[
     * 'export' => array,
     * 'total' => int
     * ]
     */
    public static function prepareDataForMorris(array $data):array
    {
        if (empty($data)) {
            return [];
        }

        $export = [];
        $visit_total = 0;
        foreach ($data as $row) {
            $export[] = [
                'date'  =>  \date('d.m.Y', \strtotime($row['event_date'])),
                'value' =>  $row['event_count']
            ];
            $visit_total += $row['event_count'];
        }

        return [
            $export, $visit_total
        ];
    }


}
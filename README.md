# FSNews Engine NViews collector

Библиотека работает с SQL-таблицей следующего (минимального) вида:

```sql
CREATE TABLE example (
    item_id int DEFAULT NULL,
    event_date date DEFAULT NULL,
    event_count int DEFAULT NULL,
    UNIQUE KEY uniq_id_eventdate (item_id, event_date)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='visit log';
```

Впрочем, уникальный ключ может быть сложнее и покрывать какие-то дополнительные поля (например, `is_external`). 
Это минимальная необходимая структура:

- id материала
- дата события
- количество событий на дату и материал


```php
use AJUR\FSNews\NViews;

require_once __DIR__ . '/vendor/autoload.php';

// bootstrapping

// connect to database
$pdo = new \Arris\Database\DBWrapper([
    'driver'    =>  'mysql',
    'hostname'  =>  'localhost',
    'username'  =>  'wombat',
    'password'  =>  'wombatsql',
    'database'  =>  '47news',
    'charset'   =>  'utf8',
    'charset_collate'   =>  'utf8_general_ci',
    'slow_query_threshold'  => 1
]);

NViews::init($pdo /* default values */); // connection may be null, then required at addStream()
NViews::addStream('unique', $pdo, 'stat_nviews_full'); // connection may be null, default from init() will be used


// somewhere below in the code

NViews::stream('unique')->addEvent(239418);

// anywhere 

$data = NViews::stream('unique')->getEvents(239418);
var_dump($data);


```
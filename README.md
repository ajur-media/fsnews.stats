# FSNews Engine NViews collector

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
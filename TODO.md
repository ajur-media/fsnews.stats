```php
    public function recordVisitInMemoryCache($item_id)
    {
        if (_env('COLLECT.VISITS.ENABLED', 1, 'int') == 0) {
            return false;
        }

        $sql_query = "
            INSERT INTO _stat_nviews_actual
            SET
                `item_id` = :item_id,
                `event_date` = NOW()
            ";
        $sth = $this->pdo->prepare($sql_query);
        return $sth->execute([
            'item_id'   =>  $item_id,
        ]);
    }
```

Подумать насчет метода регистрации вот такого события (просто вставка в таблицу
без ON DUPLICATE UPDATE)

addEventX - что вместо X? 
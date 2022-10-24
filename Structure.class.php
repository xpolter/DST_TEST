<?php

class Structure
{
    private \PDO $pdo;
    private string $tableName;

    public function __construct(\PDO $pdo, string $tableName)
    {
        $this->pdo = $pdo;
        $this->tableName = $tableName;
        // Create table
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS `' . $this->tableName . '` (
            `id` INT(10) NOT NULL AUTO_INCREMENT,
            `pid` INT(10) NULL DEFAULT NULL,
            `title` TEXT NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `'.$this->tableName.'_ip` (`pid`),
            CONSTRAINT `'.$this->tableName.'_ip` FOREIGN KEY (`pid`) REFERENCES `' . $this->tableName . '` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE);');
    }

    /**
     * Добавление элемента
     * 
     * Если $pid == null то корень
     *
     * @param integer|null $pid Идентификатор родителя
     * @param string $title Название
     * @return integer|false ID новой записи
     */
    public function create(int|null $pid, string $title): int
    {
        // Проверяем существование родителя
        if (!is_null($pid)) {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM `' . $this->tableName . '` WHERE `id` = ? LIMIT 1');
            $stmt->execute([$pid]);
            if (empty($stmt->fetchColumn())) {
                return false;
            }
        }

        $stmt = $this->pdo->prepare('INSERT INTO `' . $this->tableName . '` (`pid`, `title`) VALUES (:pid, :title);');

        $stmt->execute([
            ':pid' => $pid,
            ':title' => $title,
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * Получение данных
     *
     * @param integer|null $parent - Родительский элемент
     * @return array Результат запроса
     */
    public function read(int $parent = null): array
    {
        if(is_null($parent)){
            $data = $this->pdo->query('SELECT * FROM `' . $this->tableName . '`')->fetchAll(PDO::FETCH_ASSOC);
        }else{
            $data = $this->pdo->query('WITH RECURSIVE cte AS (
                SELECT  id,
                        pid,
                        title
                FROM    `' . $this->tableName . '`
                WHERE   id = ' . $parent . '
                UNION ALL
                SELECT  p.id,
                        p.pid,
                        p.title
                FROM `' . $this->tableName . '` p
                inner join cte
                on p.pid = cte.id
                )
                SELECT * FROM cte;')->fetchAll(PDO::FETCH_ASSOC);
        }

        $data = array_column($data, null, 'id');

        foreach ($data as $key => $val) {
            if ($val['pid']) {
                if (isset($data[$val['pid']])) {
                    $data[$val['pid']]['children'][] = &$data[$key];
                }
            }
        }

        foreach ($data as $key => $val) {
            if ($val['pid']) unset($data[$key]);
        }

        return $data;
    }

    /**
     * Обновление элемента по ID
     *
     * @param integer $id - ID записи
     * @param string $title - Новое название
     * @return boolean
     */
    public function update(int $id, string $title) : bool
    {
        $stmt = $this->pdo->prepare('UPDATE `' . $this->tableName . '` SET `title` = :title WHERE `id` = :id');
        return $stmt->execute([
            ':id' => $id,
            ':title' => $title
        ]);
    }

    /**
     * Удаление
     *
     * @param integer $id ID записи
     * @return boolean
     */
    public function delete(int $id) : bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM `' . $this->tableName . '` WHERE `id` = ?');
        return  $stmt->execute( [$id]);
    }
}

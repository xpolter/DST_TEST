<?php

require_once './Structure.class.php';

$pdo = new PDO('mysql:host=localhost;dbname=dst', 'root', 'root');

$tableName = 'company';

$Structure = new Structure($pdo, $tableName);

$pdo->query('TRUNCATE `' . $tableName . '`;');
$Structure->create(null, 'Завод');
$Structure->create(null, 'Рядом ещё завод');
$Structure->create(1, 'Производство');
$Structure->create(2, 'Производство на другом заводе');
$Structure->create(2, 'Бухгалтерия');
$Structure->create(1, 'Склад');
$Structure->create(3, 'Сборка');
$Structure->create(3, 'Заготовка');
$Structure->create(7, 'Движки');
$Structure->create(7, 'Рамы');
$Structure->create(10, 'Передняя ось');
$Structure->create(10, 'Задняя ось');

// Выборка всех данных
$response = $Structure->read();
/*
 - Завод
    - Производство
        - Сборка
            - Движки
            - Рамы
                - Передняя ось
                - Задняя ось
        - Заготовка
    - Склад
 - Рядом ещё завод
    - Производство на другом заводе
    - Бухгалтерия
*/

// Выборка по ID
$response = $Structure->read(2); // Только второй завод
/*
 - Рядом ещё завод
    - Производство на другом заводе
    - Бухгалтерия
*/

// Обновление
$response = $Structure->update(1, 'ДСТ-УРАЛ'); // Переименовали завод
/*
 - ДСТ-УРАЛ
    - Производство
        - Сборка
            - Движки
            - Рамы
                - Передняя ось
                - Задняя ось
        - Заготовка
    - Склад
 - Рядом ещё завод
    - Производство на другом заводе
    - Бухгалтерия
*/

// Удаление
$response = $Structure->delete(2); // Удалим второй завод
/*
 - ДСТ-УРАЛ
    - Производство
        - Сборка
            - Движки
            - Рамы
                - Передняя ось
                - Задняя ось
        - Заготовка
    - Склад
*/

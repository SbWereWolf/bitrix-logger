# Как автоматически заполнить свойство `number`
Перед установкой свойства "Номер на Схеме" необходимо выполнить
запрос
```$sql
DROP TABLE IF EXISTS `a_construct_number`;
CREATE TABLE `a_construct_number`
(
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (`id`)
);
```
Следующим шагом необходимо выполнить `scheme/renumber.php`

Для всех РК у которых не установлено свойство `number` 
это свойство будет установлено.
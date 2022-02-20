<?php

require_once __DIR__.'./Connection.php';
require_once __DIR__.'/../model/Interest.php';
require_once __DIR__.'/../model/Language.php';
require_once __DIR__.'/../model/Person.php';
require_once __DIR__.'/../model/User.php';

abstract class DAO
{
    private static function epicFail(string $msg): void
    {
        Connection::conn()->rollBack();

        throw new Exception(
            "$msg: ".print_r(Connection::conn()->errorInfo(), true)
        );
    }

    public static function login(string $username, string $password): ?User
    {
        ($stmt = Connection::conn()->prepare(
            "
                select
                    `user_id`
                from users
                where `username` = ?
                and `password` = ?;
            "
        ))->execute([$username, md5($password)]);

        if (!$id = $stmt->fetchColumn()) {
            return null;
        } else {
            $user = new User($username, $password);
            $user->id = $id;

            return $user;
        }
    }

    public static function getLanguages(): array
    {
        $ret = [];
        $res = Connection::conn()->query(
            "
                select
                    `language_id`,
                    `language`
                from languages;
            "
        );

        while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
            $language = new Language($arr['language_id'], $arr['language']);

            $ret[] = $language;
        }

        return $ret;
    }

    public static function getInterests(): array
    {
        $ret = [];
        $res = Connection::conn()->query(
            "
                select
                    `interest_id`,
                    `interest`
                from interests;
            "
        );

        while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
            $interest = new Interest($arr['interest_id'], $arr['interest']);

            $ret[] = $interest;
        }

        return $ret;
    }

    public static function getPersons(): array
    {
        $ret = [];
        $res = Connection::conn()->query(
            "
                select
                    `person_id`,
                    `first_name`,
                    `last_name`, 
                    `id_number`,
                    `cell_number`,
                    `email`
                from persons
                order by
                    `last_name`,
                    `first_name`;
            "
        );

        while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
            $ret[] = new Person(
                $arr['person_id'],
                $arr['first_name'],
                $arr['last_name'],
                $arr['id_number'],
                $arr['cell_number'],
                $arr['email']
            );
        }

        return $ret;
    }

    public static function getPersonInterests(int $person_id): array
    {
        $ret = [];

        ($stmt = Connection::conn()->prepare(
            "
                select
                    `i`.`interest_id` `interest_id`,
                    `interest`
                from
                    interests `i`
                inner join interest_person `ip`
                    on `ip`.`interest_id` = `i`.`interest_id`
                where `ip`.`person_id` = ?;
            "
        ))->execute([$person_id]);

        while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ret[] = new Interest($arr['interest_id'], $arr['interest']);
        }

        return $ret;
    }

    public static function getPersonLanguage(int $person_id): Language
    {
        ($stmt = Connection::conn()->prepare(
            "
                select
                    `l`.`language_id` `language_id`,
                    `language`
                from
                    languages `l`
                inner join language_person `lp`
                    on `lp`.`language_id` = `l`.`language_id`
                where `lp`.`person_id` = ?;
            "
        ))->execute([$person_id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new Exception("Person ($person_id) not linked to language");
        }

        return new Language($row['language_id'], $row['language']);
    }

    public static function findPersonBy(string $field, string $value): Person
    {
        ($stmt = Connection::conn()->prepare(
            "
                select
                    `person_id`,
                    `first_name`,
                    `last_name`,
                     `id_number`,
                     `cell_number`,
                     `email`
                from persons
                where `$field` = ?;
            "
        ))->execute([$value]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return new Person(
            $row['person_id'],
            $row['first_name'],
            $row['last_name'],
            $row['id_number'],
            $row['cell_number'],
            $row['email'],
        );
    }

    // also used for updating
    public static function createPerson(array $parms): Person
    {
        

        Connection::conn()->beginTransaction();

        $success = Connection::conn()->prepare(
            "
                insert into persons
                (`first_name`, `last_name`, `id_number`, `cell_number`, `email`)
                values
                (?, ?, ?, ?, ?)
                on duplicate key update
                    `first_name` = ?,
                    `last_name` = ?,
                    `id_number` = ?,
                    `cell_number` = ?,
                    `email` = ?;
            "
        )->execute(
            [
                $parms['first_name'], $parms['last_name'],
                $parms['id_number'], $parms['cell_number'],
                $parms['email'], $parms['first_name'],
                $parms['last_name'], $parms['id_number'],
                $parms['cell_number'], $parms['email'] 
            ]
        );

        if (!$success) {
            self::epicFail('Failed to create person');
        }

        $person_id = Connection::conn()->lastInsertId();

        // $person_id == false signifies an update.
        // If so, the record exists. Find it by unique, immutable `id_number`.
        if (!$person_id) {
            $person_id = self::findPersonBy(
                'id_number',
                $parms['id_number']
            )->id;
        }

        // for updates
        self::deletePersonInterests($person_id);
        
        $stmt = Connection::conn()->prepare(
            "
                insert into interest_person
                (`interest_id`, `person_id`)
                values
                (?, ?);
            "
        );

        foreach ($parms['interests'] as $interest_id) {
            $success = $stmt->execute([$interest_id, $person_id]);

            if (!$success) {
                self::epicFail('Failed to link interest');
            }
        }

        // for updates
        self::deletePersonLanguage($person_id);

        $success = Connection::conn()->prepare(
            "
                insert into language_person
                (`language_id`, `person_id`)
                values
                (?, ?);
            "
        )->execute([$parms['language'], $person_id]);

        if (!$success) {
            self::epicFail('Failed to link language');
        }

        Connection::conn()->commit();

        return self::findPersonBy('person_id', $person_id);
    }

    public static function deletePersonLanguage(int $person_id): void
    {
        $success = Connection::conn()->prepare(
            "
                delete from language_person
                where `person_id` = ?;
            "
        )->execute([$person_id]);

        if (!$success) {
            self::epicFail("Failed to delete person language");
        }
    }

    public static function deletePersonInterests(int $person_id): void
    {
        $success = Connection::conn()->prepare(
            "
                delete from interest_person
                where `person_id` = ?;
            "
        )->execute([$person_id]);

        if (!$success) {
            self::epicFail("Failed to delete person interests");
        }
    }

    public static function deletePerson(int $person_id): void
    {
        Connection::conn()->beginTransaction();

        self::deletePersonInterests($person_id);
        self::deletePersonLanguage($person_id);

        $success = Connection::conn()->prepare(
            "
                delete from persons
                where person_id = ?;
            "
        )->execute([$person_id]);

        if (!$success) {
            self::epicFail("Failed to delete person");
        }

        Connection::conn()->commit();
    }
}
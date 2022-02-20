<?php

require_once __DIR__.'/Interest.php';
require_once __DIR__.'/Language.php';
require_once __DIR__.'/../db/DAO.php';

class Person
{
    public function __construct(
        int $id,
        string $firstName,
        string $lastName,
        string $idNumber,
        string $cellNumber,
        string $email
    ) {
        $this->id = $id; 
        $this->firstName = $firstName; 
        $this->lastName = $lastName;
        $this->idNumber = $idNumber;
        $this->cellNumber = $cellNumber;
        $this->email = $email;
        $this->interests = DAO::getPersonInterests($id);
        $this->language = DAO::getPersonLanguage($id);
    }
}
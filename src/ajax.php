<?php

require_once __DIR__.'/settings/Configuration.php';
require_once __DIR__.'/db/DAO.php';
require __DIR__.'/email/Email.php';

if (!isset($_POST['fn']) || !function_exists($_POST['fn'])) {
    echo json_encode([Configuration::get('JSON_ERROR') => 'Invalid call.']);
    
    return;
}

session_start();

$_POST['fn']();

function getParms(array $expected): ?array
{
    $parmMap = [];
    $values = [];

    try {
        $values = array_map(
            function ($parm) {
                $parms = Configuration::get('JSON_PARMS');

                if (!isset($_POST[$parms][$parm])
                    || $_POST[$parms][$parm] == ""
                ) {
                    throw new Exception("`$parm` not specified");
                }

                return $_POST[$parms][$parm];
            }, $expected    
        );
    } catch (Exception $ex) {
        echo json_encode([Configuration::get('JSON_ERROR'), $ex->getMessage()]);

        return null;
    }

    for ($i=0; $i<count($expected); $i++) {
        $parmMap[$expected[$i]] = $values[$i];
    }

    return $parmMap;
}

function login(): void
{
    if ($parms = getParms(['username', 'password'])) {
        if ($user = DAO::login($parms['username'], $parms['password'])) {
            $_SESSION[Configuration::get('VAR_USER_SESSION')] = $user;

            echo json_encode([Configuration::get('JSON_MESSAGE') => 'success']);
        } else {
            echo json_encode([Configuration::get('JSON_MESSAGE') => 'failure']);
        }
    }
}

function getLanguages(): void
{
    echo json_encode([Configuration::get('JSON_DATA') => DAO::getLanguages()]);
}

function getInterests(): void
{
    echo json_encode([Configuration::get('JSON_DATA') => DAO::getInterests()]);
}

function getPersons(): void
{
    echo json_encode([Configuration::get('JSON_DATA') => DAO::getPersons()]);
}

function getPerson(): void
{
    if ($parms = getParms(['id'])) {
        $person = DAO::findPersonBy('person_id', $parms['id']);

        echo json_encode(
            [
                Configuration::get('JSON_DATA') => $person
            ]
        );
    }
}

function createPerson(): void
{
    $expected = [
        'first_name', 'last_name', 'id_number',
        'cell_number', 'language', 'interests',
        'email'
    ];

    if ($parms = getParms($expected)) {
        try {
            $person = DAO::createPerson($parms);

            echo json_encode([Configuration::get('JSON_DATA') => $person]);
        } catch (Exception $ex) {
            echo json_encode(
                [Configuration::get('JSON_ERROR') => $ex->getMessage()]
            );
        }
    }
}

function updatePerson(): void
{
    createPerson();
}

function deletePerson(): void
{
    if ($parms = getParms(['id'])) {
        try {
            DAO::deletePerson($parms['id']);

            echo json_encode(
                [
                    Configuration::get('JSON_MESSAGE') => "Deleted"
                ]
            );
        } catch (Exception $ex) {
            echo json_encode(
                [
                    Configuration::get('JSON_ERROR') =>
                         "Deletion failed: ".$ex->getMessage()
                ]
            );
        }
    }
}
function sendMail(): void
{
    if ($parms = getParms(['person_id'])) {
        $msg = null;

        if (Email::sendMail($parms['person_id'])) {
            $msg = [Configuration::get('JSON_MESSAGE') => "Mail sent"];
        } else {
            $msg = [Configuration::get('JSON_ERROR') => "Mail sent failed"];
        }

        echo json_encode($msg);
    }
}
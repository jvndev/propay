<?php

require_once __DIR__.'./../settings/Configuration.php';
require_once __DIR__.'./../db/DAO.php';
require __DIR__.'/PHPMailer/src/Exception.php';
require __DIR__.'/PHPMailer/src/PHPMailer.php';
require __DIR__.'/PHPMailer/src/SMTP.php';

abstract class Email
{
    private static function createBody(Person $person): String
    {
        $interests = array_reduce(
            $person->interests,
            function ($p, $c) {
                return $p.$c->interest.", ";
            }, ""
        );
        $interests = substr($interests, 0, strlen($interests)-2);

        return "
            <html>
                <body>
                    You have been registered.<br />
                    <table>
                        <tr>
                            <td><b>Name & Surname:&nbsp;</b></td>
                            <td>{$person->firstName} {$person->lastName}</td>
                        </tr>
                        <tr>
                            <td><b>ID Number:&nbsp;</b></td>
                            <td>{$person->idNumber}</td>
                        </tr>
                        <tr>
                            <td><b>Cell Number:&nbsp;</b></td>
                            <td>{$person->cellNumber}</td>
                        </tr>
                        <tr>
                            <td><b>Language:&nbsp;</b></td>
                            <td>{$person->language->language}</td>
                        </tr>
                        <tr>
                            <td><b>Interests:&nbsp;</b></td>
                            <td>$interests</td>
                        </tr>
                    </table>
                </body>
            </html>
        ";
    }

    public static function sendMail(int $person_id): bool
    {
        $person = DAO::findPersonBy('person_id', $person_id);

        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = Configuration::get('SMTP_CONNECTION');
        $mail->Host = Configuration::get('SMTP_HOST');
        $mail->Port = Configuration::get('SMTP_PORT');
        $mail->isHTML();
        $mail->Username = Configuration::get('SMTP_USER');
        $mail->Password = Configuration::get('SMTP_PASSWORD');
        $mail->SetFrom(
            Configuration::get('SMTP_FROM_ADDRESS'),
            Configuration::get('SMTP_FROM_NAME')
        );
        $mail->Subject = Configuration::get('SMTP_SUBJECT');
        $mail->Body = self::createBody($person);

        $mail->AddAddress($person->email);

        return $mail->Send();
    }
}
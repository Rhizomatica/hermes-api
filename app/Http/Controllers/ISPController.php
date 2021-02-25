<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;


class ISPController extends Controller
{

    function createEmail(Request $request)
    {
        /*if ($request['password']){
            $request['hash'] = hash('sha256', $request['password']);
        }*/

        $username = env('HERMES_EMAILAPI_USER');
        $password = env('HERMES_EMAILAPI_PASS');
        $soap_location = env('HERMES_EMAILAPI_LOC');
        $soap_uri = env('HERMES_EMAILAPI_URI');

        $client = new \SoapClient(null, array('location' => $soap_location,
                'uri'      => $soap_uri,
                'trace' => 1,
                'stream_context'=> stream_context_create(array('ssl'=> array('verify_peer'=>false,'verify_peer_name'=>false))),
                'exceptions' => 1));
        try {
            if($session_id = $client->login($username, $password)) {
                    echo 'Logged successfull. Session ID:'.$session_id.'<br />';
            }
            //, 'phone', 'site', 'location', 'password', 'recoverphrase', 'recoveranswer', 'updated_at', 'created_at', 'admin'
            //* Set the function parameters.
            $client_id = 1;
            $params = array(
                    'server_id' => 1,
                    'email' =>  $request['email'] . '@' . env('HERMES_DOMAIN'),
                    'login' => $request['email'],
                    'password' => $request['password'],
                    'name' => $request['name'],
                    'uid' => 5000,
                    'gid' => 5000,
                    'maildir' => '/var/vmail/' . $request['email'],
                    'quota' => 5242880,
                    'cc' => '',
                    'homedir' => '/var/vmail',
                    'autoresponder' => 'n',
                    'autoresponder_start_date' => '',
                    'autoresponder_end_date' => '',
                    'autoresponder_text' => 'hallo',
                    'autoresponder_subject' => 'Out of office reply',
                    'move_junk' => 'n',
                    'custom_mailfilter' => 'spam',
                    'postfix' => 'n',
                    'access' => 'n',
                    'disableimap' => 'n',
                    'disablepop3' => 'n',
                    'disabledeliver' => 'n',
                    'disablesmtp' => 'n',
                    'dbispconfig' => 1,
                    'mail_user' => 0,
                    'purge_trash_days' => 100,
                    'purge_junk_days' => 100
            );

            $mailuser_id = $client->mail_user_add($session_id, $client_id, $params);

            echo "New user: ".$mailuser_id."<br>";

            if($client->logout($session_id)) {
                    echo 'Logged out.<br />';
            }
        }
        catch (SoapFault $e) {
            echo $client->__getLastResponse();
            die('SOAP Error: '.$e->getMessage());
        }
    }

    public function updateEmail(Request $request){
        $username = env('HERMES_EMAILAPI_USER');
        $password = env('HERMES_EMAILAPI_PASS');
        $soap_location = env('HERMES_EMAILAPI_LOC');
        $soap_uri = env('HERMES_EMAILAPI_URI');

        $client = new \SoapClient(null, array('location' => $soap_location,
                'uri'      => $soap_uri,
                'trace' => 1,
                'stream_context'=> stream_context_create(array('ssl'=> array('verify_peer'=>false,'verify_peer_name'=>false))),
                'exceptions' => 1));

        try {
            if($session_id = $client->login($username, $password)) {
                echo 'Logged successfull. Session ID:'.$session_id.'<br />';
            }
            //* Parameters
            $mailuser_id = 1;
            $client_id = 1;

            //* Get the email user record
            $mail_user_record = $client->mail_user_get($session_id, $mailuser_id);

            //* Change the status to inactive
            $mail_user_record['name'] = $request['name'];
            $mail_user_record['password'] = $request['password'];

            $affected_rows = $client->mail_user_update($session_id, $client_id, $mailuser_id, $mail_user_record);

            echo "Number of records that have been changed in the database: ".$affected_rows."<br>";

            if($client->logout($session_id)) {
                echo 'Logged out.<br />';
            }

        } catch (SoapFault $e) {
            echo $client->__getLastResponse();
            die('SOAP Error: '.$e->getMessage());
        }
    }

    public function deleteEmail(Request $request){
        try {
            if($session_id = $client->login($username, $password)) {
                echo 'Logged successfull. Session ID:'.$session_id.'<br />';
            }

            //* Parameters
            $mailuser_id = 1;

            $affected_rows = $client->mail_user_delete($session_id, $mailuser_id);

            echo "Number of records that have been deleted: ".$affected_rows."<br>";

            if($client->logout($session_id)) {
                echo 'Logged out.<br />';
            }


        } catch (SoapFault $e) {
            echo $client->__getLastResponse();
            die('SOAP Error: '.$e->getMessage());
        }
    }

}
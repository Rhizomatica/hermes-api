<?php

namespace App\Http\Controllers;

use App\User;

class ISPConfigController extends Controller
{

    public function updateForward($session_id, $client, $client_id, $email) //TODO - Pegar $ciente_id do $cliente
    {
        if (env('HERMES_EMAILAPI_FORWARDING_ID')) {
            $mail_forward = $client->mail_forward_get($session_id, env('HERMES_EMAILAPI_FORWARDING_ID'));
            $find =  strpos($mail_forward['destination'], $email);
            if ($find === false) {
                $mail_forward['destination'] .= ', ' .  $email . '@' . env('HERMES_DOMAIN');
                $client->mail_forward_update($session_id, $client_id, env('HERMES_EMAILAPI_FORWARDING_ID'), $mail_forward);
            }
        }
    }
    public function removeForward($session_id, $client, $mail): void
    {
        if (env('HERMES_EMAILAPI_FORWARDING_ID')) {
            $client_id = 1;
            $mail_forward = $client->mail_forward_get($session_id, env('HERMES_EMAILAPI_FORWARDING_ID'));
            $find =  strpos($mail_forward['destination'], $mail);

            if ($find !== false) {
                $destination = explode(', ', $mail_forward['destination']);
                $new_destination = [];
                //remove
                foreach ($destination as $key => $value) {
                    if ($value != $mail . "@" . env('HERMES_DOMAIN')) {
                        array_push($new_destination, $value);
                    }
                }

                $mail_forward['destination'] = '';

                for ($i = 0; $i < count($new_destination); $i++) {

                    if (count($new_destination) - 1 == $i) {
                        $mail_forward['destination'] .= $new_destination[$i];
                    } else {
                        $mail_forward['destination'] .= $new_destination[$i] . ', ';
                    }
                }

                $client->mail_forward_update($session_id, $client_id, env('HERMES_EMAILAPI_FORWARDING_ID'), $mail_forward);
            }
        }
    }
}

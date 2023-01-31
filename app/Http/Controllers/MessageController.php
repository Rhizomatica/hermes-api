<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     *  Get all messages by type
     *  parameter:
     *
     * @return Json Messages
     */
    public function showAllMessagesByType($type)
    {
		if($type=='inbox'){
			return response()->json(Message::where('inbox', '=', true)->orderBy('sent_at')->get());
		}
		if($type=='draft'){
			return response()->json(Message::where('draft', '=', true)->orderBy('sent_at')->get());
		}
		else{
			return response()->json(Message::where('inbox', '!=', true)->where('draft', '!=', true)->orderBy('sent_at')->get());
		}
    }

    /**
     * Get a message
     *  parameter: message id
     *
     * @return Json
     */
    public function showOneMessage($id)
    {
		return response()->json(Message::find($id));
    }

    /**
     * SendHMP - Send Hermes Message Pack
     * parameter: http request
     *
     * @return Json
     */
    public function sendHMP(Request $request)
    {
        $request->inbox=false;
		$request->orig = explode("\n", exec_cli("cat /etc/uucp/config|grep nodename|cut -f 2 -d \" \""))[0];

        if($message = Message::create($request->all())){
            if($request->pass && $request->pass!='' && $request->pass!='undefined'){
                $command = 'echo "'. $request->text . '"| gpg -o - -c -t --cipher-algo AES256 --utf8-strings --batch --passphrase "' . $request->pass. '"  --yes -';
                $cryptout = "";
                if ($output = exec_cli($command) ){
                    $cryptout = $output; // redundant
                }
                else {
        			return response()->json(['message' => 'sendHMP: can\'t encrypt the message: ' ], 500);
                }
                $message->secure=true;
                $message->text=bin2hex($cryptout);
                $message->save();
            }

            //log
			Log::info('creating message ' . $message);
            //find the message in database
            // Assures to delete the working path
            Storage::deleteDirectory('tmp/'.$message->id);

            // Write message file
            if (! Storage::disk('local')->put('tmp/' . $message->id . '/hmp.json'  , $message)){
        		return response()->json(['message' => 'sendHMP Error: can\'t write message file'], 500);
            }

            // Has file?
            if ($message->fileid && Storage::disk('local')->exists('uploads/'.$message->fileid)) {
				// TODO Mantain original files?
                if (! Storage::disk('local')->copy('uploads/' . $message->fileid, 'tmp/' . $message->id . '/' .$message->fileid )){
        			return response()->json(['message' => 'Hermes send message Error: can\'t move file'], 500);
                }
            }

            $pathtmp = Storage::disk('local')->path('tmp');
            $command  = 'tar cfz ' . $pathtmp . '/' . $message->id . '.hmp -C '.  $pathtmp . ' ' . $message->id  ;
            if ($output = exec_cli($command) ){
        		return response()->json(['message' => 'Hermes send message Error: cant move image file' . $output . $command], 500);
            }
			$origpath = 'tmp/' . $message->id . '.hmp';


			// check file size
			$hmpsize = Storage::disk('local')->size($origpath);
			if ( $hmpsize > env('HERMES_MAX_FILE')){
				$path = Storage::disk('local')->delete($origpath);
        		return response()->json(['message' => 'HMP error: larger than ' . env('HERMES_MAX_FILE')], 500);
			}

			// set new origpath on outbox
			$origpath = env('HERMES_OUTBOX') . '/' . $message->id . '.hmp';
            $path = Storage::disk('local')->path($origpath);

            //work path
			if (!env('HERMES_OUTBOX')){
        		return response()->json(['message' => 'Hermes pack message Error: cant package the file' . $path], 500);
			}

            // Clean outbox destination and move the package
            if (! Storage::disk('local')->move('tmp/'.$message->id.'.hmp', $origpath)){
        		return response()->json(['message' => 'Hermes pack message Error: cant package the file' . $path], 500);
            }
            //$message = @json_decode(json_encode($messagefile), true);
            Storage::disk('local')->deleteDirectory('tmp/'.$message->id);


            // UUCP -C Copy  (default) / -d create dirs
            if (Storage::disk('local')->exists($origpath)) {
				//send message by uucp
        		foreach ($message->dest as $dest){
					//check spool size
					$command = "uustat -s " . $dest . " -u www-data  | egrep -o '(\w+)\sbytes' | awk -F ' ' '{sum+=$1; } END {print sum}'";
					$destspoolsize = exec_cli($command);
					$destspoolsize = $hmpsize + intval($destspoolsize);
					if ($destspoolsize > env('HERMES_MAX_SPOOL')){
						$path = Storage::disk('local')->delete($origpath);
						return response()->json(['message' => 'HMP error: spool larger than ' . env('HERMES_MAX_SPOOL') .' bytes ' ], 500);
					}

                	$command = 'uucp -r -j -C -d \'' .  $path . '\' \'' . $dest . '!~/' . $message->orig . '_' . $message->id . '.hmp\'';
                	if(!$output = exec_cli_no($command)){
							return response()->json(['message' => 'Hermes sendMessage - Error on uucp:  ' . $output . ' - ' .$command], 500);
					}
				}
				//setting no draft
				if (! $message->update([ 'draft' => false])){
					return response()->json(['message' => 'Hermes sendMessage - cant update no draft:  ' . $output ], 500);
				}
				//delete hmp file
				if ( Storage::disk('local')->delete($origpath));

				Log::info('sent message ' . $message->id);
            }
            else{
        		return response()->json(['message' => 'Hermes send message Error: Cant find '.$path], 500);
            }
        }
        else{
        	return response()->json(['message' => 'Hermes pack message Error: can\'t create message in DB'], 500);
        }

        return response()->json(['message' => 'Hermes sendMessage: DONE', 'content' => $message], 200);
    }

    /**
     * deleteMessage - deleteMessage
     * parameter: message id
     * @return Json
     */
    public function deleteMessage($id)
    {
		$message = Message::findOrFail($id);
        Message::findOrFail($id)->delete();
        if ( $message->fileid){
			if ($message->inbox){
				Storage::disk('local')->delete('downloads/' . $message->fileid);
			}
			else{
				Storage::disk('local')->delete('uploads/' . $message->fileid);

			}
		}
		Log::info('delete message ' . $id);
        return response()->json(['message' => 'Delete sucessfully message: ' . $id], 200);
    }

    /**
     * unCrypt text message
     * parameter: message id, $request->pass
     * @return Json
     */
    public function unCrypt($id, Request $request){
		if ($request->pass && $request->pass != '') {
        	if ($message = Message::find($id)){
				if ($message['secure'] ){
                	$crypt = hex2bin($message['text']);
            		if ( Storage::disk('local')->put('tmp/' . $message->id . '-uncrypt'  , $crypt)){
						$path = Storage::disk('local')->path('tmp') . '/' . $message->id . '-uncrypt';
						$command  = 'gpg -d --batch --passphrase "' .  $request->pass . '" --decrypt ' . $path  ;
						$output = exec_cli($command);

						Log::info('message unCrypt  '. $id  . ' - ' . $output );
						return response()->json(['text' => $output], 200);
					}
				}
				else{
					Log::warning('message unCrypt message is not secure '. $id  . ' - ' . $output );
        			return response()->json(['message' => 'HMP uncrypt error: message is not secured'], 500);
				}

			}
			else{
       			return response()->json(['message' => 'HMP uncrypt error: cant find message'], 500);
			}
		}
		else{
       			return response()->json(['message' => 'HMP uncrypt error: form pass is required'], 500);
		}
	}
}

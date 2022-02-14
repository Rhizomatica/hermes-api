<?php

namespace App\Http\Controllers;

use Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use App\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Get all messages
     *  parameter: messages 
     *
     * @return Json
     */
    public function showAllMessages()
    {
        return response()->json(Message::all());
    }

    /**
     *  Get all messages by type
     *  parameter: 
     *
     * @return Json Messages
     */
    public function showAllMessagesByType($type)
    {
		if($type=='inbox'){
			return response()->json(Message::where('inbox', '=', true)->get());
		}
		if($type=='draft'){
			return response()->json(Message::where('draft', '=', true)->get());
		}
		else{
			return response()->json(Message::where('inbox', '!=', true)->where('draft', '!=', true)->get());
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
			if (Storage::disk('local')->size($origpath) > env('HERMES_MAX_FILE')){
				$path = Storage::disk('local')->delete($origpath);
        		return response()->json(['message' => 'HMP error: larger than ' . env('HERMES_MAX_FILE')], 500);
			}

			//check spool size 
			$command = "uustat -a | egrep -o '(\w+)\sbytes' | awk -F ' ' '{sum+=$1; } END {print sum}'";

			$output = exec_cli($command);
			if ($output > env('HERMES_MAX_SPOOL')){
				$path = Storage::disk('local')->delete($origpath);
        		return response()->json(['message' => 'HMP error: spool larger than ' . env('HERMES_MAX_SPOOL') .' bytes ' ], 500);
				
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
     * updateMessage - update Hermes Message Pack (DEPRECATED)
     * parameter: id and http request
     *
     * @return Json
     */
    public function updateMessage($id, Request $request)
    {
        if($message = Message::findOrFail($id)){
            $message->update($request->all());
			Log::info('update message ' . id);
            return response()->json($user, 200);
        }
        else{
			Log::warning('update message cant find ' . $id);
        	return response()->json(['message' => 'cant find ' . $id], 404);
        }
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
     * unpackInboxMessage - Unpack Hermes Message Pack
     * parameter: id and http request
     *
     * @return Json
     */
    public function unpackInboxMessage($arg){
        $arg = explode('_', $arg);
        $orig = $arg[0];
        $id = $arg[1];
        $id = explode('.', $id)[0];

        $message='';
        // Test for tmp dir, if doesnt exist, creates it
        if (! Storage::disk('local')->exists('inbox/tmp')){
            if(!Storage::disk('local')->makeDirectory('tmp')){
        		return response()->json(['message' => 'Hermes unpack inbox message Error: can\'t find or create tmp dir'], 500);
            }
        }
        // Test for HMP file and unpack it
         if (Storage::disk('local')->exists('inbox/'. $orig  . '_' . $id . '.hmp')){
            // Get path, unpack into tmp and read message data
            $path = Storage::disk('local')->path('');
            $command  = 'tar xvfz ' .  $path . 'inbox/' . $orig .'_' . $id  . '.hmp' . ' -C ' . $path . 'tmp/'  ;
            $output = exec_cli($command);
            $files[] = explode(' ', $output);

            // Test for HMP: hermes message package, create record on messages database
            if (Storage::disk('local')->exists('tmp/'.$id.'/hmp.json')){
                $messagefile = json_decode(Storage::disk('local')->get('tmp/'. $id . '/hmp.json'));
                $message = @json_decode(json_encode($messagefile), true);
				// force reset id to get the next from db
                $message['id'] = null;
				// force inbox flag
                $message['inbox'] = true;
            }
            else {
        		return response()->json(['message' => 'Hermes unpack inbox message Error: cant find json file from unpacked message'], 500);
            }

            //create message on database, delete tar and hmp
            if(!$message = Message::create($message)){
        		return response()->json(['message' => 'Hermes unpack inbox message Error: cant create message on db'], 500);
            }

            // Move attached files
			// test for field file and fileid in message
			if($message['file'] && $message['fileid'])
			{
				// test if file exists 
            	if (Storage::disk('local')->exists('tmp/'.$id.'/' . $message['fileid'])){
                	// Test and create download folder if it doesn't exists
                	if (! Storage::disk('local')->exists('downloads')){
                    	if(!Storage::disk('local')->makeDirectory('downloads')){
        					return response()->json(['message' => 'Hermes unpack inbox message Error: can\'t find or create downloads dir'], 500);
                    	}
                	}
                	// movefile 
                	if (! Storage::disk('local')->move('tmp/' . $id .'/' .  $message['fileid'] , 'downloads/' . $message['fileid'] )){
        				return response()->json(['message' => 'Hermes unpack inbox message Error: can\'t move imagefile'], 500);
                	}
                	// TODO move audio and other files
            	}

			}

            if (Storage::disk('local')->exists('tmp/'.$id)){
                if (!Storage::disk('local')->deleteDirectory('tmp/' .  $id)){
        			return response()->json(['message' => 'Hermes unpack inbox message Error: can\'t delete tmp dir'], 500);
                }
				//TODO error 
            	$fullpath = Storage::disk('local')->path('inbox/'. $orig . '_' . $message['id'] . '.hmp');
				$command = 'sudo rm -f ' . $fullpath;
				if (! exec_cli_no($command)){
        		 	return response()->json(['message' => 'Hermes unpack inbox message Error: can\'t delete orig file'], 500);
				}
            }
            else{
        		return response()->json(['message' => 'Hermes unpack inbox message Error: can\'t create message on database'], 500);
            }
        }
        else {
        	return response()->json(['message' => 'Hermes unpack inbox message Error: can\'t find HMP'], 500);
        }
        Log::info('API unpack  '. $id  . ' - ' . $message .  ' from ' . $orig  );

        return response()->json(['message' => $message], 200);
    }

    /**
     * showAllInboxMessages
     *
     * @return Json
     */
    public function showAllInboxMessages()
    {
        $files = \Storage::allFiles('inbox');
        $file = [];

        /*$filtered_files = array_filter($files, function($str){
            return strpos($str, 'hmp') === 0;
        });*/

        $files_out = [];
        for ($i = '0' ; $i < count($files); $i++) {
            $file = explode('inbox/', $files[$i]);

            if(!empty($files[$i])) {
                $files_out[] = $file[1];
            }

        }
        //var_dump($files_out);
        return response()->json($files_out);
    }

    /**
     * showOneInboxMessage
     * parameter: message id
     * @return Json
     */
    public function showOneInboxMessage($id)
    {
        $file = \Storage::get('inbox/' . $id);
        $output = explode('}', $file)[0];
        $output = $output . '}';
        $output = json_decode($output);
        return response()->json($output);
    }

    /**
     * parameter: message id
     * @return Json
     */
    public function hideInboxMessage($id)
    {
        \Storage::move('inbox/' . $id, 'inbox/.' . $id);
        log::info('hide message ' . $id);
		return response()->json(['hide messag ' . $id . 'Sucessfully'], 200);
    }

    /**
     * unhideInboxMessage
     * parameter: message id
     *
     * @return Json
     */
    public function unhideInboxMessage($id)
   {
        \Storage::move('inbox/.' . $id, 'inbox/' . $id);
		return response()->json(['unhide' . $id . 'Sucessfully'], 200);
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

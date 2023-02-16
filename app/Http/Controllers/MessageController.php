<?php

namespace App\Http\Controllers;

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
    if ($type == 'inbox') {
      return response()->json(Message::where('inbox', '=', true)->orderBy('sent_at')->get());
    }

    if ($type == 'draft') {
      return response()->json(Message::where('draft', '=', true)->orderBy('sent_at')->get());
    }

    //Sent
    return response()->json(Message::where('inbox', '!=', true)->where('draft', '!=', true)->orderBy('sent_at')->get());
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

    $this->validate($request, [
      'orig' => 'required|string',
      'dest' => 'required|array',
      'name' => 'required|string',
      // 'text' => 'string',
      // 'pass' => 'string',
      // 'secure' => 'boolean'
    ]);

    $request->inbox = false;
    $request->orig = explode("\n", exec_cli("cat /etc/uucp/config|grep nodename|cut -f 2 -d \" \""))[0];

    $message = Message::create($request->all());

    if (!$message) {
      (new ErrorController)->saveError(get_class($this), 500, 'API Error: Hermes pack message Error: can\'t create message in DB');
      return response()->json(['message' => 'Server error'], 500);
    }

    //TODO - Create own function ()
    if ($request->pass && $request->pass != '' && $request->pass != 'undefined') {

      $command = 'echo "' . $request->text . '"| gpg -o - -c -t --cipher-algo AES256 --utf8-strings --batch --passphrase "' . $request->pass . '"  --yes -';

      $cryptout = "";

      $output = exec_cli($command);

      if (!$output) {
        (new ErrorController)->saveError(get_class($this), 500, 'API Error: sendHMP can not encrypt the message');
        return response()->json(['message' => 'Server error'], 500);
      }

      $cryptout = $output; // redundant

      $message->secure = true;
      $message->text = bin2hex($cryptout);
      $message->save();
    }

    //find the message in database
    // Assures to delete the working path
    Storage::deleteDirectory('tmp/' . $message->id);

    $file = $this->createFile($message);
    $message = $this->sentUUCPMessage($message, $file);

    if (is_int($message) && $message == 500) {
      return response()->json(['message' => 'Server error'], 500);
    }

    return response()->json(['message' => $message], 200);
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

    if ($message->fileid && $message->inbox) {
      Storage::disk('local')->delete('downloads/' . $message->fileid);
    }

    if ($message->fileid && !$message->inbox) {
      Storage::disk('local')->delete('uploads/' . $message->fileid);
    }

    return response()->json(['message' => 'Delete sucessfully: ' . $id], 200);
  }

  /**
   * unCrypt text message
   * parameter: message id, $request->pass
   * @return Json
   */
  public function unCrypt($id, Request $request)
  {

    if (!$request->pass && $request->pass == '') {
      (new ErrorController)->saveError(get_class($this), 500, 'API Error: HMP uncrypt error - form pass is required');
      return response()->json(['message' => 'Server error'], 500);
    }

    $message = Message::find($id);

    if (!$message) {
      (new ErrorController)->saveError(get_class($this), 500, 'API Error: HMP uncrypt error - can not find message');
      return response()->json(['message' => 'Server error'], 500);
    }

    if (!$message['secure']) {
      (new ErrorController)->saveError(get_class($this), 500, 'API Error: HMP uncrypt error - message is not secured');
      return response()->json(['message' => 'Server error'], 500);
    }

    $crypt = hex2bin($message['text']);
    $messageUncrypt = Storage::disk('local')->put('tmp/' . $message->id . '-uncrypt', $crypt);

    if ($messageUncrypt) {
      $path = Storage::disk('local')->path('tmp') . '/' . $message->id . '-uncrypt';
      $command  = 'gpg -d --batch --passphrase "' .  $request->pass . '" --decrypt ' . $path;
      $output = exec_cli($command);

      return response()->json(['message' => $output], 200);
    }

    (new ErrorController)->saveError(get_class($this), 500, 'API Error: HMP uncrypt error - message can not be uncrypted');
    return response()->json(['message' => 'Server error'], 500);
  }

  public function createFile($message)
  {

    //$message = @json_decode(json_encode($messagefile), true);
    Storage::disk('local')->deleteDirectory('tmp/' . $message->id);

    // Write message file
    if (!Storage::disk('local')->put('tmp/' . $message->id . '/hmp.json', $message)) {
      (new ErrorController)->saveError(get_class($this), 500, 'API Error: sendHMP can not write message file');
      return response()->json(['message' => 'Server error'], 500);
    }

    // Has file?
    if ($message->fileid && Storage::disk('local')->exists('uploads/' . $message->fileid)) {
      // TODO Mantain original files?
      if (!Storage::disk('local')->copy('uploads/' . $message->fileid, 'tmp/' . $message->id . '/' . $message->fileid)) {
        (new ErrorController)->saveError(get_class($this), 500, 'API Error: Hermes send message error - can not move file');
        return response()->json(['message' => 'Server error'], 500);
      }
    }

    $pathtmp = Storage::disk('local')->path('tmp');
    $command  = 'tar cfz ' . $pathtmp . '/' . $message->id . '.hmp -C ' .  $pathtmp . ' ' . $message->id;

    if ($output = exec_cli($command)) {
      (new ErrorController)->saveError(get_class($this), 500, 'API Error: Hermes send message error - cant move image file' . $output . $command);
      return response()->json(['message' => 'Server error'], 500);
    }

    $origpath = 'tmp/' . $message->id . '.hmp';

    // check file size
    $hmpsize = Storage::disk('local')->size($origpath);
    if ($hmpsize > env('HERMES_MAX_FILE')) {
      $path = Storage::disk('local')->delete($origpath);
      (new ErrorController)->saveError(get_class($this), 500, 'API Error: HMP error - larger than ' . env('HERMES_MAX_FILE'));

      return response()->json(['message' => 'Server error'], 500);
    }

     // set new origpath on outbox
     $origpath = env('HERMES_OUTBOX') . '/' . $message->id . '.hmp';
     $path = Storage::disk('local')->path($origpath);

    //work path
    if (!env('HERMES_OUTBOX')) {
      (new ErrorController)->saveError(get_class($this), 500, 'API Error: Hermes pack message Error: cant package the file' . $path);
      return response()->json(['message' => 'Server error'], 500);
    }

    // Clean outbox destination and move the package
    if (!Storage::disk('local')->move('tmp/' . $message->id . '.hmp', $origpath)) {
      (new ErrorController)->saveError(get_class($this), 500, 'API Error: Hermes pack message Error: cant package the file' . $path);
      return response()->json(['message' => 'Server error'], 500);
    }

    $file = [
      'hmpsize' => $hmpsize,
      'path' => $path,
      'origpath' => $origpath
    ];

    return $file;
  }

  public function sentUUCPMessage($message, $file)
  {
    // UUCP -C Copy  (default) / -d create dirs
    if (!Storage::disk('local')->exists($file['origpath'] )) {
      (new ErrorController)->saveError(get_class($this), 500, 'API Error: Hermes send message error - Cant find ' . $file['path']);
      return 500;
    }
    //send message by uucp
    foreach ($message->dest as $dest) {
      //check spool size
      $command = "uustat -s " . $dest . " -u www-data  | egrep -o '(\w+)\sbytes' | awk -F ' ' '{sum+=$1; } END {print sum}'";
      $destspoolsize = exec_cli($command);
      $destspoolsize = $file['hmpsize'] + intval($destspoolsize);

      if ($destspoolsize > env('HERMES_MAX_SPOOL')) {
        $file['path'] = Storage::disk('local')->delete($file['$origpath']);
        (new ErrorController)->saveError(get_class($this), 500, 'API Error: HMP spool larger than ' . env('HERMES_MAX_SPOOL') . ' bytes');
        return 500;
      }

      $command = 'uucp -r -j -C -d \'' .  $file['path'] . '\' \'' . $dest . '!~/' . $message->orig . '_' . $message->id . '.hmp\'';

      if (!$output = exec_cli_no($command)) {
        (new ErrorController)->saveError(get_class($this), 500, 'API Error: Hermes sendMessage - Error on uucp:  ' . $output . ' - ' . $command);
        return 500;
      }
    }

    //setting no draft
    if (!$message->update(['draft' => false])) {
      (new ErrorController)->saveError(get_class($this), 500, 'API Error: Hermes sendMessage - cant update no draft:  ' . $output);
      return 500;
    }

    //delete hmp file
    Storage::disk('local')->delete($file['origpath']);
    return $message;
  }
}

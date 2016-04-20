<?php
namespace App\Http\Controllers;

use App\Code;
use Illuminate\Http\Request;

class CodeController extends Controller {

  public function index() {
    return view("home")/*->with( "codes", Code::all() )*/;
  }

  public function code(Request $request) {

    // Build response
    $response = [];
    $response["response_type"] = "in_channel";

    $code = strtolower($request->input("text", "10-4"));
    $code = str_replace(" ", "-", $code);

    $response_url = $request->input("response_url");
    $user = $request->input("user_name");

    if( $code == "random" ) {

      // Pick one at random
      $object = Code::orderByRaw("RAND()")->first();
      $response["text"] = "{$user}: {$object->code}";
      $response["attachments"][]["text"] = "{$object->definition}";

    } else if( $code == "help" ) {

      // List all codes
      $response["response_type"] = "ephemeral";
      $response["text"] = "Breaker, breaker...";
      $response["attachments"][]["text"] = "Type /radio [code]\nView all codes at http://slackrad.io\nValid keyword commands: random, list, help";

    } else if ( $code == "list" ) {

      $all = Code::all();
      $response["response_type"] = "ephemeral";
      $response["text"] = "All accepted APCO 10 codes:";

      $str = "";
      foreach( $all as $code ) {
        $str .= "{$code->code}: {$code->definition}\n";
      }
      $response["attachments"][]["text"] = $str;

    } else {

      if( $object = Code::where("code", "=", $code)->first() ) {
        // Code found
        $object->increment("count");
        $response["text"] = "{$user}: {$object->code}";
        $response["attachments"][]["text"] = "{$object->definition}";
      } else {
        // Code not found
        $response["response_type"] = "ephemeral";
        $response["text"] = "Code not found, try `/radio list` to see 'em all";
      }

    }

    // POST it
    $data_string = json_encode($response);
    $ch = curl_init($response_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Content-Length: ' . strlen($data_string))
    );
    $result = curl_exec($ch);

    return "";

  }

}

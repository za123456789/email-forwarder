<?php

namespace App\Http\Controllers\Admin;

use App\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmailsRequest;
use App\Http\Requests\Admin\UpdateEmailsRequest;
use Illuminate\Support\Facades\Response;
use Validator;

class EmailsController extends Controller
{
    /**
     * Display a listing of Email.
     *
     * @return \Illuminate\Http\Response
     */

    public $response_type;
    public $response_msg;
    public $response_code;
    public $allowed_domains = array('mydevops.space','mail-forward.wpmudev.host','missionstay.com','missionstay.org');

    public function index(Request $request)
    {
        // $email = Email::all();

        // return $email;
           
        if (! Gate::allows('email_access')) {
            return abort(401);
        }


        if (request('show_deleted') == 1) {
            if (! Gate::allows('email_delete')) {
                return abort(401);
            }
            $emails = Email::onlyTrashed()->get();
        } else {
            $emails = Email::all();
        }

         return view('admin.emails.index', compact('emails'));
    }

    public function insert(Request $request){

        if ($this->postfix_config($request->input('from'), $request->input('to')) != "401") {
        $validator = Validator::make($request->all(), [ 
            'from' => 'required|email|unique:emails,from', 
            'to' => 'required|email', 
        ],[
            'from.unique' => 'Duplicate forwarder'
        ]);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 401);            
        }

        $email = new Email;
        $email->id = $request->input('id'); 
        $email->from = $request->input('from');
        $email->to = $request->input('to');
        $result =  $email->save();
       
        }
            return response()->json([$this->response_type => $this->response_msg], $this->response_code);

    }

    public function delete(Request $request){
         
         $from_add = $request->from;
         $email = Email::where('from', $from_add)->first();
         if ($email == null ){
            return response()->json(['message' => "Forwarder not found"], 401);
            }
            else {
                $this->destroy($email->id);
            $grep_forwarder = shell_exec("grep '$request->from' /email/virtual");
            $grep_array = explode(' ', $grep_forwarder);
            $content = file_get_contents('/email/virtual'); 
            $content = str_replace($grep_forwarder, ' ', $content);
            file_put_contents ('/email/virtual', $content);        
            system('sudo docker exec emailserver postmap /etc/postfix/virtual');            
            system('sudo docker exec emailserver postfix reload');

            }

            return response()->json(['success' => "Forwarder deleted"], 200);

    }

    public function updateemail(Request $request)
    {   
        $from_add = $request->from;
        $email = Email::where('from', $from_add)->first();
        if ($email == null ){
        
            return response()->json(['message' => "Forwarder not found"], 404);
        }
        else {
            $email->update($request->all());

            $grep_forwarder = shell_exec("grep '$request->from' /email/virtual");
            $grep_array = explode(' ', $grep_forwarder);
            $new_forwarder = $grep_array[0] ."   ". $request->to; 
            $content = file_get_contents('/email/virtual'); 
            $content = str_replace($grep_forwarder, $new_forwarder, $content);
            file_put_contents ('/email/virtual', $content);        
            system('sudo docker exec emailserver postmap /etc/postfix/virtual');            
            system('sudo docker exec emailserver postfix reload');

            }
    
            return response()->json(['success' => "Forwarder updated"], 200);
    }

    public function postfix_config($from, $to){

        $email_forwarder = $from. "   ". $to;        
        $domain = explode('@', $from)[1];

        if (in_array($domain, $this->allowed_domains)) {
        
            $grep_domain = shell_exec("grep '$domain' /email/relaydomains");

       if ($grep_domain == null ) {
            $relaydomains = $domain . " #domain" . "\n"; 
            $relaydomains .= file_get_contents('/email/relaydomains');
            file_put_contents('/email/relaydomains',  $relaydomains);
            system('sudo docker exec emailserver postmap /etc/postfix/relaydomains');
        } 
            $grep_forwarder = shell_exec("grep '$email_forwarder' /email/virtual");
       
        if ($grep_forwarder == null ){

            $current_forwarder = $email_forwarder . "\n";
            $current_forwarder .= file_get_contents('/email/virtual'); 
            file_put_contents('/email/virtual',  $current_forwarder);
            system('sudo docker exec emailserver postmap /etc/postfix/virtual');            
            system('sudo docker exec emailserver postfix reload');
            $this->response_type = "success";
            $this->response_msg = "Forwarder added";
            $this->response_code = 200;
        } else {
            $this->response_type = "error";
            $this->response_msg = "Duplicate forwarder";
            $this->response_code = "401";
                }
        } else {
            $this->response_type = "error";
            $this->response_msg = "Domain not allowed";
            $this->response_code = "401";
        } 

    return $this->response_code;

    }

    /**
     * Show the form for creating new Email.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Gate::allows('email_create')) {
            return abort(401);
        }
        return view('admin.emails.create');
    }

    /**
     * Store a newly created Email in storage.
     *
     * @param  \App\Http\Requests\StoreEmailsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEmailsRequest $request)
    {
        if (! Gate::allows('email_create')) {
            return abort(401);
        }
        $email = Email::create($request->all());


        $email_forwarder = $request->from . "   ". $request->to ;

            // $domain = explode('@', $request->input('from'))[1];
            // $relaydomains = $domain . "\n"; 
            // $relaydomains .= file_get_contents('/email/relaydomains');
            // file_put_contents('/email/relaydomains',  $relaydomains);

        $current_forwarder = $email_forwarder . "\n";
        $current_forwarder .= file_get_contents('/email/virtual'); 
        file_put_contents('/email/virtual',  $current_forwarder);
        system('sudo docker exec emailserver postmap /etc/postfix/virtual');            
        system('sudo docker exec emailserver postfix reload');


        return redirect()->route('admin.emails.index');
    }


    /**
     * Show the form for editing Email.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('email_edit')) {
            return abort(401);
        }
        $email = Email::findOrFail($id);

        return view('admin.emails.edit', compact('email'));
    }

    /**
     * Update Email in storage.
     *
     * @param  \App\Http\Requests\UpdateEmailsRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEmailsRequest $request, $id)
    {
        if (! Gate::allows('email_edit')) {
            return abort(401);
        }

        $email = Email::findOrFail($id);
        $email->update($request->all());

        $grep_forwarder = system("grep '$email->from' /email/virtual");
        $grep_array = explode(' ', $grep_forwarder);
        $new_forwarder = $grep_array[0] ."   ". $request->to; 
        $content = file_get_contents('/email/virtual'); 
        $content = str_replace($grep_forwarder, $new_forwarder, $content);
        file_put_contents ('/email/virtual', $content);
        system('sudo docker exec emailserver postmap /etc/postfix/virtual');            
        system('sudo docker exec emailserver postfix reload');                
        
        return redirect()->route('admin.emails.index');
    }

    /**
     * Display Email.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (! Gate::allows('email_view')) {
            return abort(401);
        }
        $email = Email::findOrFail($id);

        return view('admin.emails.show', compact('email'));
    }


    /**
     * Remove Email from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        // if (! Gate::allows('email_delete')) {

        //     return abort(401);
        // }
        $email = Email::findOrFail($id);
        $email->delete();

        return redirect()->route('admin.emails.index');
    }

    /**
     * Delete all selected Email at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (! Gate::allows('email_delete')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = Email::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }


    /**
     * Restore Email from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        if (! Gate::allows('email_delete')) {
            return abort(401);
        }
        $email = Email::onlyTrashed()->findOrFail($id);
        $email->restore();

        return redirect()->route('admin.emails.index');
    }

    /**
     * Permanently delete Email from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {
        if (! Gate::allows('email_delete')) {
            return abort(401);
        }
        $email = Email::onlyTrashed()->findOrFail($id);
        $email->forceDelete();

        return redirect()->route('admin.emails.index');
    }
}

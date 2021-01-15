<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;

use App\Models\User;

use App\Models\Mesage;
use Illuminate\Support\Facades\Http;

use function GuzzleHttp\Promise\all;

class UserController extends Controller
{

    //User List  Auth_email
    public function userGet(Request $req)
    {
        if ($req->integration_id) {
            $auth_user = User::where('integration_id', $req->integration_id)->first();  //Auth User
            // $users = User::where('integration_id' , '<>' , $req->integration_id)->get(); //auth exacin chi tpum listi mej
            $users = User::all();
            // return User::all();
            $last_messages = Mesage::where([
                ['seen', '=', 0],
                ['receiver_id', '=', $auth_user->id]
            ])->whereIn('creator_id', $users->pluck('id'))
                ->orderBy('id', 'DESC')
                ->get();
            $last_messages_group = [];

            foreach ($last_messages as $v) {
                if (isset($last_messages_group[$v['creator_id']])) continue;
                $last_messages_group[$v['creator_id']] = $v['message'];
            }
        } else {
            $users = User::all();
        }
        return ['auth_user' => $auth_user, 'users' => $users, 'messages' => $last_messages_group ?? ''];
        // return User::all();
    }



    //Login Request JO

    public function loginPost(Request $req)
    {
        $user = User::where(['email' => $req->email])->first();
        if ($user && $req->password == $user->password) { // Hash::check($req->password,$user->password)
            return $user;
        }
        return 'Not Match';
    }

    //Registration JO
    public function registerPost(Request $req)
    {

        $input_values = $req->only(['name', 'password', 'email', 'img']);
        $input_values['password'] = $req->password;//Hash::make($req->password);
        $has_email = User::where('email', $input_values['email'])->first(); //Ete ka User Tableum ->eta stugum

        if ($has_email) {
            return ['success' => false, 'message' => 'Email exists'];
        }

        if ($boolean = User::create($input_values)) { //True False  veradarcnelu hamara
            return ['success' => (bool) $boolean];
        }

        return ['success' => false, 'message' => 'Error'];
    }
    //Search
    public function search(Request $req , $id)
    {
      
        $users_from_api = Http::get('https://www.webwork-tracker.com/chat-api/users?user_id='. $id);
      
        $search_resault = [];
        $word_search =  $req->search;
        $regexp = '/.*' . $word_search . '.*/isu';

        foreach ($users_from_api['users'] as $us) {
            $append = preg_match($regexp, $us['firstname'], $match) || preg_match($regexp, $us['lastname'], $match);

            if ($append) $search_resault[] = $us;
        }
        return $search_resault;
    }


    public function userLast(Request $req )
    {
        $user = Http::get('https://www.webwork-tracker.com/chat-api/users?user_id=' . $req->user_id);// . mianum
        $users = $user['users'];

        if ($req->user_id) {

            $userId = $req->user_id;
            $user_ids = array_column($users, 'id');
            foreach ($user_ids as $key => $id) {
                if ($id == $userId) {
                    unset($user_ids[$key]);
                }
            }
            unset($user_ids[$userId]); //login exaci ID-in hanuma
            $last_messages = Mesage::where([
                ['seen', '=', 0],
                ['receiver_id', '=', $userId]
            ])->whereIn('creator_id', $user_ids)
                ->orderBy('id', 'DESC') //ASC 
                ->get();



            $last_messages_group = [];

            foreach ($last_messages as $v) {
                if (isset($last_messages_group[$v['creator_id']])) continue;
                $last_messages_group[$v['creator_id']] =  $v['message'] . $v['created_at']->diffForHumans();
                // shortRelativeDiffForHumans()  => 1d ago , 2h ago
                
                
            }
            return $last_messages_group;
        }
        // return ['messages' => $last_messages_group ];
    }
    public function authUser(Request $req){ 
        $user = Http::get('https://www.webwork-tracker.com/chat-api/users?user_id=66289');
        $userId = $req->user_id;
        $users = $user['users'];
        
        foreach($users as $item){
            if($userId==$item['id'])
            return $item;
        }
        
        
             
    }
}


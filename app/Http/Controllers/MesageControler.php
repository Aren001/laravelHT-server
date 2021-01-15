<?php

namespace App\Http\Controllers;

use App\Models\Mesage;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use function GuzzleHttp\Promise\all;

class MesageControler extends Controller
{

    public function delete($id)
    {
        $mess = Mesage::findOrFail($id);
        $mess->delete();
        return 204;
    }

    //PO
    public function mesageGet(Request $req, $id,$team_id)
    {   
        $userId=$req->user_id;
                   
            // return Mesage::where('team_id', 71)->get();
            
            // return $team_id;
            $messages = Mesage::where(function ($q) use ($userId, $id,$team_id) {   //ID-i pahna stugum
                $q->where('team_id', $team_id);
                $q->where('receiver_id', $userId);
                $q->where('creator_id', $id);
            })->orWhere(function ($q) use ($userId, $id,$team_id) {  //-> ||
                $q->where('team_id', $team_id);
                $q->where('receiver_id', $id);
                $q->where('creator_id', $userId);
            })->get();
            Mesage::where('creator_id', $id)->update(['seen' => 1]);
            // Mesage::where('receiver_id', $userId)->update(['seen' => 1]);//auth-in nayuma poxuma
            return  $messages;
        
        
        
    }



    public function store(Request $req)
    {

            $userId=$req->user_id; //inch uxarkelem eta vercnum
            // return $req->team_id;

            $message = [
                'team_id'=>$req->team_id,
                'creator_id' => $userId,
                'receiver_id' => $req->receiver_id,
                'seen' => $req->seen,
                'message' => preg_replace("/<br>.*/U","<p>",$req->message )  
                //Remove HTML Tags strip_tags()
            ];

            $message = Mesage::create($message);

            return ['success' => (bool) $message];

        return ['success' => false, 'mes' => 'YES', 'req' => $req->all()];
    }
}

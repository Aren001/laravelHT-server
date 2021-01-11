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
    public function mesageGet(Request $req, $id)
    {
        // return $req->user_id;
        $userId=$req->user_id;

            $messages = Mesage::where(function ($q) use ($userId, $id) {   //ID-i pahna stugum
                $q->where('receiver_id', $userId);
                $q->where('creator_id', $id);
            })->orWhere(function ($q) use ($userId, $id) {  //-> ||
                $q->where('receiver_id', $id);
                $q->where('creator_id', $userId);
            })->get();
            Mesage::where('creator_id', $id)->update(['seen' => 1]);
            // Mesage::where('receiver_id', $userId)->update(['seen' => 1]);//auth-in nayuma poxuma

            return $messages;
    }



    public function store(Request $req)
    {

            $userId=$req->user_id; //inch uxarkelem eta vercnum
            // return $req->team_id;
            $v= $req->user_id==$req->receiver_id ? 1 : 0;
            // return $v;
            $message = [
                'team_id'=>$req->team_id,
                'creator_id' => $userId,
                'receiver_id' => $req->receiver_id,
                'seen' => $req->seen,
                'message' => $req->message
            ];

            $message = Mesage::create($message);

            return ['success' => (bool) $message];

        return ['success' => false, 'mes' => 'YES', 'req' => $req->all()];
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Profile extends Model
{
    protected $table = 'profiles';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id','image_top','image_right','text'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */


    public static function createProfile($request)
    {
        $chek = self::where('user_id','=', Auth::user()->id)->exists();
        if ($chek) {
           if($request->attachmentTop !== "null") {
               $success = self::changeImg("image_top", $request->attachmentTop);
           }
            if($request->attachmentRight !== "null") {
                $success = self::changeImg("image_right", $request->attachmentRight);
            }

            if($request->text !== "null") {
                $success = self::where('user_id', '=', Auth::user()->id)
                    ->update(['text' => $request['text'],]);
            }
            if($request->vk !== "null") {
                $success = self::where('user_id', '=', Auth::user()->id)
                    ->update(['vk' => $request['vk'],]);
            }
            if($request->instagram !== "null") {
                $success = self::where('user_id', '=', Auth::user()->id)
                    ->update(['instagram' => $request['instagram'],]);
            }
            if($request->facebook !== "null") {
                $success = self::where('user_id', '=', Auth::user()->id)
                    ->update(['facebook' => $request['facebook'],]);
            }
            if($request->twitter !== "null") {
                $success = self::where('user_id', '=', Auth::user()->id)
                    ->update(['twitter' => $request['twitter'],]);
            }
        }
        if (!$chek) {
            mkdir(public_path() . "/images/Profile/".Auth::user()->name.Auth::user()->id, 0700);
            if(!empty($request->attachmentTop)) {
                $image_topName = self::saveImg($request->attachmentTop);
            }else{$image_topName = null;}
            if(!empty($request->attachmentRight)) {
                $image_rightName = self::saveImg($request->attachmentRight);
            }else{$image_rightName = null;}

            $success = self::create([
                'user_id' => Auth::user()->id,
                'image_top' => Auth::user()->name.Auth::user()->id."/".$image_topName,
                'image_right' => Auth::user()->name . Auth::user()->id."/".$image_rightName,
                'text' => $request['text'],
                'vk' => $request['vk'],
                'instagram' => $request['instagram'],
                'facebook' => $request['facebook'],
                'twitter' => $request['twitter'],
            ]);
        }
        return response()->json(["status" => $success]);
    }

    private static function saveImg($img)
    {
        $image = $img->getClientOriginalName();
        $imageNewName = Auth::user()->id . $image;
        $img->move(public_path() . "/images/Profile/".Auth::user()->name.Auth::user()->id, $imageNewName);

        return $imageNewName;
    }

    private static function changeImg($teg, $attachment)
    {
        $img = self::where('user_id','=', Auth::user()->id)->pluck($teg);
        if($img[0] !== null){
        unlink(public_path() . "/images/Profile/".$img[0]);
        }
        if ($attachment !== 'false') {
            $image_topName = Auth::user()->name . Auth::user()->id . "/" . self::saveImg($attachment);
            $success = self::where('user_id', '=', Auth::user()->id)
                ->update([$teg => $image_topName,]);
        }
        else{$success = self::where('user_id', '=', Auth::user()->id)
            ->update([$teg => null]);}

        return $success;
    }

    public static function GetProfile()
    {
        $chek = self::where('user_id', '=', Auth::user()->id)->exists();
        if (!$chek) {

            return false;

        } else {
            $profile = self::where('user_id', Auth::user()->id)->get();

            return $profile;
        }
    }
}

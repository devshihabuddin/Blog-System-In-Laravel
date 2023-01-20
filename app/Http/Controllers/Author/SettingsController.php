<?php

namespace App\Http\Controllers\Author;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index(){
        return view('author.settings');
    }

    public function updateProfile(Request $request){

        $this->validate($request,[
            'name'  => 'required',
            'email' => 'required|email',
            'image' => 'required|mimes:png,jpg,jpeg,web'

        ]);
        $image  = $request->file('image');
        $slug   =  Str::slug($request->name);
        $user   = User::findOrFail(Auth::id());
        if(isset($image)){
            $currentdate    = Carbon::now()->toDateString();
            $imagename  = $slug.'-'.$currentdate.'-'.uniqid().'.'.$image->getClientOriginalExtension();
            if(!Storage::disk('public')->exists('profile/')){
                Storage::disk('public')->makeDirectory('profile/');
            }

            if(Storage::disk('public')->exists('profile/'.$user->image)){
                Storage::disk('public')->delete('profile/'.$user->image);

            }
            $profile    = Image::make($image)->resize(500,500)->stream();
            Storage::disk('public')->put('profile/'.$imagename,$profile);
        }else{
            $imagename = $user->image;
        }

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->image = $imagename;
        $user->about  = $request->about;
        $user->save();
        Toastr::success('Profile Successfully Updated :)','Success');
        return redirect()->back();
    }

    public function updatePassword(Request $request){

        $this->validate($request,[
            'old_password'  => 'required',
            'password'      => 'required|confirmed'
        ]);
        $hashedPassword = Auth::user()->password;
        if(Hash::check($request->old_password, $hashedPassword)){

            if(!Hash::check($request->password, $hashedPassword)){

                $user   = User::findOrFail(Auth::id());
                $user->password = Hash::make($request->password);
                $user->save();
                Toastr::success('Password Successfully Updated','Success');
                Auth::logout();
                return redirect()->back();
            }
        }else{
            Toastr::error('Current Password does not match','Error');
            return redirect()->back();
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Requests\ProfileRequest;

class ProfileController extends Controller
{
    // プロフィール編集画面（設定画面）
    public function profile()
    {
        $user = Auth::user();
        return view('profile', compact('user'));
    }

    // プロフィール更新
    public function upload(ProfileRequest $request, User $user)
    {
        $data = $request->all();
        $user = Auth::user();

        // profile_imageはバリデーション上は任意(sometimes)なので、
        // 選択されていない場合は既存の画像を変更しない
        $dir = null;
        $file_name = null;
        if ($request->hasFile('profile_image')) {
            $dir = 'images';
            $file_name = $request->file('profile_image')->getClientOriginalname();
            $request->file('profile_image')->storeAs('public/' . $dir, $file_name);
        }

        $user->profileUpload($data, $dir, $file_name);

        return redirect('/mypage?tab=sell');
    }
}

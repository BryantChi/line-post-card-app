<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Flash;

class SubUserProfileController extends Controller
{
    //
    /**
     * 顯示編輯個人資料的表單。
     */
    public function edit()
    {
        $subUser = Auth::user(); // 假設登入的使用者即為 sub_user
                               // 如果 SubUser 是獨立的模型且非預設 User 模型，
                               // 您可能需要用不同方式獲取，例如 Auth::guard('sub_user_guard')->user()
                               // 或者如果 User 模型有 type 欄位：$user = Auth::user(); if ($user->type !== 'sub_user') abort(403);
        if ($subUser->id !== Auth::id()) {
            Flash::error('您沒有權限編輯此帳號');
            return redirect()->route('sub-users.index')
                ->with('error', '您沒有權限編輯此帳號');
        }
        return view('admin.profile.edit', compact('subUser'));
    }

    /**
     * 更新使用者的個人資料。
     */
    public function update(Request $request)
    {
        $subUser = Auth::user();

        // 確保只能更新自己的個人資料
        if ($subUser->id !== Auth::id()) {
            Flash::error('您沒有權限更新此帳號');
            return redirect()->route('sub-users.index')
                ->with('error', '您沒有權限更新此帳號');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $subUser->name = $validated['name'];

        if (!empty($validated['password'])) {
            $subUser->password = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $subUser->save();

        Flash::success('個人資料已成功更新！');
        return redirect()->route('sub-users.index')
            ->with('success', '個人資料已成功更新！');
    }
}

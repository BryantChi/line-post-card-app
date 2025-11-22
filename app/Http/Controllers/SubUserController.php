<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Laracasts\Flash\Flash;

class SubUserController extends Controller
{
    /**
     * 顯示所有子帳號
     */
    public function index()
    {
        // 取得當前用戶的所有子帳號
        // 如果是超級管理員，則顯示所有子帳號
        if (Auth::user()->role == 'super_admin') {
            $subUsers = User::where('role', 'sub_user')->latest()->paginate(10);
            return view('admin.sub_users.index', compact('subUsers'));
        }
        $subUsers = User::where('parent_id', Auth::id())->latest()->paginate(10);
        return view('admin.sub_users.index', compact('subUsers'));
    }

    /**
     * 顯示建立子帳號表單
     */
    public function create()
    {
        // 傳遞預設配額設定到視圖
        $defaultMaxBusinessCards = 1;
        $defaultMaxCardBubbles = 10;

        // 如果是超級管理員，則可以選擇父帳號
        if (Auth::user()->isSuperAdmin()) {
            // 獲取所有主帳號列表
            $mainUsers = User::where('role', 'main_user')->get();
            return view('admin.sub_users.create', compact('mainUsers', 'defaultMaxBusinessCards', 'defaultMaxCardBubbles'));
        }
        return view('admin.sub_users.create', compact('defaultMaxBusinessCards', 'defaultMaxCardBubbles'));
    }

    /**
     * 儲存新子帳號
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'parent_id' => 'nullable',
            'expires_at' => 'nullable|date|after:today',
            'active' => 'boolean',
            'remarks' => 'nullable|string',
            'max_business_cards' => 'required|integer|min:1',
            'max_card_bubbles' => 'required|integer|min:1|max:10',
        ], [
            'max_business_cards.required' => '請設定名片數量上限',
            'max_business_cards.min' => '名片數量上限至少為1',
            'max_card_bubbles.required' => '請設定卡片數量上限',
            'max_card_bubbles.min' => '卡片數量上限至少為1',
            'max_card_bubbles.max' => '卡片數量上限最多為10',
        ]);

        $subUser = new User();
        $subUser->name = $validated['name'];
        $subUser->email = $validated['email'];
        $subUser->password = Hash::make($validated['password']);
        $subUser->role = 'sub_user';
        if (Auth::user()->role == 'super_admin') {
            $subUser->parent_id = $validated['parent_id'] ?? Auth::id();
        } else {
            $subUser->parent_id = Auth::id();
        }
        $subUser->expires_at = $validated['expires_at'] ?? Carbon::now()->addYear();
        $subUser->active = $validated['active'] ?? true;
        $subUser->remarks = $validated['remarks'] ?? null;
        $subUser->max_business_cards = $validated['max_business_cards'];
        $subUser->max_card_bubbles = $validated['max_card_bubbles'];
        $subUser->save();

        Flash::success('會員帳號建立成功！');
        return redirect()->route('sub-users.index')
            ->with('success', '子帳號建立成功');
    }

    /**
     * 顯示編輯子帳號表單
     */
    public function edit($id)
    {
        $subUser = User::findOrFail($id);

        // Super Admin 可以編輯所有子帳號
        if (Auth::user()->isSuperAdmin()) {
            // 獲取所有主帳號列表
            $mainUsers = User::where('role', 'main_user')->get();
            return view('admin.sub_users.edit', compact('subUser', 'mainUsers'));
        }

        // 主帳號只能編輯自己的子帳號
        if (Auth::user()->isMainUser()) {
            if ($subUser->parent_id !== Auth::id()) {
                Flash::error('您沒有權限編輯此子帳號');
                return redirect()->route('sub-users.index')
                    ->with('error', '您沒有權限編輯此子帳號');
            }
            return view('admin.sub_users.edit', compact('subUser'));
        }
        // 如果是子帳號，則不能編輯
        if (Auth::user()->isSubUser()) {
            Flash::error('子帳號無權編輯其他子帳號');
            return redirect()->route('sub-users.index')
                ->with('error', '子帳號無權編輯其他子帳號');
        }
    }

    /**
     * 更新子帳號
     */
    public function update(Request $request, $sub)
    {
        $subUser = User::findOrFail($sub);

        // 確保只能更新自己的子帳號
        // if ($subUser->parent_id !== Auth::id() && !Auth::user()->isSuperAdmin()) {
        //     return redirect()->route('sub-users.index')
        //         ->with('error', '您沒有權限更新此子帳號');
        // }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($subUser->id),
            ],
            'password' => 'nullable|string|min:6|confirmed',
            'parent_id' => 'nullable',
            'expires_at' => 'nullable|date',
            'active' => 'boolean',
            'remarks' => 'nullable|string',
            'max_business_cards' => 'required|integer|min:1',
            'max_card_bubbles' => 'required|integer|min:1|max:10',
        ]);

        // 檢查是否會導致現有資料超過新限制
        $currentBusinessCards = $subUser->businessCards()->count();
        if ($currentBusinessCards > $validated['max_business_cards']) {
            Flash::warning("該子帳號目前已有 {$currentBusinessCards} 張名片,無法設定低於此數量的上限");
            return redirect()->back()->withInput();
        }

        $subUser->name = $validated['name'];
        $subUser->email = $validated['email'];

        if (!empty($validated['password'])) {
            $subUser->password = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if (Auth::user()->role == 'super_admin') {
            $subUser->parent_id = $validated['parent_id'] ?? Auth::id();
        }

        $subUser->expires_at = $validated['expires_at'] ?? Carbon::parse($subUser->created_at)->addYear();
        $subUser->active = $validated['active'] ?? false;
        $subUser->remarks = $validated['remarks'] ?? $subUser->remarks;
        $subUser->max_business_cards = $validated['max_business_cards'];
        $subUser->max_card_bubbles = $validated['max_card_bubbles'];
        $subUser->save();

        Flash::success('會員帳號更新成功！');
        // 使用 303 See Other 避免 Big Redirect 警告,並防止敏感資訊洩漏
        return redirect()->route('sub-users.index', [], 303)
            ->with('success', '子帳號更新成功');
    }

    /**
     * 刪除子帳號
     */
    public function destroy($sub)
    {
        $subUser = User::findOrFail($sub);

        // 確保只能刪除自己的子帳號
        // if ($subUser->parent_id !== Auth::id()) {
        //     return redirect()->route('sub-users.index')
        //         ->with('error', '您沒有權限刪除此子帳號');
        // }

        $subUser->delete();

        // 刪除子帳號所有的AI數位名片
        $subUser->businessCards()->delete();

        Flash::success('會員帳號刪除成功！');
        return redirect()->route('sub-users.index')
            ->with('success', '子帳號刪除成功');
    }
}

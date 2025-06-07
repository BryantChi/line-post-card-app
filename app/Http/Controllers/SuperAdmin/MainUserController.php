<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MainUserController extends Controller
{
    /**
     * 顯示所有主帳號
     */
    public function index()
    {
        $mainUsers = User::where('role', 'main_user')->latest()->paginate(10);
        return view('super_admin.main_users.index', compact('mainUsers'));
    }

    /**
     * 顯示建立主帳號表單
     */
    public function create()
    {
        return view('super_admin.main_users.create');
    }

    /**
     * 儲存新主帳號
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'active' => 'boolean',
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->role = 'main_user';
        $user->active = $validated['active'] ?? true;
        $user->save();

        return redirect()->route('super_admin.mainUsers.index')
            ->with('success', '主帳號建立成功');
    }

    /**
     * 顯示特定主帳號
     */
    public function show($id)
    {
        $mainUser = User::findOrFail($id);

        if ($mainUser->role !== 'main_user') {
            return redirect()->route('super_admin.mainUsers.index')
                ->with('error', '此用戶不是主帳號');
        }

        return view('super_admin.main_users.show', compact('mainUser'));
    }

    /**
     * 顯示編輯主帳號表單
     */
    public function edit($id)
    {
        $mainUser = User::findOrFail($id);

        if ($mainUser->role !== 'main_user') {
            return redirect()->route('super_admin.mainUsers.index')
                ->with('error', '此用戶不是主帳號');
        }

        return view('super_admin.main_users.edit', compact('mainUser'));
    }

    /**
     * 更新主帳號
     */
    public function update(Request $request, $id)
    {
        $mainUser = User::findOrFail($id);

        if ($mainUser->role !== 'main_user') {
            return redirect()->route('super_admin.mainUsers.index')
                ->with('error', '此用戶不是主帳號');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($mainUser->id),
            ],
            'password' => 'nullable|string|min:6|confirmed',
            'active' => 'boolean',
        ]);

        $mainUser->name = $validated['name'];
        $mainUser->email = $validated['email'];

        if (!empty($validated['password'])) {
            $mainUser->password = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $mainUser->active = $validated['active'] ?? true;
        $mainUser->save();

        return redirect()->route('super_admin.mainUsers.index')
            ->with('success', '主帳號更新成功');
    }

    /**
     * 刪除主帳號
     */
    public function destroy($id)
    {
        $mainUser = User::findOrFail($id);

        if ($mainUser->role !== 'main_user') {
            return redirect()->route('super_admin.mainUsers.index')
                ->with('error', '此用戶不是主帳號');
        }

        // 連帶刪除此主帳號所有的子帳號
        $mainUser->subUsers()->delete();

        $mainUser->delete();

        return redirect()->route('super_admin.mainUsers.index')
            ->with('success', '主帳號及其所有子帳號已刪除');
    }
}

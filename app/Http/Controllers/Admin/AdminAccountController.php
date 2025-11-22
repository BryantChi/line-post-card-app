<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\Admin\CreateAdminAccountRequest;
use App\Http\Requests\Admin\UpdateAdminAccountRequest;
use Illuminate\Http\Request;
use Flash;
use Response;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AdminAccountController extends AppBaseController
{

    public function index()
    {
        $users = User::where('role', 'super_admin')->latest()->paginate(10);
        return view('admin.admin_users.index')
            ->with('adminUsers', $users);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        if ($user->role !== 'super_admin') {
            Flash::error('User not found');
            return redirect(route('admin.adminUsers.index'));
        }

        return view('admin.admin_users.edit')
            ->with('adminUsers', $user);
    }

    public function create()
    {
        return view('admin.admin_users.create');
    }

    public function store(CreateAdminAccountRequest $request)
    {
        $input = $request->all();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'role' => 'super_admin',
            // 'role' is set to super_admin by default
            'active' => $input['active'] ?? true,
            'signature' => $input['signature'] ?? null,
        ]);
        Flash::success('超級管理者帳號建立成功。');
        return redirect(route('admin.adminUsers.index'));
    }

    public function show($id)
    {
        $user = User::find($id);
        if (empty($user)) {
            Flash::error('User not found');
            return redirect(route('admin.adminUsers.index'));
        }
        return view('admin.admin_users.show')
            ->with('adminUsers', $user);
    }

    public function update(UpdateAdminAccountRequest $request, $id)
    {
        $user = User::find($id);
        $input = $request->all();

        if (empty($user)) {
            Flash::error('User not found');
            return redirect(route('admin.adminUsers.index'));
        }

        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            unset($input['password']);
        }

        $user->update($input);

        Flash::success('超級管理者帳號更新成功。');
        return redirect(route('admin.adminUsers.index'));
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (empty($user)) {
            Flash::error('User not found');
            return redirect(route('admin.adminUsers.index'));
        }

        // 確保不能刪除自己或id為1的超級管理者帳號
        if ($user->id === 1) {
            Flash::error('您不能刪除系統預設的超級管理者帳號。');
            return redirect(route('admin.adminUsers.index'));
        }
        // if ($user->role === 'super_admin' && $user->id !== auth()->user()->id) {
        //     Flash::error('您不能刪除其他超級管理者帳號。');
        //     return redirect(route('admin.adminUsers.index'));
        // }
        if ($user->id === auth()->user()->id) {
            Flash::error('您不能刪除自己的帳號。');
            return redirect(route('admin.adminUsers.index'));
        }

        $user->delete();

        // 刪除超級管理者帳號所有的AI數位名片
        $user->businessCards()->delete();

        Flash::success('使用者帳號刪除成功。');
        return redirect(route('admin.adminUsers.index'));
    }

}

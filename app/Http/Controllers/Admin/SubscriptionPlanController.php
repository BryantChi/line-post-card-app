<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppBaseController;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Flash;

class SubscriptionPlanController extends AppBaseController
{
    public function index(Request $request)
    {
        $query = SubscriptionPlan::query()
            ->withCount('renewalOrders')
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                  ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        }

        $plans = $query->paginate(15)->appends($request->all());

        return view('admin.subscription_plans.index')->with('plans', $plans);
    }

    public function create()
    {
        return view('admin.subscription_plans.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'description'   => 'nullable|string|max:1000',
            'price'         => 'required|integer|min:1|max:999999',
            'duration_days' => 'required|integer|min:1|max:3650',
            'active'        => 'required|in:0,1',
            'sort_order'    => 'required|integer|min:0|max:999',
        ]);

        SubscriptionPlan::create($request->only([
            'name', 'description', 'price', 'duration_days', 'active', 'sort_order',
        ]));

        Flash::success('訂閱方案已建立');
        return redirect(route('admin.subscriptionPlans.index'));
    }

    public function show($id)
    {
        return redirect(route('admin.subscriptionPlans.edit', $id));
    }

    public function edit($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        return view('admin.subscription_plans.edit')->with('plan', $plan);
    }

    public function update(Request $request, $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        $request->validate([
            'name'          => 'required|string|max:100',
            'description'   => 'nullable|string|max:1000',
            'price'         => 'required|integer|min:1|max:999999',
            'duration_days' => 'required|integer|min:1|max:3650',
            'active'        => 'required|in:0,1',
            'sort_order'    => 'required|integer|min:0|max:999',
        ]);

        $plan->update($request->only([
            'name', 'description', 'price', 'duration_days', 'active', 'sort_order',
        ]));

        Flash::success('訂閱方案已更新');
        return redirect(route('admin.subscriptionPlans.index'));
    }

    public function destroy($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        // 有使用中的訂單時不可刪除
        if ($plan->renewalOrders()->whereIn('status', ['pending', 'paid'])->exists()) {
            Flash::error('此方案有進行中或已付款的訂單，無法刪除');
            return redirect(route('admin.subscriptionPlans.index'));
        }

        $plan->delete();

        Flash::success('訂閱方案已刪除');
        return redirect(route('admin.subscriptionPlans.index'));
    }
}

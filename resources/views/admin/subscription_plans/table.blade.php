<div class="table-responsive">
    <table class="table" id="subscription-plans-table">
        <thead>
        <tr>
            <th>排序</th>
            <th>方案名稱</th>
            <th>層級 / 頁數</th>
            <th>價格（NT$）</th>
            <th>有效天數</th>
            <th>標籤</th>
            <th>狀態</th>
            <th>已有訂單數</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @forelse($plans as $plan)
            <tr>
                <td>{{ $plan->sort_order }}</td>
                <td>
                    <div class="d-flex align-items-center">
                        @if($plan->icon)
                            <img src="{{ asset('uploads/' . $plan->icon) }}" alt=""
                                 class="img-thumbnail mr-2" style="width:42px; height:42px; object-fit:cover;">
                        @endif
                        <div>
                            <strong>{{ $plan->name }}</strong>
                            @if($plan->description)
                                <br><small class="text-muted">{{ Str::limit(strip_tags($plan->description), 50) }}</small>
                            @endif
                        </div>
                    </div>
                </td>
                <td>
                    @if($plan->plan_tier)
                        <span class="badge badge-info">{{ \App\Models\SubscriptionPlan::TIER_OPTIONS[$plan->plan_tier] ?? $plan->plan_tier }}</span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                    @if($plan->page_limit)
                        <br><small class="text-muted">{{ $plan->page_limit }} 頁</small>
                    @endif
                </td>
                <td>NT$ {{ number_format($plan->price) }}</td>
                <td>{{ $plan->duration_days }} 天</td>
                <td>
                    @if($plan->is_popular)
                        <span class="badge badge-warning">最受歡迎</span>
                    @endif
                    @if($plan->is_featured)
                        <span class="badge badge-danger">特別優惠</span>
                    @endif
                    @if(!$plan->is_popular && !$plan->is_featured)
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    @if($plan->active)
                        <span class="badge badge-success">啟用</span>
                    @else
                        <span class="badge badge-secondary">停用</span>
                    @endif
                </td>
                <td>{{ $plan->renewal_orders_count }}</td>
                <td>
                    {!! Form::open(['route' => ['admin.subscriptionPlans.destroy', $plan->id], 'method' => 'delete']) !!}
                    <div class="btn-group">
                        <a href="{{ route('admin.subscriptionPlans.edit', $plan->id) }}"
                           class="btn btn-default btn-sm">
                            <i class="far fa-edit"></i>
                        </a>
                        {!! Form::button('<i class="far fa-trash-alt"></i>', [
                            'type' => 'button',
                            'class' => 'btn btn-danger btn-sm js-confirm-delete',
                            'data-confirm' => '確定要刪除「' . $plan->name . '」方案嗎？'
                        ]) !!}
                    </div>
                    {!! Form::close() !!}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center text-muted py-4">尚無訂閱方案，請點右上角新增</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

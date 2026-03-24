<div class="table-responsive">
    <table class="table" id="subscription-plans-table">
        <thead>
        <tr>
            <th>排序</th>
            <th>方案名稱</th>
            <th>價格（NT$）</th>
            <th>有效天數</th>
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
                    <strong>{{ $plan->name }}</strong>
                    @if($plan->description)
                        <br><small class="text-muted">{{ Str::limit($plan->description, 50) }}</small>
                    @endif
                </td>
                <td>NT$ {{ number_format($plan->price) }}</td>
                <td>{{ $plan->duration_days }} 天</td>
                <td>
                    @if($plan->active)
                        <span class="badge badge-success">啟用</span>
                    @else
                        <span class="badge badge-secondary">停用</span>
                    @endif
                </td>
                <td>{{ $plan->renewalOrders()->count() }}</td>
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
                <td colspan="7" class="text-center text-muted py-4">尚無訂閱方案，請點右上角新增</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

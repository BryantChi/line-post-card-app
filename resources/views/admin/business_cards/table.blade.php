<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="businessCards-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>名稱</th>
                <th>建立者</th>
                <th>電子名片-卡片數</th>
                <th>狀態</th>
                <th>建立時間</th>
                <th colspan="3">操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach($businessCards as $businessCard)
                <tr>
                    <td>{{ $businessCard->id }}</td>
                    <td>{{ $businessCard->title }}</td>
                    <td>
                        @if($businessCard->user)
                            {{ $businessCard->user->name }}
                            @if(Auth::user()->isMainUser() && $businessCard->user->parent_id == Auth::id())
                                <span class="badge badge-info">子帳號</span>
                            @endif
                        @else
                            <span class="text-muted">未知</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-secondary">{{ $businessCard->bubbles()->count() }}</span>
                    </td>
                    <td>
                        @if($businessCard->active)
                            <span class="badge badge-success">啟用</span>
                        @else
                            <span class="badge badge-secondary">停用</span>
                        @endif
                    </td>
                    <td>{{ $businessCard->created_at->format('Y-m-d H:i') }}</td>
                    <td width="120">
                        {!! Form::open(['route' => ['admin.businessCards.destroy', $businessCard->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('admin.businessCards.show', [$businessCard->id]) }}"
                               class='btn btn-default btn-sm'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.businessCards.edit', [$businessCard->id]) }}"
                               class='btn btn-default btn-sm'>
                                <i class="far fa-edit"></i>
                            </a>
                            <a href="{{ route('admin.businessCards.bubbles.index', [$businessCard->id]) }}"
                               class='btn btn-info btn-sm' title="管理電子名片-卡片">
                                <i class="fas fa-th-large"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'button', 'class' => 'btn btn-danger btn-sm', 'onclick' => "return check(this,'確定要刪除此電子名片嗎?')"]) !!}
                        </div>
                        {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            @include('adminlte-templates::common.paginate', ['records' => $businessCards])
        </div>
    </div>
</div>

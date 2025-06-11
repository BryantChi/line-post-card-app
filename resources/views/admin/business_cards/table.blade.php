<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="businessCards-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>名稱</th>
                <th>建立者</th>
                <th>數位名片-卡片數</th>
                <th>點閱率</th>
                <th>分享數</th>
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
                    {{-- 點閱率、分享數 --}}
                    <td>
                        <span class="badge badge-secondary">{{ $businessCard->views ?? 0 }}</span>
                    </td>
                    <td>
                        <span class="badge badge-secondary">{{ $businessCard->shares ?? 0 }}</span>
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
                               class='btn btn-default btn-md'
                               data-step="4" data-intro="點擊這裡查看數位名片的詳細資訊及預覽。">
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.businessCards.edit', [$businessCard->id]) }}"
                               class='btn btn-default btn-md'
                               data-step="5" data-intro="點擊這裡編輯數位名片的基本設定，例如標題、描述等。">
                                <i class="far fa-edit"></i>
                            </a>
                            <a href="{{ route('admin.businessCards.bubbles.index', [$businessCard->id]) }}"
                               class='btn btn-info btn-md' title="管理數位名片-卡片"
                               data-step="6" data-intro="點擊這裡管理此數位名片包含的「卡片」。您可以在此新增、編輯、排序或刪除卡片。">
                                <i class="fas fa-th-large"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'button', 'class' => 'btn btn-danger btn-md', 'onclick' => "return check(this,'確定要刪除此數位名片嗎?')", 'data-step' => "7", 'data-intro' => "點擊這裡刪除此數位名片。請注意，此操作無法復原。"]) !!}
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

<div class="table-responsive">
    <table class="table" id="aboutUsers-table">
        <thead>
        <tr>
            @if(Auth::user()->isSuperAdmin())
            <th width="30">
                <input type="checkbox" id="select-all" title="全選">
            </th>
            @endif
            <th>帳號</th>
            <th>Email</th>
            <th>登入次數</th>
            <th>最後登入時間</th>
            <th>備註</th>
            <th>到期日</th>
            <th>狀態</th>
            <th colspan="3">Action</th>
        </tr>
        </thead>
        <tbody>
        @foreach($subUsers as $subUser)
            <tr>
                @if(Auth::user()->isSuperAdmin())
                <td>
                    <input type="checkbox" class="user-checkbox" value="{{ $subUser->id }}">
                </td>
                @endif
                <td>{{ $subUser->name }}</td>
                <td class="min-w-300">{{ $subUser->email }}</td>
                <td>{{ $subUser->login_count ?? 0 }}</td>
                <td>{{ $subUser->last_login_at ? $subUser->last_login_at->format('Y-m-d H:i') : '尚未登入' }}</td>
                <td>{{ $subUser->remarks ?? '無' }}</td>
                <td>{{ ($subUser->expires_at ?? null) ? \Carbon\Carbon::parse($subUser->expires_at)->format('Y-m-d') : '無' }}</td>
                <td>{{ ($subUser->active ?? null) ? '是' : '否' }}</td>
                <td width="160">

                    {!! Form::open(['route' => ['sub-users.destroy', $subUser->id], 'method' => 'delete']) !!}

                    <div class='btn-group'>
                        @if(Auth::user()->isSuperAdmin())
                        <button type="button"
                                class='btn btn-info btn-sm login-report-single'
                                data-user-id="{{ $subUser->id }}"
                                title="下載登入紀錄">
                            <i class="fas fa-file-download"></i>
                        </button>
                        @endif

                        <a href="{{ route('sub-users.edit', [$subUser->id]) }}"
                           class='btn btn-default btn-sm'>
                            <i class="far fa-edit"></i>
                        </a>
                        {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'button', 'class' => 'btn btn-danger btn-sm js-confirm-delete', 'data-confirm' => '確定要刪除此子帳號嗎?']) !!}

                    </div>

                    {!! Form::close() !!}

                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

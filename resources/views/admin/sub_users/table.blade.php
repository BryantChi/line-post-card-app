<div class="table-responsive">
    <table class="table" id="aboutUsers-table">
        <thead>
        <tr>
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
                <td>{{ $subUser->name }}</td>
                <td style="min-width: 300px;">{{ $subUser->email }}</td>
                <td>{{ $subUser->login_count ?? 0 }}</td>
                <td>{{ $subUser->last_login_at ? $subUser->last_login_at->format('Y-m-d H:i') : '尚未登入' }}</td>
                <td>{{ $subUser->remarks ?? '無' }}</td>
                <td>{{ ($subUser->expires_at ?? null) ? \Carbon\Carbon::parse($subUser->expires_at)->format('Y-m-d') : '無' }}</td>
                <td>{{ ($subUser->active ?? null) ? '是' : '否' }}</td>
                <td width="120">

                    {!! Form::open(['route' => ['sub-users.destroy', $subUser->id], 'method' => 'delete']) !!}

                    <div class='btn-group'>
                        {{-- <a href="{{ route('sub-users.show', [$adminUser->id]) }}"
                           class='btn btn-default btn-md'>
                            <i class="far fa-eye"></i>
                        </a> --}}

                        <a href="{{ route('sub-users.edit', [$subUser->id]) }}"
                           class='btn btn-default btn-md'>
                            <i class="far fa-edit"></i>
                        </a>
                        {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'button', 'class' => 'btn btn-danger btn-md', 'onclick' => "return check(this)"]) !!}

                    </div>

                    {!! Form::close() !!}

                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

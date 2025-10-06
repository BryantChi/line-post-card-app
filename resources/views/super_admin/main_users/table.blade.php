<div class="table-responsive">
    <table class="table" id="aboutUsers-table">
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>登入次數</th>
            <th>最後登入時間</th>
            <th colspan="3">Action</th>
        </tr>
        </thead>
        <tbody>
        @foreach($mainUsers as $mainUser)
            <tr>
                <td>{{ $mainUser->name }}</td>
                <td style="min-width: 300px;">{{ $mainUser->email }}</td>
                <td>{{ $mainUser->login_count ?? 0 }}</td>
                <td>{{ $mainUser->last_login_at ? $mainUser->last_login_at->format('Y-m-d H:i') : '尚未登入' }}</td>
                <td width="120">

                    {!! Form::open(['route' => ['super_admin.mainUsers.destroy', $mainUser->id], 'method' => 'delete']) !!}

                    <div class='btn-group'>
                        {{-- <a href="{{ route('super_admin.mainUsers.show', [$adminUser->id]) }}"
                           class='btn btn-default btn-md'>
                            <i class="far fa-eye"></i>
                        </a> --}}

                        <a href="{{ route('super_admin.mainUsers.edit', [$mainUser->id]) }}"
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

<div class="table-responsive">
    <table class="table" id="aboutUsers-table">
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th colspan="3">Action</th>
        </tr>
        </thead>
        <tbody>
        @foreach($adminUsers as $adminUser)
            @if (Str::lower($adminUser->name) == 'bryant')
                @continue
            @endif
            <tr>
                <td>{{ $adminUser->name }}</td>
                <td style="min-width: 300px;">{{ $adminUser->email }}</td>
                <td width="120">

                    {!! Form::open(['route' => ['admin.adminUsers.destroy', $adminUser->id], 'method' => 'delete']) !!}

                    <div class='btn-group {{ $adminUser->id == 1 && Auth::user()->id != 1 ? 'd-none' : '' }}'>
                        {{-- <a href="{{ route('admin.adminUsers.show', [$adminUser->id]) }}"
                           class='btn btn-default btn-sm'>
                            <i class="far fa-eye"></i>
                        </a> --}}
                        @if (Auth::user()->id <= 2 || Auth::user()->id == $adminUser->id)
                        <a href="{{ route('admin.adminUsers.edit', [$adminUser->id]) }}"
                           class='btn btn-default btn-sm'>
                            <i class="far fa-edit"></i>
                        </a>
                        @endif
                        @if ((Auth::user()->id <= 2 && $adminUser->id != 1) || Auth::user()->id == $adminUser->id)
                        {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'button', 'class' => 'btn btn-danger btn-sm', 'onclick' => "return check(this)"]) !!}
                        @endif
                    </div>

                    {!! Form::close() !!}

                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

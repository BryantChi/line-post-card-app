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
        @foreach($mainUsers as $mainUser)
            <tr>
                <td>{{ $mainUser->name }}</td>
                <td style="min-width: 300px;">{{ $mainUser->email }}</td>
                <td width="120">

                    {!! Form::open(['route' => ['super_admin.mainUsers.destroy', $mainUser->id], 'method' => 'delete']) !!}

                    <div class='btn-group'>
                        {{-- <a href="{{ route('super_admin.mainUsers.show', [$adminUser->id]) }}"
                           class='btn btn-default btn-sm'>
                            <i class="far fa-eye"></i>
                        </a> --}}

                        <a href="{{ route('super_admin.mainUsers.edit', [$mainUser->id]) }}"
                           class='btn btn-default btn-sm'>
                            <i class="far fa-edit"></i>
                        </a>
                        {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'button', 'class' => 'btn btn-danger btn-sm', 'onclick' => "return check(this)"]) !!}

                    </div>

                    {!! Form::close() !!}

                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

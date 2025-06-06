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
        @foreach($subUsers as $subUser)
            <tr>
                <td>{{ $subUser->name }}</td>
                <td style="min-width: 300px;">{{ $subUser->email }}</td>
                <td width="120">

                    {!! Form::open(['route' => ['sub-users.destroy', $subUser->id], 'method' => 'delete']) !!}

                    <div class='btn-group'>
                        {{-- <a href="{{ route('sub-users.show', [$adminUser->id]) }}"
                           class='btn btn-default btn-sm'>
                            <i class="far fa-eye"></i>
                        </a> --}}

                        <a href="{{ route('sub-users.edit', [$subUser->id]) }}"
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

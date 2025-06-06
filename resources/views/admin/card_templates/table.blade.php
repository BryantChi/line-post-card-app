<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="card-templates-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Preview Image</th>
                <th>Template Schema</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($cardTemplates as $cardTemplates)
                <tr>
                    <td>{{ $cardTemplates->name }}</td>
                    <td>{{ $cardTemplates->description }}</td>
                    <td>{{ $cardTemplates->preview_image }}</td>
                    <td>
                        <pre>{{ json_encode($cardTemplates->template_schema, JSON_PRETTY_PRINT) }}</pre>
                    </td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['admin.cardTemplates.destroy', $cardTemplates->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('admin.cardTemplates.show', [$cardTemplates->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.cardTemplates.edit', [$cardTemplates->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-edit"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'button', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return check(this);"]) !!}
                        </div>
                        {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- <div class="card-footer clearfix">
        <div class="float-right">
            @include('adminlte-templates::common.paginate', ['records' => $cardTemplates])
        </div>
    </div> --}}
</div>

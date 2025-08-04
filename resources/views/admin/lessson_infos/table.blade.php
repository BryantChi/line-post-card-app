<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="lessson-infos-table">
            <thead>
            <tr>
                <th>編號</th>
                <th>圖片</th>
                <th>標題</th>
                <th>瀏覽次數</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach($lesssonInfos as $lesssonInfo)
                <tr>
                    <td>{{ $lesssonInfo->num }}</td>
                    <td>
                        @if($lesssonInfo->image)
                            <img src="{{ asset('uploads/' . $lesssonInfo->image) }}" alt="圖片" style="max-width: 100px; max-height: 100px;">
                        @else
                            <span class="text-muted">無圖片</span>
                        @endif
                    </td>
                    <td>{{ $lesssonInfo->title }}</td>
                    <td>{{ $lesssonInfo->views }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['admin.lessonInfos.destroy', $lesssonInfo->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            {{-- <a href="{{ route('admin.lessonInfos.show', [$lesssonInfo->id]) }}"
                               class='btn btn-default btn-sm'>
                                <i class="far fa-eye"></i>
                            </a> --}}
                            <a href="{{ route('admin.lessonInfos.edit', [$lesssonInfo->id]) }}"
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

    <div class="card-footer clearfix">
        <div class="float-right">
            @include('adminlte-templates::common.paginate', ['records' => $lesssonInfos])
        </div>
    </div>
</div>

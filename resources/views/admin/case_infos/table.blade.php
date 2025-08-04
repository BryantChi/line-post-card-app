<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="case-infos-table">
            <thead>
            <tr>
                <th>封面</th>
                <th>標題</th>
                <th>分享連結</th>
                <th>瀏覽次數</th>
                <th>狀態</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach($caseInfos as $caseInfo)
                <tr>
                    <td>
                        @if($caseInfo->businessCard && $caseInfo->businessCard->profile_image)
                            <img src="{{ asset('uploads/' . $caseInfo->businessCard->profile_image) }}" alt="封面圖片" style="max-width: 100px; max-height: 100px;">
                        @else
                            <span class="text-muted">無封面圖片</span>
                        @endif
                    </td>
                    <td>{{ $caseInfo->name }}</td>
                    <td>
                        @if($caseInfo->businessCard)
                            <a href="{{ $caseInfo->businessCard->getShareUrl() }}" target="_blank" class="text-primary">{{ $caseInfo->businessCard->uuid }}</a>
                        @else
                            <span class="text-muted">無分享連結</span>
                        @endif
                    </td>
                    <td>{{ $caseInfo->businessCard->views ?? 0 }}</td>
                    <td>
                        @if($caseInfo->status)
                            <span class="badge badge-success">啟用</span>
                        @else
                            <span class="badge badge-secondary">停用</span>
                        @endif
                    </td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['admin.caseInfos.destroy', $caseInfo->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            {{-- <a href="{{ route('admin.caseInfos.show', [$caseInfo->id]) }}"
                               class='btn btn-default btn-sm'>
                                <i class="far fa-eye"></i>
                            </a> --}}
                            <a href="{{ route('admin.caseInfos.edit', [$caseInfo->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $caseInfos])
        </div>
    </div>
</div>

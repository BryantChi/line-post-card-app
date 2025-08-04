<div class="table-responsive">
    <table class="table" id="seoSettings-table">
        <thead>
            <tr>
                <th>Url</th>
                <th>Title</th>
                <th>Description</th>
                <th>Keywords</th>
                <th>Og:title</th>
                <th>Og:description</th>
                <th>Og:site Name</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($seoSettings as $seoSetting)
                <tr>
                    <td>{{ $seoSetting->url }}</td>
                    <td style="min-width: 300px;">{{ $seoSetting->title }}</td>
                    <td style="min-width: 300px;">{{ $seoSetting->description }}</td>
                    <td style="min-width: 300px;">{{ $seoSetting->keywords }}</td>
                    <td style="min-width: 300px;">{{ $seoSetting->og_title }}</td>
                    <td style="min-width: 300px;">{{ $seoSetting->og_description }}</td>
                    <td style="min-width: 300px;">{{ $seoSetting->og_site_name }}</td>
                    <td width="120">
                        {!! Form::open(['route' => ['admin.seoSettings.destroy', $seoSetting->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            {{-- <a href="{{ route('admin.seoSettings.show', [$seoSetting->id]) }}"
                                class='btn btn-default btn-sm'>
                                <i class="far fa-eye"></i>
                            </a> --}}
                            <a href="{{ route('admin.seoSettings.edit', [$seoSetting->id]) }}"
                                class='btn btn-default btn-sm'>
                                <i class="far fa-edit"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt"></i>', [
                                'type' => 'button',
                                'class' => 'btn btn-danger btn-sm',
                                'onclick' => 'return check(this)',
                            ]) !!}
                        </div>
                        {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

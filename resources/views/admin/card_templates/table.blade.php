<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="card-templates-table">
            <thead>
            <tr>
                <th>模板名稱</th>
                <th>模板描述</th>
                <th>預覽圖片</th>
                <th>Flex Message 預覽</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($cardTemplates as $cardTemplates)
                <tr>
                    <td>{{ $cardTemplates->name }}</td>
                    <td>{{ $cardTemplates->description }}</td>
                    <td>
                        <img src="{{ asset('uploads/' . $cardTemplates->preview_image) }}" alt="">
                    </td>
                    <td>
                        <button class="btn btn-sm btn-info mb-2 toggle-json-btn" data-target="json-{{ $cardTemplates->id }}">查看 JSON</button>
                        <pre id="json-{{ $cardTemplates->id }}" style="display: none;">{{ json_encode($cardTemplates->template_schema, JSON_PRETTY_PRINT) }}</pre>
                        <div class="border p-3 flex-preview-container">
                            <div id="flex-root-{{ $cardTemplates->id }}" class="flex-root" data-schema="{{ htmlspecialchars(json_encode($cardTemplates->template_schema), ENT_QUOTES, 'UTF-8') }}"></div>
                        </div>
                    </td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['admin.cardTemplates.destroy', $cardTemplates->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            {{-- <a href="{{ route('admin.cardTemplates.show', [$cardTemplates->id]) }}"
                               class='btn btn-default btn-sm'>
                                <i class="far fa-eye"></i>
                            </a> --}}
                            <a href="{{ route('admin.cardTemplates.edit', [$cardTemplates->id]) }}"
                               class='btn btn-default btn-sm'>
                                <i class="far fa-edit"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'button', 'class' => 'btn btn-danger btn-sm', 'onclick' => "return check(this);"]) !!}
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

@push('page_css')
<link rel="stylesheet" href="{{ asset('assets/css/renderer.css') }}">
<style>
    .flex-preview-container {
        background-color: #f5f5f5;
        max-width: 400px;
        overflow-x: auto;
    }
    .toggle-json-btn {
        cursor: pointer;
    }
    pre {
        max-height: 200px;
        overflow: auto;
        background-color: #f8f9fa;
        padding: 10px;
        border-radius: 4px;
    }
</style>
@endpush

@push('page_scripts')
<script src="{{ asset('js/renderer.js') }}"></script>
<script>
    $(document).ready(function() {
        // 切換 JSON 顯示/隱藏
        $('.toggle-json-btn').on('click', function() {
            const targetId = $(this).data('target');
            $('#' + targetId).toggle();

            if ($('#' + targetId).is(':visible')) {
                $(this).text('隱藏 JSON');
            } else {
                $(this).text('查看 JSON');
            }
        });

        // 渲染所有 Flex Message 預覽
        $('.flex-root').each(function() {
            const $root = $(this);
            const schemaStr = $root.data('schema');

            try {
                // 解析 schema
                let schema = decodeAndParseJSON(schemaStr);

                // 確認是否為 carousel 格式，如果不是則轉換為 carousel 格式
                if (schema && schema.type !== 'carousel') {
                    // 將單一 bubble 包裝成 carousel 格式
                    schema = {
                        type: "carousel",
                        direction: "ltr",
                        contents: [schema]
                    };
                }

                // 渲染 Flex 組件
                const rendered = renderFlexComponent(schema, "");
                if (rendered) {
                    $root.append(rendered);
                } else {
                    $root.html('<div class="alert alert-warning">無法渲染 Flex 訊息</div>');
                }
            } catch (error) {
                console.error('渲染 Flex 訊息失敗:', error);
                $root.html('<div class="alert alert-danger">渲染失敗: ' + error.message + '</div>');
            }
        });

        // 強大的 JSON 解析函數 (從 fields.blade.php 複製過來)
        function decodeAndParseJSON(input) {
            // 如果輸入已經是物件，直接返回
            if (typeof input === 'object') return input;

            // 嘗試不同的解析方法
            let result;

            // 方法 1: 直接解析
            try {
                result = JSON.parse(input);
                return result;
            } catch (e) {
                console.log('方法 1 失敗:', e);
            }

            // 方法 2: 解碼 HTML 實體後解析
            try {
                const decoded = $('<div/>').html(input).text();
                result = JSON.parse(decoded);
                return result;
            } catch (e) {
                console.log('方法 2 失敗:', e);
            }

            // 方法 3: 解碼 Unicode 後解析
            try {
                const decodedUnicode = input.replace(/\\u([0-9a-fA-F]{4})/g, function(match, hex) {
                    return String.fromCharCode(parseInt(hex, 16));
                });
                result = JSON.parse(decodedUnicode);
                return result;
            } catch (e) {
                console.log('方法 3 失敗:', e);
            }

            // 方法 4: 移除額外轉義字元後解析
            try {
                let cleaned = input.replace(/\\\\/g, '\\')
                             .replace(/\\"/g, '"');

                // 如果字串被引號包圍，去除這些引號
                if (cleaned.startsWith('"') && cleaned.endsWith('"')) {
                    cleaned = cleaned.substring(1, cleaned.length - 1);
                }

                result = JSON.parse(cleaned);
                return result;
            } catch (e) {
                console.log('方法 4 失敗:', e);
            }

            // 如果所有方法都失敗，則拋出錯誤
            throw new Error('無法解析 JSON 字串');
        }
    });
</script>
@endpush

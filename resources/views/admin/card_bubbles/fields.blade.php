
{{-- 左側：模板清單 --}}
<div class="col-md-4">
    <h5>選擇氣泡模板</h5>
    <div class="list-group">
        @foreach ($templates as $template)
            <a href="javascript:void(0);" class="list-group-item list-group-item-action template-item"
               data-id="{{ $template->id }}"
               data-schema="{{ htmlspecialchars(json_encode($template->template_schema), ENT_QUOTES, 'UTF-8') }}"
               data-fields="{{ htmlspecialchars(json_encode($template->editable_fields), ENT_QUOTES, 'UTF-8') }}"
               data-preview-url="{{ asset('uploads/' . $template->preview_image) }}">
                <img src="{{ asset('uploads/' . $template->preview_image) }}" class="img-fluid mb-2"
                     alt="{{ $template->name }}">
                <p>{{ $template->name }}</p>
            </a>
        @endforeach
    </div>
</div>

{{-- 右側：即時預覽 --}}
<div class="col-md-4">
    <h5>卡片預覽</h5>
    <div id="livePreview" class="border p-3">
        <div id="flex-root"></div>
    </div>
</div>

<!-- 隱藏的模板 ID 欄位 -->
<input type="hidden" name="template_id" id="template_id" value="{{ isset($bubble) ? $bubble->template_id : '' }}">

<!-- 基本欄位 -->
<div class="col-md-12 mt-4">
    <div class="card">
        <div class="card-header">
            <h5>基本設定</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- 標題 -->
                <div class="form-group col-sm-6">
                    {!! Form::label('title', '標題:') !!}
                    {!! Form::text('title', isset($bubble) ? $bubble->title : null, ['class' => 'form-control', 'required', 'maxlength' => 255]) !!}
                </div>

                <!-- 副標題 -->
                <div class="form-group col-sm-6">
                    {!! Form::label('subtitle', '副標題:') !!}
                    {!! Form::text('subtitle', isset($bubble) ? $bubble->subtitle : null, ['class' => 'form-control', 'maxlength' => 255]) !!}
                </div>

                <!-- 圖片 -->
                <div class="form-group col-sm-6">
                    {!! Form::label('image', '圖片:') !!}
                    <div class="input-group">
                        <div class="custom-file">
                            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
                            {!! Form::label('image', '選擇檔案', ['class' => 'custom-file-label']) !!}
                        </div>
                    </div>
                    @if(isset($bubble) && $bubble->image)
                        <div class="mt-2">
                            <img src="{{ asset('uploads/' . $bubble->image) }}" style="max-height: 100px" class="img-thumbnail">
                        </div>
                    @endif
                </div>

                <!-- 內容 -->
                <div class="form-group col-sm-12">
                    {!! Form::label('content', '內容:') !!}
                    {!! Form::textarea('content', isset($bubble) ? $bubble->content : null, ['class' => 'form-control', 'rows' => 3]) !!}
                </div>

                <!-- 啟用狀態 -->
                <div class="form-group col-sm-12">
                    <div class="form-check">
                        {!! Form::checkbox('active', '1', isset($bubble) ? $bubble->active : true, ['class' => 'form-check-input']) !!}
                        {!! Form::label('active', '啟用此氣泡卡片', ['class' => 'form-check-label']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 動態欄位 -->
<div class="col-md-12 mt-4">
    <div class="card">
        <div class="card-header">
            <h5>模板欄位</h5>
        </div>
        <div class="card-body">
            <div id="dynamicFields">
                <p class="text-muted">請先選擇模板</p>
            </div>
        </div>
    </div>
</div>

@push('page_scripts')
<script>
    $(document).ready(function() {
        // 當選擇模板時
        $('.template-item').on('click', function() {
            let schema = $(this).data('schema');
            let fields = $(this).data('fields');
            const previewUrl = $(this).data('preview-url');
            const templateId = $(this).data('id');

            // 解析 schema 和 fields
            if (typeof schema === 'string') {
                try {
                    schema = decodeAndParseJSON(schema);
                } catch (error) {
                    console.error('無法解析 schema:', error);
                    alert('模板資料解析失敗，請聯繫管理員');
                    return;
                }
            }

            if (typeof fields === 'string') {
                try {
                    fields = decodeAndParseJSON(fields);
                } catch (error) {
                    console.error('無法解析 fields:', error);
                    fields = {};
                }
            }

            console.log('成功解析的 schema:', schema);
            console.log('成功解析的 fields:', fields);

            // 更新隱藏的 template_id 欄位
            $('#template_id').val(templateId);

            // 渲染 LINE Flex Message 預覽
            renderFlexPreview(schema);

            // 動態生成欄位
            $('#dynamicFields').empty();

            // 如果模板有定義欄位，優先使用
            if (fields && Object.keys(fields).length > 0) {
                Object.keys(fields).forEach(fieldKey => {
                    const field = fields[fieldKey];
                    let fieldHtml = '';

                    switch(field.type) {
                        case 'image':
                            fieldHtml = `
                                <div class="form-group col-sm-6">
                                    <label for="${fieldKey}">${field.label || fieldKey}:</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="${fieldKey}" class="custom-file-input" id="${fieldKey}">
                                            <label class="custom-file-label" for="${fieldKey}">選擇檔案</label>
                                        </div>
                                    </div>
                                </div>
                            `;
                            break;
                        case 'textarea':
                            fieldHtml = `
                                <div class="form-group col-sm-12">
                                    <label for="${fieldKey}">${field.label || fieldKey}:</label>
                                    <textarea name="${fieldKey}" id="${fieldKey}" class="form-control" rows="3">${field.default || ''}</textarea>
                                </div>
                            `;
                            break;
                        case 'email':
                            fieldHtml = `
                                <div class="form-group col-sm-6">
                                    <label for="${fieldKey}">${field.label || fieldKey}:</label>
                                    <input type="email" name="${fieldKey}" id="${fieldKey}" class="form-control" value="${field.default || ''}">
                                </div>
                            `;
                            break;
                        case 'phone':
                            fieldHtml = `
                                <div class="form-group col-sm-6">
                                    <label for="${fieldKey}">${field.label || fieldKey}:</label>
                                    <input type="tel" name="${fieldKey}" id="${fieldKey}" class="form-control" value="${field.default || ''}">
                                </div>
                            `;
                            break;
                        case 'url':
                            fieldHtml = `
                                <div class="form-group col-sm-6">
                                    <label for="${fieldKey}">${field.label || fieldKey}:</label>
                                    <input type="url" name="${fieldKey}" id="${fieldKey}" class="form-control" value="${field.default || ''}">
                                </div>
                            `;
                            break;
                        default: // text
                            fieldHtml = `
                                <div class="form-group col-sm-6">
                                    <label for="${fieldKey}">${field.label || fieldKey}:</label>
                                    <input type="text" name="${fieldKey}" id="${fieldKey}" class="form-control" value="${field.default || ''}">
                                </div>
                            `;
                            break;
                    }

                    $('#dynamicFields').append(fieldHtml);
                });
            } else {
                // 否則從 schema 中提取變數
                const placeholders = extractPlaceholders(schema);
                console.log('提取到的變數:', placeholders);

                placeholders.forEach(field => {
                    if (field === 'title' || field === 'subtitle' || field === 'content' || field === 'image') {
                        return; // 跳過基本欄位
                    }

                    let fieldHtml = '';
                    if (field.includes('image') || field.includes('picture') || field.includes('photo')) {
                        fieldHtml = `
                            <div class="form-group col-sm-6">
                                <label for="${field}">${field}:</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" name="${field}" class="custom-file-input" id="${field}">
                                        <label class="custom-file-label" for="${field}">選擇檔案</label>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else if (field.includes('url') || field.includes('link')) {
                        fieldHtml = `
                            <div class="form-group col-sm-6">
                                <label for="${field}">${field}:</label>
                                <input type="url" name="${field}" id="${field}" class="form-control">
                            </div>
                        `;
                    } else if (field.includes('email')) {
                        fieldHtml = `
                            <div class="form-group col-sm-6">
                                <label for="${field}">${field}:</label>
                                <input type="email" name="${field}" id="${field}" class="form-control">
                            </div>
                        `;
                    } else if (field.includes('phone') || field.includes('tel')) {
                        fieldHtml = `
                            <div class="form-group col-sm-6">
                                <label for="${field}">${field}:</label>
                                <input type="tel" name="${field}" id="${field}" class="form-control">
                            </div>
                        `;
                    } else if (field.includes('content') || field.includes('description')) {
                        fieldHtml = `
                            <div class="form-group col-sm-12">
                                <label for="${field}">${field}:</label>
                                <textarea name="${field}" id="${field}" class="form-control" rows="3"></textarea>
                            </div>
                        `;
                    } else {
                        fieldHtml = `
                            <div class="form-group col-sm-6">
                                <label for="${field}">${field}:</label>
                                <input type="text" name="${field}" id="${field}" class="form-control">
                            </div>
                        `;
                    }

                    $('#dynamicFields').append(fieldHtml);
                });
            }

            // 如果沒有動態欄位
            if ($('#dynamicFields').children().length === 0) {
                $('#dynamicFields').html('<p class="text-muted">此模板沒有可編輯的欄位</p>');
            }

            // 重新初始化 custom-file-input
            bsCustomFileInput.init();
        });

        // 提取 schema 中的變數
        function extractPlaceholders(obj) {
            const placeholders = new Set();

            function traverse(o) {
                if (!o || typeof o !== 'object') return;

                for (const key in o) {
                    if (Object.prototype.hasOwnProperty.call(o, key)) {
                        if (typeof o[key] === 'object' && o[key] !== null) {
                            traverse(o[key]);
                        } else if (typeof o[key] === 'string') {
                            // 使用正則表達式尋找所有 /{/{變數/}/} 格式的占位符
                            const regex = /\{\{([^}]+)\}\}/g;
                            let match;
                            while ((match = regex.exec(o[key])) !== null) {
                                // 將匹配到的名稱添加到 Set 中
                                const placeholder = match[1].trim();
                                placeholders.add(placeholder);
                            }
                        }
                    }
                }
            }

            traverse(obj);
            return Array.from(placeholders);
        }

        // Flex Message 預覽渲染
        function renderFlexPreview(schema) {
            // 清空預覽容器
            $('#livePreview').empty().append('<div id="flex-root"></div>');

            // 1. 載入渲染器樣式
            if (!document.getElementById('renderer-css')) {
                const rendererCss = document.createElement('link');
                rendererCss.id = 'renderer-css';
                rendererCss.rel = 'stylesheet';
                rendererCss.href = '{{ asset("assets/css/renderer.css") }}';
                document.head.appendChild(rendererCss);
            }

            // 2. 載入渲染器腳本
            $.getScript('{{ asset("js/renderer.js") }}', function() {
                try {
                    // 確認是否為 carousel 格式，如果不是則轉換為 carousel 格式
                    let flexJson = schema;
                    if (flexJson && flexJson.type !== 'carousel') {
                        // 將單一 bubble 包裝成 carousel 格式
                        flexJson = {
                            type: "carousel",
                            direction: "ltr",
                            contents: [flexJson]
                        };
                    }

                    // 3. 渲染 Flex 組件
                    const root = document.getElementById("flex-root");
                    if (root) {
                        const rendered = renderFlexComponent(flexJson, "");
                        if (rendered) {
                            root.appendChild(rendered);
                        } else {
                            $('#flex-root').html('<div class="alert alert-warning">無法渲染 Flex 訊息</div>');
                        }
                    }
                } catch (error) {
                    console.error('渲染 Flex 訊息失敗:', error);
                    $('#flex-root').html('<div class="alert alert-danger">渲染失敗: ' + error.message + '</div>');
                }
            });
        }

        // 強大的 JSON 解析函數
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

        // 預設選擇模板 (如果是編輯模式)
        @if(isset($bubble) && $bubble->template_id)
            $('.template-item[data-id="{{ $bubble->template_id }}"]').click();

            // 預先填充欄位值
            @if(isset($bubble->bubble_data) && is_array($bubble->bubble_data))
                @foreach($bubble->bubble_data as $key => $value)
                    setTimeout(function() {
                        $('input[name="{{ $key }}"], textarea[name="{{ $key }}"]').val('{{ $value }}');
                    }, 500);
                @endforeach
            @endif
        @else
            // 如果是新建模式，選擇第一個模板
            if ($('.template-item').length > 0) {
                $('.template-item:first').click();
            }
        @endif
    });
</script>
@endpush

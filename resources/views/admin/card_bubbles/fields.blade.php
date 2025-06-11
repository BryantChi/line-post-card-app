{{-- 左側：模板清單 --}}
<div class="col-md-4 template-list">
    <h5>選擇電子名片-卡片模板</h5>
    <div class="list-group" style="max-height: 500px; overflow-y: auto;">
        @foreach ($templates as $template)
            <a href="javascript:void(0);" class="list-group-item list-group-item-action template-item"
                data-id="{{ $template->id }}"
                data-schema="{{ htmlspecialchars(json_encode($template->template_schema), ENT_QUOTES, 'UTF-8') }}"
                data-fields="{{ htmlspecialchars(json_encode($template->editable_fields), ENT_QUOTES, 'UTF-8') }}"
                data-preview-url="{{ asset('uploads/' . $template->preview_image) }}"
                data-share-url="{{ $shareUrl }}">
                <img src="{{ asset('uploads/' . $template->preview_image) }}" class="img-fluid mb-2"
                    style="max-height: 300px;" alt="{{ $template->name }}"
                    onerror="this.onerror=null; this.src='{{ asset('images/no-image.png') }}'; this.classList.add('img-placeholder');">
                <p>{{ $template->name }}</p>
            </a>
        @endforeach
    </div>
</div>

{{-- 右側：即時預覽 --}}
<div class="col-md-4">
    <h5>卡片預覽</h5>
    <div id="livePreview" class="border p-3" style="min-width: 400px; min-height: 500px;overflow: auto;">
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
                    {!! Form::text('title', isset($bubble) ? $bubble->title : null, [
                        'class' => 'form-control',
                        'required',
                        'maxlength' => 255,
                    ]) !!}
                </div>

                <!-- 副標題 -->
                <div class="form-group col-sm-6">
                    {!! Form::label('subtitle', '副標題:') !!}
                    {!! Form::text('subtitle', isset($bubble) ? $bubble->subtitle : null, [
                        'class' => 'form-control',
                        'maxlength' => 255,
                    ]) !!}
                </div>

                <!-- 圖片 -->
                <div class="form-group col-sm-6">
                    {!! Form::label('image', '圖片:') !!}
                    <div class="input-group">
                        <div class="custom-file">
                            {!! Form::file('image', ['class' => 'custom-file-input', 'id' => 'image_input', 'accept' => 'image/*']) !!}
                            {!! Form::label('image', '選擇檔案', ['class' => 'custom-file-label']) !!}
                        </div>
                    </div>
                    <div class="mt-2">
                        @if (isset($bubble) && $bubble->image)
                            <img src="{{ asset('uploads/' . $bubble->image) }}" id="image_preview"
                                style="max-height: 100px" class="img-thumbnail">
                        @else
                            <img src="" id="image_preview" style="max-height: 100px; display: none;"
                                class="img-thumbnail">
                        @endif
                    </div>
                </div>

                <!-- 內容 -->
                <div class="form-group col-sm-12">
                    {!! Form::label('content', '內容:') !!}
                    {!! Form::textarea('content', isset($bubble) ? $bubble->content : null, [
                        'class' => 'form-control',
                        'rows' => 3,
                    ]) !!}
                </div>

                <!-- 啟用狀態 -->
                <div class="form-group col-sm-12">
                    <div class="form-check">
                        {!! Form::checkbox('active', '1', isset($bubble) ? $bubble->active : true, ['class' => 'form-check-input']) !!}
                        {!! Form::label('active', '啟用此電子名片-卡片', ['class' => 'form-check-label']) !!}
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
            <p class="text-muted">以下欄位皆為必填</p>
        </div>
        <div class="card-body">
            <div id="dynamicFields" style="max-height: 500px; overflow-y: auto;">
                <p class="text-muted">請先選擇模板</p>
            </div>
        </div>
    </div>
</div>

@push('page_scripts')
    <!-- 引入 Pickr 函式庫 (示範) -->
    <script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr@1.8.2/dist/pickr.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr@1.8.2/dist/themes/classic.min.css">

    <script>
        $(document).ready(function() {
            // 初始化 Bootstrap 自訂檔案輸入
            bsCustomFileInput.init();

            // 常數定義
            const SHARE_CARD_LABEL = '分享名片給朋友';

            // 尋找分享動作的佔位符欄位
            function findShareActionField(schema) {
                let foundField = null;

                function traverse(obj) {
                    if (!obj || typeof obj !== 'object') return;

                    // 檢查是否為 URI action 物件
                    if (obj.type === 'uri' && obj.label === SHARE_CARD_LABEL && typeof obj.uri === 'string') {
                        const match = obj.uri.match(/\{\{([^}]+)\}\}/);
                        if (match && match[1]) {
                            foundField = match[1].trim();
                            return;
                        }
                    }

                    // 檢查是否為包含 action 屬性的物件
                    if (obj.action && typeof obj.action === 'object' && obj.action.type === 'uri' &&
                        obj.action.label === SHARE_CARD_LABEL && typeof obj.action.uri === 'string') {
                        const match = obj.action.uri.match(/\{\{([^}]+)\}\}/);
                        if (match && match[1]) {
                            foundField = match[1].trim();
                            return;
                        }
                    }

                    // 遞迴處理陣列
                    if (Array.isArray(obj)) {
                        for (let i = 0; i < obj.length; i++) {
                            traverse(obj[i]);
                            if (foundField) return;
                        }
                        return;
                    }

                    // 遞迴處理物件屬性
                    for (const key in obj) {
                        if (Object.prototype.hasOwnProperty.call(obj, key)) {
                            traverse(obj[key]);
                            if (foundField) return;
                        }
                    }
                }

                traverse(schema);
                return foundField;
            }

            // 圖片預覽功能
            $('#image_input').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#image_preview').attr('src', e.target.result);
                        $('#image_preview').show();
                    }
                    reader.readAsDataURL(file);
                } else {
                    $('#image_preview').attr('src', '');
                    $('#image_preview').hide();
                }
            });

            // 當選擇模板時
            $('.template-item').on('click', function() {
                let schema = $(this).data('schema');
                let fields = $(this).data('fields');
                const previewUrl = $(this).data('preview-url');
                const templateId = $(this).data('id');
                const shareUrl = $(this).data('share-url');

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

                // 尋找分享名片的欄位
                const shareFieldName = findShareActionField(schema);
                console.log('分享名片欄位名稱:', shareFieldName);

                // 取得分享網址
                const businessCardShareUrl = shareUrl;
                console.log('分享網址:', businessCardShareUrl);

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
                        // 檢查是否為分享名片欄位
                        const isShareField = (fieldKey === shareFieldName);
                        const fieldValue = isShareField ? businessCardShareUrl : (field.default ||
                            '');

                        switch (field.type) {
                            case 'image':
                                fieldHtml = `
                                <div class="form-group col-sm-6">
                                    <label for="${fieldKey}">${field.label || fieldKey}:</label>
                                    <div class="input-group input-group-${fieldKey} input-group-dynamic">
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
                                    <textarea name="${fieldKey}" id="${fieldKey}" class="form-control" rows="3">${fieldValue}</textarea>
                                </div>
                            `;
                                break;
                            case 'email':
                                fieldHtml = `
                                <div class="form-group col-sm-6">
                                    <label for="${fieldKey}">${field.label || fieldKey}:</label>
                                    <input type="email" name="${fieldKey}" id="${fieldKey}" class="form-control" value="${fieldValue}">
                                </div>
                            `;
                                break;
                            case 'phone':
                                fieldHtml = `
                                <div class="form-group col-sm-6">
                                    <label for="${fieldKey}">${field.label || fieldKey}:</label>
                                    <input type="tel" name="${fieldKey}" id="${fieldKey}" class="form-control" value="${fieldValue}">
                                </div>
                            `;
                                break;
                            case 'url':
                                fieldHtml = `
                                <div class="form-group col-sm-6">
                                    <label for="${fieldKey}">${field.label || fieldKey}:</label>
                                    <input type="url" name="${fieldKey}" id="${fieldKey}" class="form-control" value="${fieldValue}">
                                </div>
                            `;
                                break;
                            case 'color':
                                fieldHtml = `
                                <div class="form-group col-sm-6">
                                    <label for="${fieldKey}">${field.label || fieldKey}:</label>
                                    <input type="text" name="${fieldKey}" id="${fieldKey}" class="form-control color-picker" value="${fieldValue}">
                                </div>
                            `;
                                break;
                            default: // text
                                fieldHtml = `
                                <div class="form-group col-sm-6">
                                    <label for="${fieldKey}">${field.label || fieldKey}:</label>
                                    <input type="text" name="${fieldKey}" id="${fieldKey}" class="form-control" value="${fieldValue}">
                                </div>
                            `;
                                break;
                        }

                        $('#dynamicFields').append(fieldHtml);

                        // 如果是分享欄位，添加提示
                        if (isShareField) {
                            $(`#${fieldKey}`).after(
                                `<small class="form-text text-muted">此欄位自動填入分享連結，無需修改</small>`);
                            // 可選：禁用該欄位，防止用戶修改
                            $(`#${fieldKey}`).prop('readonly', true);
                        }
                    });
                } else {
                    // 否則從 schema 中提取變數
                    const placeholders = extractPlaceholders(schema);
                    console.log('提取到的變數:', placeholders);

                    placeholders.forEach(field => {
                        if (field === 'title' || field === 'subtitle' || field === 'content' ||
                            field === 'image') {
                            return; // 跳過基本欄位
                        }

                        // 檢查是否為分享名片欄位
                        const isShareField = (field === shareFieldName);
                        const fieldValue = isShareField ? businessCardShareUrl : '';

                        let fieldHtml = '';
                        if (field.includes('image') || field.includes('picture') || field.includes(
                                'photo')) {
                            fieldHtml = `
                            <div class="form-group col-sm-6">
                                <label for="${field}">${field}:</label>
                                <div class="input-group input-group-${field} input-group-dynamic">
                                    <div class="custom-file">
                                        <input type="file" name="${field}" class="custom-file-input" id="${field}">
                                        <label class="custom-file-label" for="${field}">選擇檔案</label>
                                    </div>
                                </div>
                            </div>
                        `;
                        } else if (field.includes('url') || field.includes('link') ||
                            isShareField) {
                            fieldHtml = `
                            <div class="form-group col-sm-6">
                                <label for="${field}">${field}:</label>
                                <input type="url" name="${field}" id="${field}" class="form-control" value="${fieldValue}">
                            </div>
                        `;
                        } else if (field.includes('email')) {
                            fieldHtml = `
                            <div class="form-group col-sm-6">
                                <label for="${field}">${field}:</label>
                                <input type="email" name="${field}" id="${field}" class="form-control" value="${fieldValue}">
                            </div>
                        `;
                        } else if (field.includes('phone') || field.includes('tel')) {
                            fieldHtml = `
                            <div class="form-group col-sm-6">
                                <label for="${field}">${field}:</label>
                                <input type="tel" name="${field}" id="${field}" class="form-control" value="${fieldValue}">
                            </div>
                        `;
                        } else if (field.includes('content') || field.includes('description')) {
                            fieldHtml = `
                            <div class="form-group col-sm-12">
                                <label for="${field}">${field}:</label>
                                <textarea name="${field}" id="${field}" class="form-control" rows="3">${fieldValue}</textarea>
                            </div>
                        `;
                        } else {
                            fieldHtml = `
                            <div class="form-group col-sm-6">
                                <label for="${field}">${field}:</label>
                                <input type="text" name="${field}" id="${field}" class="form-control" value="${fieldValue}">
                            </div>
                        `;
                        }

                        $('#dynamicFields').append(fieldHtml);

                        // 如果是分享欄位，添加提示
                        if (isShareField) {
                            $(`#${field}`).after(
                                `<small class="form-text text-muted">此欄位自動填入分享連結，無需修改</small>`);
                            // 可選：禁用該欄位，防止用戶修改
                            $(`#${field}`).prop('readonly', true);
                        }
                    });
                }

                // 多個圖片欄位，顯示圖片，並綁定檔案讀取事件，並更新自定義檔案標籤
                $('.input-group-dynamic input[type="file"]').each(function() {
                    const $field = $(this);
                    const $fieldInputGroup = $field.closest('.input-group-dynamic');
                    const img = $(
                        `<img src="" alt="${$field.attr('name')}" style="max-width: 100px; max-height: 100px; margin-top: 10px;">`
                    );
                    // 如果已經有圖片，則不重複添加
                    if ($fieldInputGroup.find('img').length == 0) {
                        img.insertAfter($fieldInputGroup);
                    } else {
                        $fieldInputGroup.find('img').attr('src',
                            '{{ url('assets/admin/img/ci.png') }}');
                    }

                    // 綁定檔案讀取事件
                    $field.on('change', function(e) {
                        const file = e.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                img.attr('src', e.target.result);
                            };
                            reader.readAsDataURL(file);
                        } else {
                            img.attr('src', '');
                        }
                    });
                });


                // 初始化所有顏色選擇器
                $('.color-picker').each(function() {
                    const $field = $(this);
                    // 如果已經有顏色選擇器，則不重複創建
                    if ($field.next('.color-swatch').length > 0) return;

                    try {
                        // 創建顏色選擇器
                        $field.after(
                            '<div class="color-swatch" style="display:inline-block; width: 100%; height: 30px; border: 1px solid #ccc; margin-top: 5px;"></div>'
                        );

                        // 確保元素已添加到 DOM
                        setTimeout(() => {
                            const colorSwatch = $field.next('.color-swatch')[0];
                            if (!colorSwatch || !colorSwatch.parentNode) return;

                            $field.val($field.val() || '#000000'); // 確保有初始值
                            const pickr = Pickr.create({
                                el: colorSwatch, // 使用原生 DOM 元素
                                theme: 'classic',
                                default: $field.val() || '#000000',
                                components: {
                                    preview: true,
                                    opacity: true,
                                    hue: true,
                                    interaction: {
                                        input: true,
                                        save: true
                                    }
                                }
                            });

                            pickr.on('save', (color) => {
                                const hexColor = color.toHEXA().toString();
                                $field.val(hexColor);
                                pickr.hide();
                            });

                            // 顯示顏色選擇器
                            $field.next('.color-swatch').on('click', function() {
                                pickr.show();
                            });

                            $field.on('change', function() {
                                // 當輸入框變更時，更新顏色選擇器的顏色
                                const color = $field.val();
                                pickr.setColor(color);
                            });
                        }, 50); // 給予DOM時間更新
                    } catch (error) {
                        console.error('初始化顏色選擇器失敗:', error);
                    }
                });

                // 如果沒有動態欄位
                if ($('#dynamicFields').children().length === 0) {
                    $('#dynamicFields').html('<p class="text-muted">此模板沒有可編輯的欄位</p>');
                }

                // 重新初始化 custom-file-input
                bsCustomFileInput.init();

                handleImageErrors();
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
                $('#livePreview').empty();

                $('#livePreview').append('<div id="flex-root"></div>');

                // 1. 載入渲染器樣式
                if (!document.getElementById('renderer-css')) {
                    const rendererCss = document.createElement('link');
                    rendererCss.id = 'renderer-css';
                    rendererCss.rel = 'stylesheet';
                    rendererCss.href = '{{ asset('assets/css/renderer.css') }}';
                    document.head.appendChild(rendererCss);
                }

                // 2. 載入渲染器腳本
                $.getScript('{{ asset('js/renderer.js') }}', function() {
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
                        $('#flex-root').html('<div class="alert alert-danger">渲染失敗: ' + error.message +
                            '</div>');
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
            @if (isset($bubble) && $bubble->template_id)
                // $('.template-list').hide();

                // 儲存氣泡數據，以便在動態欄位生成後使用
                const bubbleData = @json($bubble->bubble_data ?? []);

                // 點擊對應模板
                $('.template-item[data-id="{{ $bubble->template_id }}"]').click();

                // 在模板點擊事件之後，等待動態欄位生成完成
                setTimeout(function() {
                    // 填充所有動態欄位
                    if (bubbleData) {
                        Object.keys(bubbleData).forEach(function(key) {
                            // 尋找對應名稱的輸入元素
                            console.log('填充欄位:', key, bubbleData[key]);
                            const $field = $(`[name="${key}"]`);
                            const $fieldInputGroup = $field.closest('.input-group-dynamic');
                            console.log('找到欄位:', $fieldInputGroup);

                            if ($field.length) {
                                // 依據欄位類型處理
                                if ($field.attr('type') === 'file') {
                                    // 圖片欄位，顯示圖片，並綁定檔案讀取事件，並更新自定義檔案標籤
                                    // 如果$fieldInputGroup同階層已經有圖片，則不重複添加
                                    const img = $fieldInputGroup.parent().find('img');
                                    if (img.length === 0) {
                                        $fieldInputGroup.after(
                                            `<img src="" alt="${key}" style="max-width: 100px; max-height: 100px; margin-top: 10px;">`
                                        );
                                    }
                                    img.attr('src', bubbleData[key]);
                                    $field.on('change', function(e) {
                                        const file = e.target.files[0];
                                        if (file) {
                                            img.attr('src', URL.createObjectURL(file));
                                        }
                                    });
                                    // 更新自定義檔案標籤
                                    const fileName = bubbleData[key].split('/').pop();
                                    $fieldInputGroup.find('.custom-file-label').text(fileName);
                                } else {
                                    // 文本、文本區域等欄位
                                    $field.val(bubbleData[key]);
                                    // console.log('XXX填充欄位:', $field, $field.hasClass('color-picker'), $field.next('.color-swatch')[0]);

                                    // // 如果是顏色欄位，創建Pickr並更新Pickr顏色
                                    // if ($field.hasClass('color-picker')) {
                                    //     // 如果已經有顏色選擇器，則不重複創建
                                    //     if ($field.next('.color-swatch').length > 0) return;

                                    //     try {
                                    //         // 創建顏色選擇器
                                    //         $field.after(
                                    //             '<div class="color-swatch" style="display:inline-block; width: 100%; height: 30px; border: 1px solid #ccc; margin-top: 5px;"></div>'
                                    //         );

                                    //         // 確保元素已添加到 DOM 並獲取原生 DOM 元素
                                    //         setTimeout(() => {
                                    //             const colorSwatch = $field.next(
                                    //                 '.color-swatch')[0];
                                    //             if (!colorSwatch) return;

                                    //             // 確保元素已附加到 DOM
                                    //             if (!colorSwatch.parentNode) return;

                                    //             const pickr = Pickr.create({
                                    //                 el: colorSwatch, // 使用原生 DOM 元素
                                    //                 theme: 'classic',
                                    //                 default: bubbleData[key] ||
                                    //                     '#000000',
                                    //                 components: {
                                    //                     preview: true,
                                    //                     opacity: true,
                                    //                     hue: true,
                                    //                     interaction: {
                                    //                         input: true,
                                    //                         save: true
                                    //                     }
                                    //                 }
                                    //             });

                                    //             pickr.setColor(bubbleData[key] ||
                                    //                 '#000000');
                                    //             pickr.on('save', (color) => {
                                    //                 const hexColor = color.toHEXA()
                                    //                     .toString();
                                    //                 $field.val(hexColor);
                                    //                 pickr.hide();
                                    //             });

                                    //             // 顯示顏色選擇器
                                    //             $field.next('.color-swatch').on('click',
                                    //                 function() {
                                    //                     pickr.show();
                                    //                 });

                                    //             $field.on('change', function() {
                                    //                 // 當輸入框變更時，更新顏色選擇器的顏色
                                    //                 const color = $field.val();
                                    //                 pickr.setColor(color);
                                    //             });
                                    //         }, 50); // 給予DOM時間更新
                                    //     } catch (error) {
                                    //         console.error('初始化顏色選擇器失敗:', error);
                                    //     }
                                    // }
                                    $field.trigger('change');
                                }

                            }
                        });
                    }
                }, 1000); // 增加延遲時間確保欄位已生成
            @else
                // 如果是新建模式，選擇第一個模板
                if ($('.template-item').length > 0) {
                    $('.template-item:first').click();
                }


            @endif

            // 圖片錯誤處理
            function handleImageErrors() {
                $('img').each(function() {
                    const $img = $(this);
                    const defaultImg = '{{ url('assets/admin/img/ci.png') }}';

                    // 設置 onerror 處理
                    $img.on('error', function() {
                        if (!$(this).hasClass('error-handled')) {
                            $(this).attr('src', defaultImg)
                                .addClass('error-handled img-placeholder')
                                .attr('title', '原始圖片無法顯示');
                        }
                    });

                    // 對已經載入失敗的圖片進行處理
                    if ($img[0].complete && $img[0].naturalHeight === 0) {
                        $img.attr('src', defaultImg)
                            .addClass('error-handled img-placeholder')
                            .attr('title', '原始圖片無法顯示');
                    }
                });
            }

            // 頁面載入後處理圖片
            handleImageErrors();

            // 當動態添加新圖片時也進行處理
            $(document).on('DOMNodeInserted', 'img', function() {
                handleImageErrors();
            });


        });
    </script>
@endpush

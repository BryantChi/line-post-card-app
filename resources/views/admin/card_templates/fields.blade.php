<!-- 模板名稱 Field -->
<div class="form-group col-sm-6" data-step="1" data-intro="請為您的模板設定一個獨特且易於辨識的名稱。">
    {!! Form::label('name', '模板名稱:') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 255]) !!}
</div>

<!-- 模板描述 Field -->
<div class="form-group col-sm-6" data-step="2" data-intro="可選填。為您的模板添加簡短的描述，說明其用途或特色。">
    {!! Form::label('description', '模板描述:') !!}
    {!! Form::text('description', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<!-- 預覽圖片 Field -->
<div class="form-group col-sm-6" data-step="3" data-intro="上傳一張預覽圖片，讓使用者在選擇模板時能快速了解模板的樣式。">
    {!! Form::label('preview_image', '預覽圖片:') !!}
    <div class="input-group">
        <div class="custom-file">
            {!! Form::file('preview_image', ['class' => 'custom-file-input', 'id' => 'preview_image_input', 'accept' => 'image/*']) !!}
            {!! Form::label('preview_image', '選擇檔案', ['class' => 'custom-file-label']) !!}
        </div>
    </div>
    <div class="mt-2">
        @if(isset($cardTemplate) && $cardTemplate->preview_image)
            <img src="{{ asset('uploads/' . $cardTemplate->preview_image) }}" id="preview_image_preview" style="max-height: 100px" class="img-thumbnail">
        @else
            <img src="" id="preview_image_preview" style="max-height: 100px; display: none;" class="img-thumbnail">
        @endif
    </div>
</div>

<!-- 可編輯欄位設定 Field -->
<div class="form-group col-sm-12" data-step="4" data-intro="定義此模板中哪些欄位是使用者可以編輯的。點擊「新增欄位」來添加可編輯的項目。">
    {!! Form::label('editable_fields', '可編輯欄位設定:') !!}
    <div class="card">
        <div class="card-header">
            <button type="button" class="btn btn-sm btn-primary add-field-btn" data-step="5" data-intro="點擊此按鈕新增一個可編輯欄位。您可以設定欄位的識別碼、標籤、類型、是否必填及預設值。">新增欄位</button>
        </div>
        <div class="card-body">
            <div id="editable-fields-container" style="max-height: 500px; overflow-y: auto;">
                @if(isset($cardTemplate) && !empty($cardTemplate->editable_fields))
                    @foreach($cardTemplate->editable_fields as $fieldKey => $fieldConfig)
                        <div class="editable-field-row card mb-3" data-step="editable_field_example" data-intro="這是一個可編輯欄位的設定範例。您需要設定欄位識別碼（用於在JSON結構中引用）、標籤（顯示給使用者）、類型等。">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">欄位設定</h5>
                                <button type="button" class="btn btn-sm btn-danger remove-field-btn">刪除</button>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>欄位識別碼</label>
                                            <input type="text" name="editable_fields[{{ $fieldKey }}][key]" value="{{ $fieldKey }}" class="form-control field-key" required readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>欄位標籤</label>
                                            <input type="text" name="editable_fields[{{ $fieldKey }}][label]" value="{{ $fieldConfig['label'] ?? '' }}" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>欄位類型</label>
                                            <select name="editable_fields[{{ $fieldKey }}][type]" class="form-control" required>
                                                <option value="text" {{ ($fieldConfig['type'] ?? '') == 'text' ? 'selected' : '' }}>文字</option>
                                                <option value="textarea" {{ ($fieldConfig['type'] ?? '') == 'textarea' ? 'selected' : '' }}>多行文字</option>
                                                <option value="image" {{ ($fieldConfig['type'] ?? '') == 'image' ? 'selected' : '' }}>圖片</option>
                                                <option value="email" {{ ($fieldConfig['type'] ?? '') == 'email' ? 'selected' : '' }}>Email</option>
                                                <option value="phone" {{ ($fieldConfig['type'] ?? '') == 'phone' ? 'selected' : '' }}>電話</option>
                                                <option value="url" {{ ($fieldConfig['type'] ?? '') == 'url' ? 'selected' : '' }}>網址</option>
                                                <option value="color" {{ ($fieldConfig['type'] ?? '') == 'color' ? 'selected' : '' }}>顏色</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>是否必填</label>
                                            <select name="editable_fields[{{ $fieldKey }}][required]" class="form-control">
                                                <option value="1" {{ ($fieldConfig['required'] ?? false) ? 'selected' : '' }}>是</option>
                                                <option value="0" {{ !($fieldConfig['required'] ?? false) ? 'selected' : '' }}>否</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>預設值</label>
                                            <input type="text" name="editable_fields[{{ $fieldKey }}][default]" value="{{ $fieldConfig['default'] ?? '' }}" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

<!-- 模板基本結構 Field -->
<div class="form-group col-sm-12" data-step="6" data-intro="在這裡輸入模板的 LINE Flex Message JSON 結構。您可以使用 @{{欄位識別碼}} 的格式來引用上面定義的可編輯欄位，例如 @{{field}}。">
    {!! Form::label('template_schema', '模板基本結構:') !!}
    {!! Form::textarea('template_schema', isset($cardTemplate) ? json_encode($cardTemplate->template_schema, JSON_PRETTY_PRINT) : '', ['class' => 'form-control', 'rows' => 10, 'id' => 'template-json']) !!}
    <small class="form-text text-muted">請輸入 LINE Flex Message 的基本 JSON 結構，可編輯欄位以 @{{欄位識別碼}} 格式插入</small>
</div>

<!-- 啟用狀態 Field -->
<div class="form-group col-sm-6" data-step="7" data-intro="勾選此項以啟用此模板，使其在建立電子名片-卡片時可供選擇。">
    {!! Form::label('active', '啟用狀態:') !!}
    <div class="form-check">
        {!! Form::checkbox('active', '1', isset($cardTemplate) ? $cardTemplate->active : true, ['class' => 'form-check-input']) !!}
        {!! Form::label('active', '啟用此模板', ['class' => 'form-check-label']) !!}
    </div>
</div>

@push('page_scripts')
<script>
    $(document).ready(function() {
        // 初始化可編輯欄位容器
        bsCustomFileInput.init();

        // 預覽圖片即時預覽功能
        $('#preview_image_input').change(function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview_image_preview').attr('src', e.target.result);
                    $('#preview_image_preview').show();
                }
                reader.readAsDataURL(file);
            } else {
                $('#preview_image_preview').attr('src', '');
                $('#preview_image_preview').hide();
            }
        });

        // 新增欄位按鈕
        $('.add-field-btn').click(function() {
            let newIndex = $('.editable-field-row').length;
            let newFieldHtml = `
                <div class="editable-field-row card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">欄位設定</h5>
                        <button type="button" class="btn btn-sm btn-danger remove-field-btn">刪除</button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>欄位識別碼</label>
                                    <input type="text" name="editable_fields[field_${newIndex}][key]" value="field_${newIndex}" class="form-control field-key" required readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>欄位標籤</label>
                                    <input type="text" name="editable_fields[field_${newIndex}][label]" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>欄位類型</label>
                                    <select name="editable_fields[field_${newIndex}][type]" class="form-control" required>
                                        <option value="text">文字</option>
                                        <option value="textarea">多行文字</option>
                                        <option value="image">圖片</option>
                                        <option value="email">Email</option>
                                        <option value="phone">電話</option>
                                        <option value="url">網址</option>
                                        <option value="color">顏色</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>是否必填</label>
                                    <select name="editable_fields[field_${newIndex}][required]" class="form-control">
                                        <option value="1" selected>是</option>
                                        <option value="0">否</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>預設值</label>
                                    <input type="text" name="editable_fields[field_${newIndex}][default]" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#editable-fields-container').append(newFieldHtml);
            updateFieldNames();
        });

        // 刪除欄位按鈕（使用事件委派）
        $(document).on('click', '.remove-field-btn', function() {
            $(this).closest('.editable-field-row').remove();
            updateFieldNames();
        });

        // 當欄位識別碼改變時更新所有欄位名稱
        $(document).on('change', '.field-key', function() {
            updateFieldNames();
        });

        // 更新所有欄位的 name 屬性
        function updateFieldNames() {
            $('.editable-field-row').each(function(index) {
                let fieldKey = $(this).find('.field-key').val();
                $(this).find('input, select').each(function() {
                    let name = $(this).attr('name');
                    if (name) {
                        // 只更新 key 值之外的所有 name 屬性
                        if (!name.endsWith('[key]')) {
                            let newName = name.replace(/editable_fields\[.*?\]/, `editable_fields[${fieldKey}]`);
                            $(this).attr('name', newName);
                        }
                    }
                });
            });
        }
    });
</script>
@endpush

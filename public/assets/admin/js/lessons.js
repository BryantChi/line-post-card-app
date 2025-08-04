
/**
 * 編輯器初始化
 */
tinymce.init({
    selector: '#contents', // 更改此值以匹配你的HTML
    language: "zh_TW", // 介面語言
    menu: {
        meun: {
            title: '編輯器'
        }
    },
    menubar: 'menu',
    plugins: [
        'advlist', 'autolink', 'link', 'image', 'media', 'lists', 'charmap', 'preview', 'anchor', 'pagebreak',
        'searchreplace', 'wordcount', 'visualblocks', 'code', 'fullscreen', 'insertdatetime',
        'table', 'emoticons', 'template', 'help'
    ],
    toolbar: "undo redo | accordion accordionremove | blocks fontfamily fontsize | bold italic underline strikethrough | align numlist bullist | link image | table media | lineheight outdent indent| forecolor backcolor removeformat | charmap emoticons | code fullscreen preview | save print | pagebreak anchor codesample | ltr rtl",
    // tinycomments_mode: 'embedded',
    // media_live_embeds: true,
    image_class_list: [
        // { title: '無', value: '' },
        { title: 'Rwd class', value: 'img-fluid' }
    ],
    table_class_list: [
        { title: 'Rwd class', value: 'table table-bordered' },
        // { title: 'Rwd class', value: 'table table-responsive' }
    ],
    table_default_styles: {
        'border-collapse': 'collapse',
        'border': '1px solid black'
    },
    content_style: "table, th, td { border: 1px solid black; }",
    /* 配置圖片和視頻上傳的URL */
    image_title: true,
    // images_upload_url: '{{ route('editor-image-upload') }}',
    automatic_uploads: false,
    promotion: false,
    relative_urls: true,
    file_picker_types: 'image', // 允許上傳圖片和視頻
    file_picker_callback: function(cb, value, meta) {
        var input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');

        input.onchange = function() {
            var file = this.files[0];

            var reader = new FileReader();
            reader.onload = function() {
                var id = 'blobid' + (new Date()).getTime();
                var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                var base64 = reader.result.split(',')[1];
                var blobInfo = blobCache.create(id, file, base64);
                blobCache.add(blobInfo);

                /* call the callback and populate the Title field with the file name */
                cb(blobInfo.blobUri(), {
                    title: file.name
                });
            };
            reader.readAsDataURL(file);
        };

        input.click();
    },
});

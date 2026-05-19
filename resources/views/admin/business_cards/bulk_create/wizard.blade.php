@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <h1>批次建立 AI 數位名片</h1>
        <p class="text-muted mb-0">選定模板組合 → 下載 Excel 範本 → 填好個人化資料 → 上傳預覽 → 一鍵套用到多個帳號</p>
    </div>
</section>

<div class="content px-3">
    @include('flash::message')

    <div class="card">
        <div class="card-header p-2">
            <ul class="nav nav-pills" id="bulkWizardTabs">
                <li class="nav-item"><a class="nav-link active" data-step="1" href="#step1">① 選模板</a></li>
                <li class="nav-item"><a class="nav-link" data-step="2" href="#step2">② 下載 Excel</a></li>
                <li class="nav-item"><a class="nav-link" data-step="3" href="#step3">③ 上傳預覽</a></li>
                <li class="nav-item"><a class="nav-link" data-step="4" href="#step4">④ 確認執行</a></li>
            </ul>
        </div>

        <div class="card-body">

            {{-- Step 1 --}}
            <div class="bulk-step" data-step="1">
                <h4>① 選擇模板組合(可選 1~10 張,依排序為 bubble 順序)</h4>
                <p class="text-muted">勾選後在下方表格設定順序;順序代表名片內 bubble 的先後。</p>

                <div class="row">
                    @forelse($templates as $tpl)
                        @php
                            $summary = \App\Services\BulkCardExcelTemplateBuilder::summarizeTemplateFields($tpl);
                        @endphp
                        <div class="col-md-4 mb-2">
                            <label class="card p-2 m-0" style="cursor:pointer;">
                                <input type="checkbox" class="bulk-template-cb" value="{{ $tpl->id }}" data-name="{{ $tpl->name }}">
                                <strong>#{{ $tpl->id }} {{ $tpl->name }}</strong>
                                <small class="text-muted d-block">{{ $tpl->description }}</small>
                                <div class="mt-1">
                                    <span class="badge badge-success">填寫欄位 {{ $summary['fillable_count'] }}</span>
                                    @if($summary['image_count'] > 0)
                                        <span class="badge badge-warning" title="批次建立時自動略過,事後在後台單張編輯補圖">
                                            圖片 {{ $summary['image_count'] }}(自動略過)
                                        </span>
                                    @endif
                                    @if($summary['fillable_count'] === 0)
                                        <div class="text-warning mt-1" style="font-size:0.85em;">
                                            <i class="fa fa-exclamation-triangle"></i> 此模板無可填欄位,批次建立後該張卡片為空白,需到後台補
                                        </div>
                                    @endif
                                </div>
                            </label>
                        </div>
                    @empty
                        <div class="col-12"><div class="alert alert-warning">目前沒有可用模板,請先到「模板管理」建立。</div></div>
                    @endforelse
                </div>

                <hr>
                <h5>已選模板與順序</h5>
                <table class="table table-bordered table-sm" id="selectedTemplatesTable">
                    <thead class="thead-light"><tr><th style="width:80px;">順序</th><th>模板</th><th style="width:120px;">調整</th></tr></thead>
                    <tbody><tr><td colspan="3" class="text-muted text-center">尚未選擇</td></tr></tbody>
                </table>

                <div class="text-right">
                    <button class="btn btn-primary" id="goStep2">下一步:下載範本 <i class="fa fa-chevron-right"></i></button>
                </div>
            </div>

            {{-- Step 2 --}}
            <div class="bulk-step d-none" data-step="2">
                <h4>② 下載 Excel 範本</h4>
                <div class="alert alert-secondary">
                    <strong>範本結構(多工作表):</strong>
                    <ul class="mb-0">
                        <li><code>cards</code>:名片主檔,每位 user 一列(使用者/覆蓋模式/標題/副標題/描述)</li>
                        <li><code>bubble1</code> ~ <code>bubbleN</code>:每張卡片一個工作表,以 user_id 為對應依據</li>
                        <li><code>user_list</code>:可選 user 清單(下拉來源)</li>
                    </ul>
                    <small class="text-muted">填寫時請先在 cards 工作表選 user 與覆蓋模式,再到 bubble1/bubble2... 各分頁填卡片內容。</small>
                </div>

                <div class="alert alert-info">
                    <strong>可用 user 範圍(僅子帳號):</strong>
                    @if(Auth::user()->isSuperAdmin())
                        全系統 sub_user(共 {{ $targetUsers->count() }} 位)
                    @else
                        旗下 sub_user(共 {{ $targetUsers->count() }} 位)
                    @endif
                    <br>範本中 <code>user_id</code> 欄位點下拉可直接選,旁邊會帶出對應的 email/姓名。
                </div>

                <form method="POST" action="{{ route('admin.businessCards.bulkCreate.template') }}" id="downloadForm">
                    @csrf
                    <div id="downloadHidden"></div>
                    <button type="submit" class="btn btn-success"><i class="fa fa-download"></i> 下載 Excel 範本</button>
                    <button type="button" class="btn btn-link" id="backToStep1">← 回上一步</button>
                    <button type="button" class="btn btn-outline-primary float-right" id="goStep3">下一步:上傳 <i class="fa fa-chevron-right"></i></button>
                </form>
            </div>

            {{-- Step 3 --}}
            <div class="bulk-step d-none" data-step="3">
                <h4>③ 上傳填好的 Excel 並預覽</h4>
                <p>系統會驗證每一列的 user 權限、配額、欄位完整度,並標示哪些可成功、哪些會失敗。</p>

                <form id="previewForm" enctype="multipart/form-data">
                    @csrf
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" name="excel" id="excelFile" accept=".xlsx" class="custom-file-input" required>
                            <label class="custom-file-label" for="excelFile">選擇 .xlsx 檔案</label>
                        </div>
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">上傳並預覽</button>
                        </div>
                    </div>
                </form>

                <div id="previewResult" class="mt-3"></div>
            </div>

            {{-- Step 4 --}}
            <div class="bulk-step d-none" data-step="4">
                <h4>④ 確認執行</h4>
                <div id="executeSummary"></div>
                <form method="POST" action="{{ route('admin.businessCards.bulkCreate.execute') }}" id="executeForm">
                    @csrf
                    <input type="hidden" name="token" id="executeToken">
                    <button type="button" class="btn btn-link" id="backToStep3">← 回預覽</button>
                    <button type="submit" class="btn btn-danger float-right">確認執行批次建立</button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection

@push('page_scripts')
<script @cspNonce>
$(function () {
    let selectedTemplates = [];

    function refreshSelectedTable() {
        const tbody = $('#selectedTemplatesTable tbody');
        if (selectedTemplates.length === 0) {
            tbody.html('<tr><td colspan="3" class="text-muted text-center">尚未選擇</td></tr>');
            return;
        }
        tbody.empty();
        selectedTemplates.forEach((t, i) => {
            tbody.append(`
                <tr>
                    <td><strong>${i + 1}</strong></td>
                    <td>#${t.id} ${t.name}</td>
                    <td>
                        ${i > 0 ? `<button type="button" class="btn btn-sm btn-light move-up" data-i="${i}"><i class="fa fa-arrow-up"></i></button>` : ''}
                        ${i < selectedTemplates.length - 1 ? `<button type="button" class="btn btn-sm btn-light move-down" data-i="${i}"><i class="fa fa-arrow-down"></i></button>` : ''}
                        <button type="button" class="btn btn-sm btn-light remove-tpl" data-id="${t.id}"><i class="fa fa-times"></i></button>
                    </td>
                </tr>
            `);
        });
    }

    $(document).on('change', '.bulk-template-cb', function () {
        const id = parseInt($(this).val(), 10);
        const name = $(this).data('name');
        if (this.checked) {
            if (!selectedTemplates.find(t => t.id === id)) selectedTemplates.push({ id, name });
        } else {
            selectedTemplates = selectedTemplates.filter(t => t.id !== id);
        }
        refreshSelectedTable();
    });

    $(document).on('click', '.move-up', function () {
        const i = parseInt($(this).data('i'), 10);
        [selectedTemplates[i - 1], selectedTemplates[i]] = [selectedTemplates[i], selectedTemplates[i - 1]];
        refreshSelectedTable();
    });

    $(document).on('click', '.move-down', function () {
        const i = parseInt($(this).data('i'), 10);
        [selectedTemplates[i + 1], selectedTemplates[i]] = [selectedTemplates[i], selectedTemplates[i + 1]];
        refreshSelectedTable();
    });

    $(document).on('click', '.remove-tpl', function () {
        const id = parseInt($(this).data('id'), 10);
        selectedTemplates = selectedTemplates.filter(t => t.id !== id);
        $(`.bulk-template-cb[value="${id}"]`).prop('checked', false);
        refreshSelectedTable();
    });

    function showStep(n) {
        $('.bulk-step').addClass('d-none');
        $(`.bulk-step[data-step="${n}"]`).removeClass('d-none');
        $('#bulkWizardTabs .nav-link').removeClass('active');
        $(`#bulkWizardTabs .nav-link[data-step="${n}"]`).addClass('active');
    }

    $('#goStep2').on('click', function () {
        if (selectedTemplates.length === 0) { alert('請至少選擇 1 張模板'); return; }
        if (selectedTemplates.length > 10) { alert('最多選擇 10 張模板'); return; }
        const hidden = $('#downloadHidden').empty();
        selectedTemplates.forEach(t => {
            hidden.append(`<input type="hidden" name="template_ids[]" value="${t.id}">`);
        });
        showStep(2);
    });

    $('#backToStep1').on('click', function () { showStep(1); });
    $('#goStep3').on('click', function () { showStep(3); });
    $('#backToStep3').on('click', function () { showStep(3); });

    $('#excelFile').on('change', function () {
        $(this).next('.custom-file-label').text(this.files[0]?.name || '選擇 .xlsx 檔案');
    });

    $('#previewForm').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        const fd = new FormData(form);
        $('#previewResult').html('<div class="text-info">解析中...</div>');

        $.ajax({
            url: '{{ route("admin.businessCards.bulkCreate.preview") }}',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function (res) {
                if (!res.success) {
                    $('#previewResult').html(`<div class="alert alert-danger">${res.message}</div>`);
                    return;
                }
                const t = res.totals;
                let html = `
                    <div class="alert alert-info">
                        共 ${t.total} 列。
                        <span class="badge badge-success">可成功 ${t.valid}</span>
                        <span class="badge badge-danger">失敗 ${t.failed}</span>
                    </div>
                `;

                if (res.failedRows.length > 0) {
                    html += '<h6 class="text-danger">失敗列:</h6><table class="table table-sm table-bordered"><thead><tr><th>列號</th><th>user_id</th><th>原因</th></tr></thead><tbody>';
                    res.failedRows.forEach(r => {
                        html += `<tr><td>${r.row_index}</td><td>${r.user_id ?? '-'}</td><td>${r.error_reason}</td></tr>`;
                    });
                    html += '</tbody></table>';
                }

                if (t.valid > 0) {
                    html += '<h6 class="text-success">即將建立(前 20 列預覽):</h6><table class="table table-sm table-bordered"><thead><tr><th>列號</th><th>user_id</th><th>覆蓋模式</th><th>名片標題</th></tr></thead><tbody>';
                    res.validPreview.forEach(r => {
                        html += `<tr><td>${r.row_index}</td><td>${r.raw_row.user_id}</td><td>${r.raw_row.override_mode}</td><td>${r.raw_row.card_title ?? ''}</td></tr>`;
                    });
                    html += '</tbody></table>';

                    html += `<div class="text-right"><button class="btn btn-primary" id="proceedExecute" data-token="${res.token}" data-valid="${t.valid}" data-failed="${t.failed}">前往執行 →</button></div>`;
                }

                $('#previewResult').html(html);
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || '上傳失敗';
                $('#previewResult').html(`<div class="alert alert-danger">${msg}</div>`);
            }
        });
    });

    $(document).on('click', '#proceedExecute', function () {
        const token = $(this).data('token');
        const valid = $(this).data('valid');
        const failed = $(this).data('failed');
        $('#executeToken').val(token);
        $('#executeSummary').html(`
            <div class="alert alert-warning">
                即將執行批次建立:
                <strong>${valid}</strong> 列將被處理,
                <strong>${failed}</strong> 列因驗證失敗將被略過。
                <br>已存在名片的 user 會依該列的 override_mode (skip / append / replace) 處理。
            </div>
        `);
        showStep(4);
    });
});
</script>
@endpush

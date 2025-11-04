@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>會員資訊</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('sub-users.create') }}">
                       <i class="fas fa-plus"></i>
                        新增
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="clearfix"></div>

        <div class="card">
            @if(Auth::user()->isSuperAdmin())
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8">
                        <button type="button" id="batch-download-btn" class="btn btn-success">
                            <i class="fas fa-download"></i> 批次下載登入紀錄
                        </button>
                        <small class="text-muted ml-2">請勾選會員後下載 (最多50個)</small>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <input type="date" id="start_date" class="form-control" placeholder="開始日期">
                            <input type="date" id="end_date" class="form-control" placeholder="結束日期">
                        </div>
                        <small class="text-muted">預設為最近30天</small>
                    </div>
                </div>
            </div>
            @endif
            <div class="card-body p-0">
                @include('admin.sub_users.table')

                <div class="card-footer clearfix">
                    <div class="float-right">
                        @include('adminlte-templates::common.paginate', ['records' => $subUsers])
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection

@push('page_scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
$(document).ready(function() {
    // 全選功能
    $('#select-all').on('click', function() {
        $('.user-checkbox').prop('checked', this.checked);
    });

    // 單一下載登入紀錄 (完全比照批次下載做法)
    $(document).on('click', '.login-report-single', function() {
        let userId = $(this).data('user-id');
        let startDate = $('#start_date').val();
        let endDate = $('#end_date').val();

        // 建立並提交表單 (使用 POST 方法,不會跳轉頁面)
        let form = $('<form>', {
            method: 'POST',
            action: '{{ url("admin/sub-users") }}/' + userId + '/login-report'
        });

        // 加入 CSRF Token
        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: '{{ csrf_token() }}'
        }));

        // 加入日期範圍
        if (startDate) {
            form.append($('<input>', {
                type: 'hidden',
                name: 'start_date',
                value: startDate
            }));
        }

        if (endDate) {
            form.append($('<input>', {
                type: 'hidden',
                name: 'end_date',
                value: endDate
            }));
        }

        $('body').append(form);
        form.submit();
    });

    // 批次下載登入紀錄
    $('#batch-download-btn').on('click', function() {
        let selectedIds = $('.user-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: '提示',
                text: '請至少選擇一個會員'
            });
            return;
        }

        if (selectedIds.length > 50) {
            Swal.fire({
                icon: 'error',
                title: '超過限制',
                text: '批次下載最多支援 50 個會員,請減少選擇數量'
            });
            return;
        }

        // 取得日期範圍
        let startDate = $('#start_date').val();
        let endDate = $('#end_date').val();

        // 建立並提交表單
        let form = $('<form>', {
            method: 'POST',
            action: '{{ route("sub-users.login-report.batch") }}'
        });

        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: '{{ csrf_token() }}'
        }));

        // 加入選擇的會員 IDs
        selectedIds.forEach(function(id) {
            form.append($('<input>', {
                type: 'hidden',
                name: 'user_ids[]',
                value: id
            }));
        });

        // 加入日期範圍
        if (startDate) {
            form.append($('<input>', {
                type: 'hidden',
                name: 'start_date',
                value: startDate
            }));
        }

        if (endDate) {
            form.append($('<input>', {
                type: 'hidden',
                name: 'end_date',
                value: endDate
            }));
        }

        $('body').append(form);
        form.submit();
    });
});
</script>
@endpush


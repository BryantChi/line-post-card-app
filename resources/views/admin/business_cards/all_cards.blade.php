@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>所有子帳號電子名片</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="clearfix"></div>

        @include('flash::message')

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table" id="business-cards-table">
                        <thead>
                            <tr>
                                <th>帳號</th>
                                <th>卡片名稱</th>
                                {{-- <th>卡片標題</th> --}}
                                <th>狀態</th>
                                <th>建立時間</th>
                                <th colspan="3">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cards as $card)
                                <tr>
                                    <td>{{ $card->user ? $card->user->name : '未知用戶' }}</td>
                                    <td>{{ $card->name }}</td>
                                    {{-- <td>{{ $card->title }}</td> --}}
                                    <td>
                                        @if($card->active)
                                            <span class="badge badge-success">啟用</span>
                                        @else
                                            <span class="badge badge-secondary">停用</span>
                                        @endif
                                    </td>
                                    <td>{{ $card->created_at->format('Y-m-d H:i') }}</td>
                                    <td width="120">
                                        <div class='btn-group'>
                                            <a href="{{ route('admin.businessCards.show', [$card->id]) }}"
                                               class='btn btn-default btn-md'>
                                                <i class="far fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.businessCards.edit', [$card->id]) }}"
                                               class='btn btn-default btn-md'>
                                                <i class="far fa-edit"></i>
                                            </a>
                                            <a href="{{ route('admin.businessCards.bubbles.index', [$card->id]) }}"
                                               class='btn btn-default btn-md' title="管理氣泡卡片">
                                                <i class="fas fa-th-large"></i>
                                            </a>
                                            <a href="{{ route('admin.businessCards.preview', [$card->uuid]) }}"
                                               target="_blank"
                                               class='btn btn-default btn-md'
                                               title="預覽電子名片">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{ $cards->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>所有子帳號AI數位名片</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <a class="btn btn-default"
                           href="{{ route('admin.businessCards.index') }}"
                           data-step="1" data-intro="點擊這裡返回AI數位名片列表頁面。">
                            <i class="fa fa-arrow-left"></i> 返回
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="clearfix"></div>

        @include('flash::message')

        <div class="card">
            {{-- <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table" id="business-cards-table">
                        <thead>
                            <tr>
                                <th>帳號</th>
                                <th>卡片名稱</th>
                                <th>卡片標題</th>
                                <th>狀態</th>
                                <th>建立時間</th>
                                <th colspan="3">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cards as $card)
                                <tr>
                                    <td>{{ $card->user ? $card->user->name : '未知用戶' }}</td>
                                    <td>{{ $card->title }}</td>
                                    <td>{{ $card->title }}</td>
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
                                               title="預覽AI數位名片">
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
            </div> --}}

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table" id="businessCards-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>名稱</th>
                            <th>建立者</th>
                            <th>AI數位名片-卡片數</th>
                            <th>點閱率</th>
                            <th>分享數</th>
                            <th>狀態</th>
                            <th>建立時間</th>
                            @if (Auth::user()->isSuperAdmin())
                                <th>管理員</th>
                            @endif
                            <th colspan="3">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($cards as $businessCard)
                            <tr>
                                <td>{{ $businessCard->id }}</td>
                                <td>{{ $businessCard->title }}</td>
                                <td>
                                    @if($businessCard->user)
                                        {{ $businessCard->user->name }}
                                        @if(Auth::user()->isMainUser() && $businessCard->user->parent_id == Auth::id())
                                            <span class="badge badge-info">子帳號</span>
                                        @endif
                                    @else
                                        <span class="text-muted">未知</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-secondary">{{ $businessCard->bubbles()->count() }}</span>
                                </td>
                                {{-- 點閱率、分享數 --}}
                                <td>
                                    <span class="badge badge-secondary">{{ $businessCard->views ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">{{ $businessCard->shares ?? 0 }}</span>
                                </td>
                                <td>
                                    @if($businessCard->active)
                                        <span class="badge badge-success">啟用</span>
                                    @else
                                        <span class="badge badge-secondary">停用</span>
                                    @endif
                                </td>
                                <td>{{ $businessCard->created_at->format('Y-m-d H:i') }}</td>
                                @if (Auth::user()->isSuperAdmin())
                                    <td>
                                        @if($businessCard->user->parentUser)
                                            {{ $businessCard->user->parentUser->name }}
                                        @else
                                            <span class="text-muted">未知</span>
                                        @endif
                                    </td>
                                @endif
                                <td width="120">
                                    {!! Form::open(['route' => ['admin.businessCards.destroy', $businessCard->id], 'method' => 'delete']) !!}
                                    <div class='btn-group'>
                                        <a href="{{ route('admin.businessCards.show', [$businessCard->id]) }}"
                                        class='btn btn-default btn-md'
                                        data-step="4" data-intro="點擊這裡查看AI數位名片的詳細資訊及預覽。">
                                            <i class="far fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.businessCards.edit', [$businessCard->id]) }}"
                                        class='btn btn-default btn-md'
                                        data-step="5" data-intro="點擊這裡編輯AI數位名片的基本設定，例如標題、描述等。">
                                            <i class="far fa-edit"></i>
                                        </a>
                                        <a href="{{ route('admin.businessCards.bubbles.index', [$businessCard->id]) }}"
                                        class='btn btn-info btn-md' title="管理AI數位名片-卡片"
                                        data-step="6" data-intro="點擊這裡管理此AI數位名片包含的「卡片」。您可以在此新增、編輯、排序或刪除卡片。">
                                            <i class="fas fa-th-large"></i>
                                        </a>
                                        {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'button', 'class' => 'btn btn-danger btn-md js-confirm-delete', 'data-confirm' => '確定要刪除此AI數位名片嗎?', 'data-step' => "7", 'data-intro' => "點擊這裡刪除此AI數位名片。請注意，此操作無法復原。"]) !!}
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
                        @include('adminlte-templates::common.paginate', ['records' => $cards])
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

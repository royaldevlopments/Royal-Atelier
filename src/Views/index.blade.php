@extends('layouts.admin')

@section('title')
    Extensions Manager
@endsection

@section('content-header')
    <h1>Extensions<small>Manage your Royal Panel extensions</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Extensions</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Installed Extensions</h3>
                <div class="box-tools">
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#installModal">
                        <i class="fa fa-plus"></i> Install
                    </button>
                </div>
            </div>
            <div class="box-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(count($errors) > 0)
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </div>
                @endif

                @if($extensions->count() === 0)
                    <div class="text-center" style="padding: 40px 0;">
                        <i class="fa fa-puzzle-piece" style="font-size: 48px; opacity: 0.3; margin-bottom: 15px;"></i>
                        <h4>No Extensions Installed</h4>
                        <p class="text-muted">Install your first extension to get started.</p>
                    </div>
                @else
                    <div class="row">
                        @foreach($extensions as $ext)
                            <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12" style="margin-bottom: 15px;">
                                <a href="{{ route('rxadmin.extensions.show', $ext->extension_id) }}" style="text-decoration: none;">
                                    <div class="rx-extension-card">
                                        <div class="rx-extension-card-bg" style="background-image: url('{{ $ext->icon ?? '/rx-assets/default-icon.png' }}')"></div>
                                        <img src="{{ $ext->icon ?? '/rx-assets/default-icon.png' }}" alt="{{ $ext->extension_id }}" class="rx-extension-card-icon">
                                        <div class="rx-extension-card-body">
                                            <h4 class="rx-extension-card-title">{{ $ext->name }}</h4>
                                            <div class="rx-extension-card-meta">
                                                <span class="rx-badge">{{ $ext->version }}</span>
                                                @if(!$ext->enabled)
                                                    <span class="rx-badge rx-badge-disabled">Disabled</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="rx-extension-card-arrow">
                                            <i class="bi bi-arrow-right-short"></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="installModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('rxadmin.extensions.install') }}" method="POST" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Install Extension</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label">Package File (.blueprint)</label>
                        <input type="file" name="package" class="form-control" accept=".blueprint,.zip" required>
                        <p class="text-muted small">Upload a .blueprint package file to install.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Install</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <style>
        .rx-extension-card {
            background: color-mix(in srgb, var(--gray800) 85%, transparent);
            backdrop-filter: blur(16px);
            border: 1px solid color-mix(in srgb, var(--primary) 25%, transparent);
            border-radius: var(--radiusBox, 12px);
            padding: 0;
            overflow: hidden;
            position: relative;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 0 0 20px color-mix(in srgb, var(--primary) 10%, transparent), inset 0 0 20px color-mix(in srgb, var(--primary) 3%, transparent);
        }
        .rx-extension-card:hover {
            border-color: color-mix(in srgb, var(--primary) 50%, transparent);
            box-shadow: 0 0 30px color-mix(in srgb, var(--primary) 20%, transparent), inset 0 0 30px color-mix(in srgb, var(--primary) 5%, transparent);
            transform: translateY(-2px);
        }
        .rx-extension-card-bg {
            height: 60px;
            background-size: cover;
            background-position: center;
            filter: blur(8px);
            opacity: 0.3;
        }
        .rx-extension-card-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            position: absolute;
            top: 10px;
            left: 12px;
            z-index: 2;
        }
        .rx-extension-card-body {
            padding: 8px 12px 12px;
            flex: 1;
        }
        .rx-extension-card-title {
            margin: 0 0 4px;
            font-size: 14px;
            font-weight: 600;
            color: var(--gray200);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .rx-extension-card-meta {
            display: flex;
            gap: 6px;
            align-items: center;
        }
        .rx-extension-card-arrow {
            position: absolute;
            right: 10px;
            bottom: 10px;
            font-size: 24px;
            color: color-mix(in srgb, var(--primary) 50%, transparent);
        }
        .rx-badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 600;
            background: color-mix(in srgb, var(--primary) 30%, transparent);
            color: var(--gray200);
            border-radius: 10px;
        }
        .rx-badge-disabled {
            background: color-mix(in srgb, var(--dangerBorder) 30%, transparent);
            color: var(--dangerText);
        }
    </style>
@endsection

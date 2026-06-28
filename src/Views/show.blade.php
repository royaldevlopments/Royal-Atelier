@extends('layouts.admin')

@section('title')
    {{ $EXTENSION_NAME }}
@endsection

@section('content-header')
    <img src="{{ $EXTENSION_ICON }}" alt="{{ $EXTENSION_ID }}" style="float:left;width:30px;height:30px;border-radius:6px;margin-right:8px;"/>

    @if($EXTENSION_WEBSITE && $EXTENSION_WEBSITE != "[website]")
        <a href="{{ $EXTENSION_WEBSITE }}" target="_blank" class="btn btn-sm btn-default pull-right" style="margin-left:6px">
            <i class="fa fa-globe"></i>
        </a>
    @endif

    <form action="{{ route('rxadmin.extensions.uninstall', $EXTENSION_ID) }}" method="POST" class="pull-right" style="margin-left:6px" onsubmit="return confirm('Uninstall {{ $EXTENSION_NAME }}?');">
        {{ csrf_field() }}
        {{ method_field('DELETE') }}
        <button type="submit" class="btn btn-sm btn-danger">Uninstall</button>
    </form>

    <form action="{{ route('rxadmin.extensions.toggle', $EXTENSION_ID) }}" method="POST" class="pull-right">
        {{ csrf_field() }}
        <button type="submit" class="btn btn-sm btn-{{ $extension->enabled ? 'warning' : 'success' }}">
            {{ $extension->enabled ? 'Disable' : 'Enable' }}
        </button>
    </form>

    <h1 style="margin-top:0!important;display:flex;align-items:center;gap:8px;">
        <span>{{ $EXTENSION_NAME }}</span>
        <span class="rx-badge">{{ $EXTENSION_VERSION }}</span>
        @if(!$extension->enabled)
            <span class="rx-badge rx-badge-disabled">Disabled</span>
        @endif
    </h1>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header"><h3 class="box-title">About</h3></div>
            <div class="box-body">
                <p>{{ $EXTENSION_DESCRIPTION }}</p>
                <hr>
                <dl class="dl-horizontal">
                    <dt>Identifier</dt>
                    <dd><code>{{ $EXTENSION_ID }}</code></dd>
                    <dt>Version</dt>
                    <dd>{{ $EXTENSION_VERSION }}</dd>
                    <dt>Author</dt>
                    <dd>{{ $extension->author ?? 'Unknown' }}</dd>
                    <dt>Status</dt>
                    <dd>{!! $extension->enabled ? '<span class="label label-success">Enabled</span>' : '<span class="label label-danger">Disabled</span>' !!}</dd>
                    @if($EXTENSION_WEBSITE && $EXTENSION_WEBSITE != "[website]")
                        <dt>Website</dt>
                        <dd><a href="{{ $EXTENSION_WEBSITE }}" target="_blank">{{ $EXTENSION_WEBSITE }}</a></dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <style>
        .rx-badge {
            display: inline-block;
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 600;
            background: color-mix(in srgb, var(--primary) 30%, transparent);
            color: var(--gray200);
            border-radius: 12px;
        }
        .rx-badge-disabled {
            background: color-mix(in srgb, var(--dangerBorder) 30%, transparent);
            color: var(--dangerText);
        }
        .dl-horizontal dt {
            color: var(--gray400);
            font-weight: 500;
            text-align: left;
            width: 100px;
        }
        .dl-horizontal dd {
            margin-left: 120px;
            color: var(--gray200);
        }
        hr {
            border-top: 1px solid color-mix(in srgb, var(--primary) 10%, transparent);
        }
    </style>
@endsection

@extends('layouts.admin')

@section('title')
    {{ $EXTENSION_NAME }} — Extensions
@endsection

@section('content-header')
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <img src="{{ $EXTENSION_ICON }}" alt="" style="width:36px;height:36px;border-radius:8px;object-fit:cover;border:2px solid rgba(168,85,247,0.2);" onerror="this.src='/rx-assets/default-icon.svg'">

        <div style="flex:1;">
            <h1 style="margin:0;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span>{{ $EXTENSION_NAME }}</span>
                <span class="atelier-tag">{{ $EXTENSION_VERSION }}</span>
                @if($extension->enabled)
                    <span class="atelier-tag atelier-tag-enabled">Active</span>
                @else
                    <span class="atelier-tag atelier-tag-disabled">Disabled</span>
                @endif
            </h1>
        </div>

        <div style="display:flex;gap:8px;">
            <form action="{{ route('rxadmin.extensions.toggle', $EXTENSION_ID) }}" method="POST">
                {{ csrf_field() }}
                <button type="submit" class="atelier-btn {{ $extension->enabled ? 'atelier-btn-warning' : 'atelier-btn-success' }}">
                    <i class="fa fa-{{ $extension->enabled ? 'pause' : 'play' }}"></i>
                    {{ $extension->enabled ? 'Disable' : 'Enable' }}
                </button>
            </form>

            @if($EXTENSION_WEBSITE && $EXTENSION_WEBSITE != "[website]")
                <a href="{{ $EXTENSION_WEBSITE }}" target="_blank" class="atelier-btn atelier-btn-ghost">
                    <i class="fa fa-globe"></i> Website
                </a>
            @endif

            <form action="{{ route('rxadmin.extensions.uninstall', $EXTENSION_ID) }}" method="POST" onsubmit="return confirm('Permanently uninstall {{ $EXTENSION_NAME }}? This cannot be undone.');">
                {{ csrf_field() }}
                {{ method_field('DELETE') }}
                <button type="submit" class="atelier-btn atelier-btn-danger">
                    <i class="fa fa-trash"></i> Uninstall
                </button>
            </form>
        </div>
    </div>
@endsection

@section('content')
<style>
:root {
    --atelier-primary: #a855f7;
    --atelier-card-bg: rgba(26, 10, 46, 0.85);
    --atelier-border: rgba(168, 85, 247, 0.2);
    --atelier-glow: rgba(168, 85, 247, 0.15);
    --atelier-text: #e9d5ff;
    --atelier-muted: #c084fc;
}

.atelier-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    background: rgba(168, 85, 247, 0.15);
    color: var(--atelier-text);
}

.atelier-tag-disabled {
    background: rgba(225, 29, 72, 0.15);
    color: #fb7185;
}

.atelier-tag-enabled {
    background: rgba(34, 197, 94, 0.15);
    color: #4ade80;
}

.atelier-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.atelier-btn-success {
    background: rgba(34, 197, 94, 0.15);
    color: #4ade80;
    border: 1px solid rgba(34, 197, 94, 0.3);
}

.atelier-btn-success:hover {
    background: rgba(34, 197, 94, 0.25);
    box-shadow: 0 0 20px rgba(34, 197, 94, 0.2);
}

.atelier-btn-warning {
    background: rgba(234, 179, 8, 0.15);
    color: #eab308;
    border: 1px solid rgba(234, 179, 8, 0.3);
}

.atelier-btn-warning:hover {
    background: rgba(234, 179, 8, 0.25);
    box-shadow: 0 0 20px rgba(234, 179, 8, 0.2);
}

.atelier-btn-danger {
    background: rgba(225, 29, 72, 0.15);
    color: #fb7185;
    border: 1px solid rgba(225, 29, 72, 0.3);
}

.atelier-btn-danger:hover {
    background: rgba(225, 29, 72, 0.25);
    box-shadow: 0 0 20px rgba(225, 29, 72, 0.2);
}

.atelier-btn-ghost {
    background: transparent;
    color: var(--atelier-text);
    border: 1px solid var(--atelier-border);
}

.atelier-btn-ghost:hover {
    border-color: var(--atelier-primary);
    background: rgba(168, 85, 247, 0.1);
}

.atelier-detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.atelier-panel {
    background: var(--atelier-card-bg);
    border: 1px solid var(--atelier-border);
    border-radius: 12px;
    backdrop-filter: blur(12px);
    overflow: hidden;
}

.atelier-panel-header {
    padding: 16px 20px;
    border-bottom: 1px solid rgba(168, 85, 247, 0.1);
    font-size: 13px;
    font-weight: 600;
    color: var(--atelier-text);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: flex;
    align-items: center;
    gap: 8px;
}

.atelier-panel-body {
    padding: 20px;
}

.atelier-info-row {
    display: flex;
    padding: 8px 0;
    border-bottom: 1px solid rgba(168, 85, 247, 0.06);
}

.atelier-info-row:last-child {
    border-bottom: none;
}

.atelier-info-label {
    width: 120px;
    font-size: 12px;
    color: var(--atelier-muted);
    opacity: 0.7;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 500;
    flex-shrink: 0;
}

.atelier-info-value {
    flex: 1;
    font-size: 13px;
    color: var(--atelier-text);
}

.atelier-info-value code {
    background: rgba(13, 2, 24, 0.5);
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 12px;
    color: #c084fc;
}

.atelier-desc {
    font-size: 14px;
    color: var(--atelier-text);
    line-height: 1.6;
    opacity: 0.85;
    margin: 0;
}

.atelier-full-width {
    grid-column: 1 / -1;
}

/* AdminLTE overrides for consistency */
.box {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
}

@media (max-width: 768px) {
    .atelier-detail-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="atelier-detail-grid">
    <div class="atelier-panel atelier-full-width">
        <div class="atelier-panel-header">
            <i class="fa fa-info-circle" style="color:var(--atelier-primary);"></i>
            About
        </div>
        <div class="atelier-panel-body">
            <p class="atelier-desc">{{ $EXTENSION_DESCRIPTION }}</p>
        </div>
    </div>

    <div class="atelier-panel">
        <div class="atelier-panel-header">
            <i class="fa fa-cog" style="color:var(--atelier-primary);"></i>
            Details
        </div>
        <div class="atelier-panel-body">
            <div class="atelier-info-row">
                <span class="atelier-info-label">Identifier</span>
                <span class="atelier-info-value"><code>{{ $EXTENSION_ID }}</code></span>
            </div>
            <div class="atelier-info-row">
                <span class="atelier-info-label">Version</span>
                <span class="atelier-info-value">{{ $EXTENSION_VERSION }}</span>
            </div>
            <div class="atelier-info-row">
                <span class="atelier-info-label">Author</span>
                <span class="atelier-info-value">{{ $extension->author ?? 'Unknown' }}</span>
            </div>
            <div class="atelier-info-row">
                <span class="atelier-info-label">Status</span>
                <span class="atelier-info-value">
                    @if($extension->enabled)
                        <span class="atelier-tag atelier-tag-enabled">Active</span>
                    @else
                        <span class="atelier-tag atelier-tag-disabled">Disabled</span>
                    @endif
                </span>
            </div>
            @if($EXTENSION_WEBSITE && $EXTENSION_WEBSITE != "[website]")
            <div class="atelier-info-row">
                <span class="atelier-info-label">Website</span>
                <span class="atelier-info-value">
                    <a href="{{ $EXTENSION_WEBSITE }}" target="_blank" style="color:var(--atelier-primary);text-decoration:none;">
                        {{ $EXTENSION_WEBSITE }} <i class="fa fa-external-link" style="font-size:10px;"></i>
                    </a>
                </span>
            </div>
            @endif
        </div>
    </div>

    <div class="atelier-panel">
        <div class="atelier-panel-header">
            <i class="fa fa-shield" style="color:var(--atelier-primary);"></i>
            Actions
        </div>
        <div class="atelier-panel-body" style="display:flex;flex-direction:column;gap:10px;">
            <form action="{{ route('rxadmin.extensions.toggle', $EXTENSION_ID) }}" method="POST">
                {{ csrf_field() }}
                <button type="submit" class="atelier-btn" style="width:100%;justify-content:center;{{ $extension->enabled ? 'background:rgba(234,179,8,0.15);color:#eab308;border:1px solid rgba(234,179,8,0.3);' : 'background:rgba(34,197,94,0.15);color:#4ade80;border:1px solid rgba(34,197,94,0.3);' }}">
                    <i class="fa fa-{{ $extension->enabled ? 'pause' : 'play' }}"></i>
                    {{ $extension->enabled ? 'Disable Extension' : 'Enable Extension' }}
                </button>
            </form>

            <form action="{{ route('rxadmin.extensions.uninstall', $EXTENSION_ID) }}" method="POST" onsubmit="return confirm('Permanently uninstall {{ $EXTENSION_NAME }}? All files and hooks will be removed.');">
                {{ csrf_field() }}
                {{ method_field('DELETE') }}
                <button type="submit" class="atelier-btn" style="width:100%;justify-content:center;background:rgba(225,29,72,0.15);color:#fb7185;border:1px solid rgba(225,29,72,0.3);">
                    <i class="fa fa-trash"></i> Uninstall Extension
                </button>
            </form>

            <a href="{{ route('rxadmin.extensions.index') }}" class="atelier-btn atelier-btn-ghost" style="width:100%;justify-content:center;">
                <i class="fa fa-arrow-left"></i> Back to Extensions
            </a>
        </div>
    </div>
</div>
@endsection

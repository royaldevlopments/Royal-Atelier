@extends('layouts.admin')

@section('title')
    Atelier Settings — Extensions
@endsection

@section('content-header')
    <h1>Atelier Configuration<small>Extension framework settings</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('rxadmin.extensions.index') }}">Extensions</a></li>
        <li class="active">Settings</li>
    </ol>
@endsection

@section('content')
@php
    $settings = $library->dbGetMany('rx');
    $flags = config('rxframework.flags', []);
@endphp

<style>
:root {
    --atelier-primary: #a855f7;
    --atelier-card-bg: rgba(26, 10, 46, 0.85);
    --atelier-border: rgba(168, 85, 247, 0.2);
    --atelier-text: #e9d5ff;
    --atelier-muted: #c084fc;
}

.atelier-settings {
    max-width: 800px;
}

.atelier-panel {
    background: var(--atelier-card-bg);
    border: 1px solid var(--atelier-border);
    border-radius: 12px;
    backdrop-filter: blur(12px);
    overflow: hidden;
    margin-bottom: 20px;
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

.atelier-flag-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 0;
    border-bottom: 1px solid rgba(168, 85, 247, 0.06);
}

.atelier-flag-row:last-child {
    border-bottom: none;
}

.atelier-flag-info {
    flex: 1;
}

.atelier-flag-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--atelier-text);
    margin: 0 0 2px;
}

.atelier-flag-desc {
    font-size: 12px;
    color: var(--atelier-muted);
    opacity: 0.6;
    margin: 0;
}

.atelier-flag-control {
    flex-shrink: 0;
    margin-left: 20px;
}

.atelier-flag-control select,
.atelier-flag-control input[type="text"],
.atelier-flag-control input[type="number"] {
    background: rgba(13, 2, 24, 0.6);
    border: 1px solid var(--atelier-border);
    border-radius: 8px;
    color: var(--atelier-text);
    padding: 8px 12px;
    font-size: 13px;
    min-width: 120px;
    transition: all 0.3s ease;
    outline: none;
}

.atelier-flag-control select:focus,
.atelier-flag-control input:focus {
    border-color: var(--atelier-primary);
    box-shadow: 0 0 15px rgba(168, 85, 247, 0.2);
}

.atelier-flag-control select {
    min-width: 100px;
}

.atelier-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 20px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.atelier-btn-primary {
    background: linear-gradient(135deg, var(--atelier-primary), #d946ef);
    color: #fff;
    box-shadow: 0 0 20px rgba(168, 85, 247, 0.3);
}

.atelier-btn-primary:hover {
    box-shadow: 0 0 35px rgba(168, 85, 247, 0.5);
    transform: translateY(-1px);
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

.atelier-alert-success {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 16px;
    font-size: 13px;
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.2);
    color: #4ade80;
}

.atelier-toggle {
    position: relative;
    width: 44px;
    height: 24px;
    cursor: pointer;
}

.atelier-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.atelier-toggle-slider {
    position: absolute;
    inset: 0;
    background: rgba(168, 85, 247, 0.2);
    border-radius: 12px;
    transition: all 0.3s ease;
}

.atelier-toggle-slider::before {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    left: 3px;
    top: 3px;
    background: #fff;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.atelier-toggle input:checked + .atelier-toggle-slider {
    background: linear-gradient(135deg, var(--atelier-primary), #d946ef);
}

.atelier-toggle input:checked + .atelier-toggle-slider::before {
    transform: translateX(20px);
}
</style>

<div class="atelier-settings">
    @if(session('success'))
        <div class="atelier-alert-success">
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('rxadmin.extensions.settings.update') }}" method="POST">
        {{ csrf_field() }}

        <div class="atelier-panel">
            <div class="atelier-panel-header">
                <i class="fa fa-flag" style="color:var(--atelier-primary);"></i>
                Framework Flags
            </div>
            <div class="atelier-panel-body">
                @forelse($flags as $key => $config)
                    @php
                        $value = $settings["flags:{$key}"] ?? $config['default'] ?? '';
                        $type = $config['type'] ?? 'string';
                    @endphp
                    <div class="atelier-flag-row">
                        <div class="atelier-flag-info">
                            <div class="atelier-flag-name">{{ $config['label'] ?? $key }}</div>
                            @if($config['description'] ?? false)
                                <div class="atelier-flag-desc">{{ $config['description'] }}</div>
                            @endif
                        </div>
                        <div class="atelier-flag-control">
                            @switch($type)
                                @case('boolean')
                                    <label class="atelier-toggle">
                                        <input type="hidden" name="flags:{{ $key }}" value="0">
                                        <input type="checkbox" name="flags:{{ $key }}" value="1" {{ $value ? 'checked' : '' }}>
                                        <span class="atelier-toggle-slider"></span>
                                    </label>
                                    @break
                                @case('number')
                                @case('integer')
                                    <input type="number" name="flags:{{ $key }}" value="{{ $value }}" step="{{ $type === 'integer' ? 1 : 'any' }}">
                                    @break
                                @default
                                    <input type="text" name="flags:{{ $key }}" value="{{ $value }}">
                            @endswitch
                        </div>
                    </div>
                @empty
                    <p style="color:var(--atelier-muted);opacity:0.6;text-align:center;padding:20px;">
                        No configuration flags available.
                    </p>
                @endforelse
            </div>
        </div>

        <div style="display:flex;gap:10px;">
            <a href="{{ route('rxadmin.extensions.index') }}" class="atelier-btn atelier-btn-ghost">
                <i class="fa fa-arrow-left"></i> Back
            </a>
            <button type="submit" class="atelier-btn atelier-btn-primary">
                <i class="fa fa-save"></i> Save Settings
            </button>
        </div>
    </form>
</div>
@endsection

@extends('layouts.admin')

@section('title')
    Extensions Manager — Royal Atelier
@endsection

@section('content-header')
    <h1>Atelier<small>Extension framework for Royal Panel</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Extensions</li>
    </ol>
@endsection

@section('content')
@php
    $total = $extensions->count();
    $enabled = $extensions->where('enabled', true)->count();
    $disabled = $total - $enabled;
@endphp

<style>
:root {
    --atelier-primary: #a855f7;
    --atelier-bg: #0d0218;
    --atelier-card-bg: rgba(26, 10, 46, 0.85);
    --atelier-border: rgba(168, 85, 247, 0.2);
    --atelier-glow: rgba(168, 85, 247, 0.15);
    --atelier-text: #e9d5ff;
    --atelier-muted: #c084fc;
}

.atelier-dashboard {
    padding: 0;
}

.atelier-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.atelier-stat {
    background: var(--atelier-card-bg);
    border: 1px solid var(--atelier-border);
    border-radius: 12px;
    padding: 18px 20px;
    backdrop-filter: blur(12px);
    transition: all 0.3s ease;
}

.atelier-stat:hover {
    border-color: rgba(168, 85, 247, 0.4);
    box-shadow: 0 0 25px var(--atelier-glow);
    transform: translateY(-1px);
}

.atelier-stat-icon {
    font-size: 24px;
    margin-bottom: 8px;
    opacity: 0.7;
}

.atelier-stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--atelier-text);
    line-height: 1.1;
}

.atelier-stat-label {
    font-size: 12px;
    color: var(--atelier-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-top: 4px;
}

.atelier-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    gap: 12px;
    flex-wrap: wrap;
}

.atelier-search {
    position: relative;
    flex: 1;
    min-width: 200px;
    max-width: 360px;
}

.atelier-search input {
    width: 100%;
    background: var(--atelier-card-bg);
    border: 1px solid var(--atelier-border);
    border-radius: 8px;
    padding: 10px 14px 10px 38px;
    color: var(--atelier-text);
    font-size: 13px;
    transition: all 0.3s ease;
    outline: none;
}

.atelier-search input:focus {
    border-color: var(--atelier-primary);
    box-shadow: 0 0 15px var(--atelier-glow);
}

.atelier-search i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--atelier-muted);
    font-size: 14px;
    opacity: 0.6;
}

.atelier-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 16px;
}

.atelier-card {
    background: var(--atelier-card-bg);
    border: 1px solid var(--atelier-border);
    border-radius: 12px;
    overflow: hidden;
    position: relative;
    transition: all 0.3s ease;
    cursor: pointer;
}

.atelier-card:hover {
    border-color: rgba(168, 85, 247, 0.5);
    box-shadow: 0 0 30px var(--atelier-glow), inset 0 0 30px rgba(168, 85, 247, 0.03);
    transform: translateY(-3px);
}

.atelier-card-banner {
    height: 72px;
    background-size: cover;
    background-position: center;
    position: relative;
}

.atelier-card-banner::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 40%, var(--atelier-card-bg));
}

.atelier-card-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    position: absolute;
    top: 50px;
    left: 16px;
    z-index: 2;
    border: 2px solid var(--atelier-border);
    background: var(--atelier-bg);
    object-fit: cover;
}

.atelier-card-body {
    padding: 28px 16px 14px;
}

.atelier-card-name {
    font-size: 15px;
    font-weight: 600;
    color: var(--atelier-text);
    margin: 0 0 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.atelier-card-desc {
    font-size: 12px;
    color: var(--atelier-muted);
    margin: 0 0 12px;
    opacity: 0.7;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.4;
}

.atelier-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    border-top: 1px solid rgba(168, 85, 247, 0.1);
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

.atelier-card-action {
    font-size: 18px;
    color: var(--atelier-muted);
    opacity: 0.4;
    transition: all 0.3s ease;
}

.atelier-card:hover .atelier-card-action {
    opacity: 0.8;
    transform: translateX(3px);
}

.atelier-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
}

.atelier-empty-icon {
    font-size: 56px;
    opacity: 0.2;
    margin-bottom: 16px;
    color: var(--atelier-primary);
}

.atelier-empty h4 {
    color: var(--atelier-text);
    margin: 0 0 6px;
    font-size: 18px;
}

.atelier-empty p {
    color: var(--atelier-muted);
    font-size: 13px;
    margin: 0;
    opacity: 0.6;
}

.atelier-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 18px;
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

/* Modal styling */
.atelier-modal .modal-content {
    background: var(--atelier-card-bg);
    border: 1px solid var(--atelier-border);
    border-radius: 12px;
    backdrop-filter: blur(16px);
}

.atelier-modal .modal-header {
    border-bottom: 1px solid rgba(168, 85, 247, 0.1);
    padding: 18px 20px;
}

.atelier-modal .modal-header h4 {
    color: var(--atelier-text);
    font-weight: 600;
}

.atelier-modal .modal-header .close {
    color: var(--atelier-text);
    opacity: 0.6;
}

.atelier-modal .modal-body {
    padding: 20px;
}

.atelier-modal .modal-footer {
    border-top: 1px solid rgba(168, 85, 247, 0.1);
    padding: 14px 20px;
}

.atelier-modal label {
    color: var(--atelier-muted);
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
    margin-bottom: 6px;
}

.atelier-modal .form-control {
    background: rgba(13, 2, 24, 0.6);
    border: 1px solid var(--atelier-border);
    border-radius: 8px;
    color: var(--atelier-text);
    padding: 10px 14px;
    font-size: 13px;
    transition: all 0.3s ease;
}

.atelier-modal .form-control:focus {
    border-color: var(--atelier-primary);
    box-shadow: 0 0 15px var(--atelier-glow);
}

/* Alert styling */
.atelier-alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 16px;
    font-size: 13px;
    border: 1px solid transparent;
}

.atelier-alert-success {
    background: rgba(34, 197, 94, 0.1);
    border-color: rgba(34, 197, 94, 0.2);
    color: #4ade80;
}

.atelier-alert-danger {
    background: rgba(225, 29, 72, 0.1);
    border-color: rgba(225, 29, 72, 0.2);
    color: #fb7185;
}

.no-results {
    display: none;
}
</style>

<div class="atelier-dashboard">
    @if(session('success'))
        <div class="atelier-alert atelier-alert-success">
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(count($errors) > 0)
        <div class="atelier-alert atelier-alert-danger">
            <i class="fa fa-exclamation-circle"></i>
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    <div class="atelier-stats">
        <div class="atelier-stat">
            <div class="atelier-stat-icon">🧩</div>
            <div class="atelier-stat-value">{{ $total }}</div>
            <div class="atelier-stat-label">Total Installed</div>
        </div>
        <div class="atelier-stat">
            <div class="atelier-stat-icon" style="color:#4ade80;">●</div>
            <div class="atelier-stat-value">{{ $enabled }}</div>
            <div class="atelier-stat-label">Active</div>
        </div>
        <div class="atelier-stat">
            <div class="atelier-stat-icon" style="color:#fb7185;">●</div>
            <div class="atelier-stat-value">{{ $disabled }}</div>
            <div class="atelier-stat-label">Disabled</div>
        </div>
        <div class="atelier-stat" style="cursor:pointer;" onclick="$('#installModal').modal('show')">
            <div class="atelier-stat-icon" style="color:#a855f7;">+</div>
            <div class="atelier-stat-value" style="font-size:20px;margin-top:6px;">Install New</div>
            <div class="atelier-stat-label">Upload Package</div>
        </div>
    </div>

    <div class="atelier-toolbar">
        <div class="atelier-search">
            <i class="fa fa-search"></i>
            <input type="text" id="atelierSearch" placeholder="Search extensions..." oninput="filterExtensions(this.value)">
        </div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('rxadmin.extensions.settings') }}" class="atelier-btn atelier-btn-ghost">
                <i class="fa fa-cog"></i> Settings
            </a>
            <button class="atelier-btn atelier-btn-primary" onclick="$('#installModal').modal('show')">
                <i class="fa fa-upload"></i> Install Extension
            </button>
        </div>
    </div>

    @if($total === 0)
        <div class="atelier-grid">
            <div class="atelier-empty" style="grid-column:1/-1;">
                <div class="atelier-empty-icon">🧩</div>
                <h4>No Extensions Installed</h4>
                <p>Upload an extension package to get started.</p>
                <br>
                <button class="atelier-btn atelier-btn-primary" onclick="$('#installModal').modal('show')">
                    <i class="fa fa-upload"></i> Install Your First Extension
                </button>
            </div>
        </div>
    @else
        <div class="atelier-grid" id="atelierGrid">
            @foreach($extensions as $ext)
                <a href="{{ route('rxadmin.extensions.show', $ext->extension_id) }}" style="text-decoration:none;display:contents;" class="atelier-link" data-name="{{ strtolower($ext->name) }}" data-id="{{ $ext->extension_id }}">
                    <div class="atelier-card">
                        <div class="atelier-card-banner" style="background-image:url('{{ $ext->icon ?? '/rx-assets/default-icon.svg' }}')"></div>
                        <img src="{{ $ext->icon ?? '/rx-assets/default-icon.svg' }}" alt="" class="atelier-card-icon" onerror="this.src='/rx-assets/default-icon.svg'">
                        <div class="atelier-card-body">
                            <h4 class="atelier-card-name">{{ $ext->name }}</h4>
                            <p class="atelier-card-desc">{{ $ext->description ?? 'No description' }}</p>
                        </div>
                        <div class="atelier-card-footer">
                            <div style="display:flex;gap:6px;">
                                <span class="atelier-tag">{{ $ext->version }}</span>
                                @if($ext->enabled)
                                    <span class="atelier-tag atelier-tag-enabled">Active</span>
                                @else
                                    <span class="atelier-tag atelier-tag-disabled">Disabled</span>
                                @endif
                            </div>
                            <span class="atelier-card-action">→</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>

<div class="modal fade atelier-modal" id="installModal" tabindex="-1" role="dialog">
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
                        <label>Package File</label>
                        <input type="file" name="package" class="form-control" accept=".zip" required>
                        <p class="text-muted small" style="margin-top:6px;color:var(--atelier-muted);opacity:0.6;">
                            Upload an extension package to install.
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="atelier-btn atelier-btn-ghost" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="atelier-btn atelier-btn-primary">
                        <i class="fa fa-cloud-upload"></i> Install
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function filterExtensions(query) {
    query = query.toLowerCase().trim();
    const cards = document.querySelectorAll('.atelier-link');
    let visible = 0;
    cards.forEach(link => {
        const name = link.dataset.name;
        const id = link.dataset.id;
        const match = !query || name.includes(query) || id.includes(query);
        link.style.display = match ? 'contents' : 'none';
        if (match) visible++;
    });
    const empty = document.getElementById('atelierEmpty');
    if (visible === 0) {
        if (!empty) {
            const grid = document.getElementById('atelierGrid');
            const div = document.createElement('div');
            div.id = 'atelierEmpty';
            div.className = 'atelier-empty';
            div.style.gridColumn = '1 / -1';
            div.innerHTML = '<div class="atelier-empty-icon">🔍</div><h4>No Results</h4><p>Try a different search term.</p>';
            grid.appendChild(div);
        }
    } else {
        const el = document.getElementById('atelierEmpty');
        if (el) el.remove();
    }
}
</script>
@endsection

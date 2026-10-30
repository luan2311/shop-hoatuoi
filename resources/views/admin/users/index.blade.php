@extends('layouts.admin')

@section('title', 'Quản lý Tài Khoản')

@push('styles')
<style>
    /* Crimson Sentinel - Identity & Access Governance Refinement */
    :root {
        --sentinel-crimson-main: #e11d48;
        --sentinel-crimson-deep: #be123c;
        --sentinel-crimson-dark: #9f1239;
        --sentinel-crimson-glow: rgba(225, 29, 72, 0.22);
        --sentinel-surface-tint: #fff1f2;
        --sentinel-border-tint: #fecdd3;
        --sentinel-slate-dark: #0f172a;
        --sentinel-slate-muted: #64748b;

        --sentinel-emerald-main: #10b981;
        --sentinel-emerald-deep: #059669;
        --sentinel-emerald-glow: rgba(16, 185, 129, 0.22);
    }

    /* Modal Backdrop & Container */
    .sentinel-modal-content {
        border-radius: 20px !important;
        border: 1px solid rgba(226, 232, 240, 0.9) !important;
        background: #ffffff !important;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35), 0 0 0 1px rgba(225, 29, 72, 0.12) !important;
        animation: sentinelModalScaleUp 0.28s cubic-bezier(0.16, 1, 0.3, 1);
        overflow: hidden;
    }

    @keyframes sentinelModalScaleUp {
        0% {
            opacity: 0;
            transform: scale(0.93) translateY(12px);
        }
        100% {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    /* Icon Badges with Ambient Pulse */
    .sentinel-icon-badge {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ffe4e6 0%, #fecdd3 100%);
        color: #be123c;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        box-shadow: 0 0 0 8px var(--sentinel-crimson-glow);
        animation: crimsonBadgePulse 2.4s infinite ease-in-out;
    }

    @keyframes crimsonBadgePulse {
        0%, 100% {
            box-shadow: 0 0 0 6px var(--sentinel-crimson-glow);
            transform: scale(1);
        }
        50% {
            box-shadow: 0 0 0 14px rgba(225, 29, 72, 0.08);
            transform: scale(1.03);
        }
    }

    .sentinel-icon-badge-emerald {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #059669;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        box-shadow: 0 0 0 8px var(--sentinel-emerald-glow);
        animation: emeraldBadgePulse 2.4s infinite ease-in-out;
    }

    @keyframes emeraldBadgePulse {
        0%, 100% {
            box-shadow: 0 0 0 6px var(--sentinel-emerald-glow);
            transform: scale(1);
        }
        50% {
            box-shadow: 0 0 0 14px rgba(16, 185, 129, 0.08);
            transform: scale(1.03);
        }
    }

    /* User Identity Dossier Capsule */
    .user-dossier-capsule {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        transition: all 0.2s ease;
    }

    .user-dossier-capsule:hover {
        border-color: #cbd5e1;
        background: #f1f5f9;
    }

    .user-avatar-initials {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
        color: #ffffff;
        font-weight: 700;
        font-size: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.15);
        letter-spacing: 0.5px;
        flex-shrink: 0;
    }

    .user-avatar-initials.crimson-tint {
        background: linear-gradient(135deg, #881337 0%, #be123c 100%);
    }

    .user-avatar-initials.emerald-tint {
        background: linear-gradient(135deg, #065f46 0%, #059669 100%);
    }

    /* Security Consequence Notices */
    .security-notice-callout {
        background: #fff1f2;
        border: 1px solid #fecdd3;
        border-radius: 10px;
        color: #9f1239;
        font-size: 12.5px;
        line-height: 1.5;
    }

    .security-notice-callout-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        color: #166534;
        font-size: 12.5px;
        line-height: 1.5;
    }

    /* Action Buttons in Modal */
    .btn-sentinel-cancel {
        background: #ffffff !important;
        color: #475569 !important;
        border: 1px solid #cbd5e1 !important;
        font-weight: 600 !important;
        border-radius: 9px !important;
        padding: 9px 20px !important;
        transition: all 0.2s ease !important;
    }

    .btn-sentinel-cancel:hover {
        background: #f8fafc !important;
        color: #1e293b !important;
        border-color: #94a3b8 !important;
    }

    .btn-sentinel-confirm-danger {
        background: linear-gradient(135deg, var(--sentinel-crimson-main) 0%, var(--sentinel-crimson-deep) 100%) !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        border: none !important;
        border-radius: 9px !important;
        padding: 9px 22px !important;
        box-shadow: 0 4px 14px rgba(225, 29, 72, 0.32) !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .btn-sentinel-confirm-danger:hover {
        background: linear-gradient(135deg, #f43f5e 0%, var(--sentinel-crimson-main) 100%) !important;
        box-shadow: 0 6px 20px rgba(225, 29, 72, 0.45) !important;
        transform: translateY(-1px);
        color: #ffffff !important;
    }

    .btn-sentinel-confirm-success {
        background: linear-gradient(135deg, var(--sentinel-emerald-main) 0%, var(--sentinel-emerald-deep) 100%) !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        border: none !important;
        border-radius: 9px !important;
        padding: 9px 22px !important;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.32) !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .btn-sentinel-confirm-success:hover {
        background: linear-gradient(135deg, #34d399 0%, var(--sentinel-emerald-main) 100%) !important;
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45) !important;
        transform: translateY(-1px);
        color: #ffffff !important;
    }

    /* Table Action Buttons */
    .btn-action-deactivate {
        background: #fff1f2;
        color: #be123c;
        border: 1px solid #fecdd3;
        font-weight: 600;
        font-size: 13px;
        padding: 5px 12px;
        border-radius: 7px;
        transition: all 0.18s ease;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
    }

    .btn-action-deactivate:hover {
        background: #e11d48;
        color: #ffffff;
        border-color: #e11d48;
        box-shadow: 0 3px 10px rgba(225, 29, 72, 0.25);
        transform: translateY(-1px);
    }

    .btn-action-activate {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
        font-weight: 600;
        font-size: 13px;
        padding: 5px 12px;
        border-radius: 7px;
        transition: all 0.18s ease;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
    }

    .btn-action-activate:hover {
        background: #10b981;
        color: #ffffff;
        border-color: #10b981;
        box-shadow: 0 3px 10px rgba(16, 185, 129, 0.25);
        transform: translateY(-1px);
    }

    /* Status Pills */
    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.2px;
    }

    .status-pill-active {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .status-pill-active::before {
        content: '';
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #10b981;
        margin-right: 6px;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
    }

    .status-pill-inactive {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
    }

    .status-pill-inactive::before {
        content: '';
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #94a3b8;
        margin-right: 6px;
    }
</style>
@endpush

@section('content')
<h1 class="text-center mb-4 font-weight-bold" style="color: #333;">QUẢN LÝ TÀI KHOẢN</h1>

<div class="d-flex justify-content-between align-items-center mb-3">
    <form action="{{ route('admin.users.index') }}" method="GET" class="form-inline">
        <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
        <div class="form-group">
            <input type="text" name="searchString" value="{{ request('searchString', request('q')) }}" class="form-control" placeholder="Tìm theo tên đăng nhập hoặc email..." />
        </div>
        <button type="submit" class="btn btn-primary ml-2">Tìm kiếm</button>
        @if(request('searchString') || request('q'))
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary ml-2">Đặt lại</a>
        @endif
    </form>
    <p class="text-right mb-0">
        <a href="{{ route('admin.users.create') }}" class="btn btn-success">
            <i class="fas fa-user-plus mr-1"></i> Tạo tài khoản mới
        </a>
    </p>
</div>

<div class="table-container p-0 border-0 shadow-sm" style="overflow: hidden; border-radius: 8px;">
    <div class="table-responsive">
        <table class="table table-light table-striped table-hover mb-0">
            <thead>
                <tr class="thead-light">
                    <th>Họ Tên</th>
                    <th>Tên Đăng Nhập</th>
                    <th>Email</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th style="min-width: 140px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $item)
                    @php
                        $isAdmin = $item->isAdmin();
                        $isActive = $item->is_active ?? true;
                        $isSelf = (string)$item->getKey() === (string)auth()->id();
                    @endphp
                    <tr>
                        <td><strong>{{ $item->name }}</strong></td>
                        <td>{{ $item->username }}</td>
                        <td>{{ $item->email }}</td>
                        <td>
                            @if($isActive)
                                <span class="status-pill status-pill-active">Hoạt động</span>
                            @else
                                <span class="status-pill status-pill-inactive">Vô hiệu hóa</span>
                            @endif
                        </td>
                        <td>{{ $item->created_at ? $item->created_at->format('d/m/Y') : '—' }}</td>
                        <td>
                            @if ($isSelf)
                                <span class="badge badge-info px-2 py-1" title="Tài khoản hiện tại của bạn">
                                    <i class="fas fa-user-circle mr-1"></i>Bạn
                                </span>
                            @elseif ($isAdmin)
                                <span class="badge badge-primary px-2 py-1">
                                    <i class="fas fa-shield-alt mr-1"></i>Admin
                                </span>
                            @else
                                @if ($isActive)
                                    <button type="button"
                                            class="btn-action-deactivate"
                                            data-action="{{ route('admin.users.toggle-active', $item) }}"
                                            data-name="{{ $item->name }}"
                                            data-username="{{ $item->username }}"
                                            data-email="{{ $item->email }}"
                                            title="Vô hiệu hóa tài khoản">
                                        <i class="fas fa-user-slash mr-1"></i> Vô hiệu hóa
                                    </button>
                                @else
                                    <button type="button"
                                            class="btn-action-activate"
                                            data-action="{{ route('admin.users.toggle-active', $item) }}"
                                            data-name="{{ $item->name }}"
                                            data-username="{{ $item->username }}"
                                            data-email="{{ $item->email }}"
                                            title="Kích hoạt lại tài khoản">
                                        <i class="fas fa-user-check mr-1"></i> Kích hoạt
                                    </button>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Không tìm thấy tài khoản nào phù hợp.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<x-admin.pagination :paginator="$users" label="tài khoản" />
@endsection

@push('modals')
<!-- Modal Xác nhận Vô hiệu hóa Tài khoản (Crimson Sentinel Refinement) -->
<div class="modal fade" id="deactivateUserModal" tabindex="-1" role="dialog" aria-labelledby="deactivateUserModalLabel" aria-hidden="true" style="backdrop-filter: blur(6px); background-color: rgba(15, 23, 42, 0.65);">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 460px;">
        <div class="modal-content sentinel-modal-content">
            <div class="modal-body p-4 text-center">
                <!-- Sentinel Shield Badge with Ambient Pulse -->
                <div class="d-flex justify-content-center mb-3 mt-1">
                    <span class="sentinel-icon-badge">
                        <i class="fas fa-user-slash"></i>
                    </span>
                </div>

                <!-- Tiêu đề & Thông điệp -->
                <h4 class="font-weight-bold text-dark mb-1" id="deactivateUserModalLabel" style="font-size: 21px; letter-spacing: -0.015em;">
                    Xác nhận vô hiệu hóa tài khoản
                </h4>
                <p class="text-muted small mb-3 px-2">
                    Tài khoản này sẽ bị tạm ngưng truy cập vào hệ thống ngay tức thì.
                </p>

                <!-- User Identity Dossier Capsule -->
                <div class="user-dossier-capsule p-3 mb-3 text-left">
                    <div class="d-flex align-items-center pb-2 mb-2 border-bottom">
                        <div class="user-avatar-initials crimson-tint mr-3" id="deactivate-modal-avatar">
                            U
                        </div>
                        <div class="overflow-hidden">
                            <div class="font-weight-bold text-dark text-truncate" id="deactivate-modal-name" style="font-size: 15px;">
                                —
                            </div>
                            <div class="text-muted small text-truncate" id="deactivate-modal-username">
                                @—
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small"><i class="fas fa-envelope mr-1 text-secondary"></i> Email:</span>
                        <span class="font-weight-bold text-dark text-truncate ml-2 small" id="deactivate-modal-email" style="max-width: 240px;">—</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted small"><i class="fas fa-toggle-on mr-1 text-secondary"></i> Trạng thái mới:</span>
                        <div class="d-flex align-items-center font-weight-bold">
                            <span class="badge badge-success px-2 py-1">Hoạt động</span>
                            <i class="fas fa-arrow-right mx-2 text-danger" style="font-size: 10px;"></i>
                            <span class="badge badge-danger px-2 py-1"><i class="fas fa-ban mr-1"></i>Vô hiệu hóa</span>
                        </div>
                    </div>
                </div>

                <!-- Security Consequence Notice -->
                <div class="security-notice-callout p-2 mb-4 text-left d-flex align-items-start">
                    <i class="fas fa-shield-alt mr-2 mt-1 text-danger" style="font-size: 14px;"></i>
                    <div>
                        Người dùng sẽ không thể đăng nhập hoặc đặt hàng sau khi thao tác này hoàn tất. Bạn có thể kích hoạt lại tài khoản bất kỳ lúc nào.
                    </div>
                </div>

                <!-- Action Controls -->
                <div class="d-flex align-items-center justify-content-center" style="gap: 12px;">
                    <button type="button" class="btn btn-sentinel-cancel" data-dismiss="modal">
                        Hủy bỏ
                    </button>
                    <form id="deactivate-user-form" method="POST" action="" style="display: inline-block; margin: 0;">
                        @csrf
                        <button type="submit" class="btn btn-sentinel-confirm-danger">
                            <i class="fas fa-user-slash mr-1"></i> Xác nhận vô hiệu hóa
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Xác nhận Kích hoạt lại Tài khoản (Emerald Vitality Refinement) -->
<div class="modal fade" id="activateUserModal" tabindex="-1" role="dialog" aria-labelledby="activateUserModalLabel" aria-hidden="true" style="backdrop-filter: blur(6px); background-color: rgba(15, 23, 42, 0.65);">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 460px;">
        <div class="modal-content sentinel-modal-content">
            <div class="modal-body p-4 text-center">
                <!-- Emerald Vitality Badge -->
                <div class="d-flex justify-content-center mb-3 mt-1">
                    <span class="sentinel-icon-badge-emerald">
                        <i class="fas fa-user-check"></i>
                    </span>
                </div>

                <!-- Tiêu đề & Thông điệp -->
                <h4 class="font-weight-bold text-dark mb-1" id="activateUserModalLabel" style="font-size: 21px; letter-spacing: -0.015em;">
                    Xác nhận kích hoạt tài khoản
                </h4>
                <p class="text-muted small mb-3 px-2">
                    Khôi phục đầy đủ quyền truy cập và giao dịch của người dùng trên hệ thống.
                </p>

                <!-- User Identity Dossier Capsule -->
                <div class="user-dossier-capsule p-3 mb-3 text-left">
                    <div class="d-flex align-items-center pb-2 mb-2 border-bottom">
                        <div class="user-avatar-initials emerald-tint mr-3" id="activate-modal-avatar">
                            U
                        </div>
                        <div class="overflow-hidden">
                            <div class="font-weight-bold text-dark text-truncate" id="activate-modal-name" style="font-size: 15px;">
                                —
                            </div>
                            <div class="text-muted small text-truncate" id="activate-modal-username">
                                @—
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small"><i class="fas fa-envelope mr-1 text-secondary"></i> Email:</span>
                        <span class="font-weight-bold text-dark text-truncate ml-2 small" id="activate-modal-email" style="max-width: 240px;">—</span>
                    </div>

                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted small"><i class="fas fa-toggle-on mr-1 text-secondary"></i> Trạng thái mới:</span>
                        <div class="d-flex align-items-center font-weight-bold">
                            <span class="badge badge-secondary px-2 py-1">Vô hiệu hóa</span>
                            <i class="fas fa-arrow-right mx-2 text-success" style="font-size: 10px;"></i>
                            <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i>Hoạt động</span>
                        </div>
                    </div>
                </div>

                <!-- Activation Consequence Notice -->
                <div class="security-notice-callout-success p-2 mb-4 text-left d-flex align-items-start">
                    <i class="fas fa-check-circle mr-2 mt-1 text-success" style="font-size: 14px;"></i>
                    <div>
                        Người dùng có thể đăng nhập bình thường và tiếp tục đặt hàng, quản lý đơn hàng như trước.
                    </div>
                </div>

                <!-- Action Controls -->
                <div class="d-flex align-items-center justify-content-center" style="gap: 12px;">
                    <button type="button" class="btn btn-sentinel-cancel" data-dismiss="modal">
                        Hủy bỏ
                    </button>
                    <form id="activate-user-form" method="POST" action="" style="display: inline-block; margin: 0;">
                        @csrf
                        <button type="submit" class="btn btn-sentinel-confirm-success">
                            <i class="fas fa-user-check mr-1"></i> Xác nhận kích hoạt
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    $(document).ready(function () {
        function extractInitials(name) {
            if (!name) return 'U';
            var words = name.trim().split(/\s+/);
            if (words.length === 1) {
                return words[0].substring(0, 2).toUpperCase();
            }
            return (words[0].charAt(0) + words[words.length - 1].charAt(0)).toUpperCase();
        }

        // Mở popup xác nhận vô hiệu hóa tài khoản
        $(document).on('click', '.btn-action-deactivate', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var action = $btn.data('action');
            var name = $btn.data('name') || 'Tài khoản';
            var username = $btn.data('username') || '';
            var email = $btn.data('email') || '—';

            $('#deactivate-modal-name').text(name);
            $('#deactivate-modal-username').text(username ? '@' + username : '');
            $('#deactivate-modal-email').text(email);
            $('#deactivate-modal-avatar').text(extractInitials(name));
            $('#deactivate-user-form').attr('action', action);

            $('#deactivateUserModal').modal('show');
        });

        // Mở popup xác nhận kích hoạt tài khoản
        $(document).on('click', '.btn-action-activate', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var action = $btn.data('action');
            var name = $btn.data('name') || 'Tài khoản';
            var username = $btn.data('username') || '';
            var email = $btn.data('email') || '—';

            $('#activate-modal-name').text(name);
            $('#activate-modal-username').text(username ? '@' + username : '');
            $('#activate-modal-email').text(email);
            $('#activate-modal-avatar').text(extractInitials(name));
            $('#activate-user-form').attr('action', action);

            $('#activateUserModal').modal('show');
        });
    });
</script>
@endpush

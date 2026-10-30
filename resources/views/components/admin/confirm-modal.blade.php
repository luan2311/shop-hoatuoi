@once
    <p class="alert alert-warning" data-confirm-unavailable role="status">Các thao tác cần xác nhận chỉ khả dụng khi JavaScript và hộp thoại đã sẵn sàng. Nếu nút bị khóa, hãy bật JavaScript và tải lại trang.</p>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/admin-confirm-modal.css') }}">
    @endpush
    @push('modals')
        <div class="modal fade admin-confirm-modal" id="adminConfirmModal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="adminConfirmTitle" aria-describedby="adminConfirmMessage adminConfirmSubject adminConfirmError" data-backdrop="true" data-keyboard="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <button type="button" class="admin-confirm-close" data-confirm-dismiss aria-label="Đóng"><span aria-hidden="true">&times;</span></button>
                    <div class="modal-body">
                        <span class="admin-confirm-icon" aria-hidden="true"><i data-confirm-icon class="fas fa-exclamation-circle"></i></span>
                        <h2 id="adminConfirmTitle"></h2>
                        <p id="adminConfirmMessage"></p>
                        <p id="adminConfirmSubject" class="admin-confirm-subject" hidden></p>
                        <p id="adminConfirmError" class="admin-confirm-error" role="alert" hidden></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn admin-confirm-cancel" data-confirm-dismiss>Hủy</button>
                        <button type="button" class="btn btn-danger" data-confirm-accept>Xác nhận</button>
                    </div>
                </div>
            </div>
        </div>
    @endpush
    @push('scripts')
        <script src="{{ asset('js/admin-confirm-modal.js') }}"></script>
    @endpush
@endonce

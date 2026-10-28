@extends('layouts.admin')

@section('title', 'Chi Tiết Liên Hệ')

@section('content')
<div class="card p-4 border-0 shadow-sm" style="display: block; border-radius: 8px;">
    <h2>Chi Tiết Liên Hệ</h2>
    <hr />

    <dl class="row mt-3">
        <dt class="col-sm-3 text-muted">Mã Liên Hệ</dt>
        <dd class="col-sm-9 font-weight-bold text-dark">{{ $contact->code ?? 'LH' }}</dd>

        <dt class="col-sm-3 text-muted">Họ Tên</dt>
        <dd class="col-sm-9 font-weight-bold">{{ $contact->full_name }}</dd>

        <dt class="col-sm-3 text-muted">Email</dt>
        <dd class="col-sm-9"><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></dd>

        <dt class="col-sm-3 text-muted">Số Điện Thoại</dt>
        <dd class="col-sm-9"><a href="tel:{{ $contact->phone }}">{{ $contact->phone }}</a></dd>

        <dt class="col-sm-3 text-muted">Ngày Gửi</dt>
        <dd class="col-sm-9">{{ $contact->created_at ? $contact->created_at->format('d/m/Y H:i') : '—' }}</dd>

        <dt class="col-sm-3 text-muted">Nội Dung</dt>
        <dd class="col-sm-9">
            <div class="p-3 bg-light rounded border text-dark" style="white-space: pre-wrap; font-size: 15px;">{{ $contact->message }}</div>
        </dd>
    </dl>

    <div class="mt-4 pt-3 border-top d-flex gap-2">
        <form method="POST" action="{{ route('admin.contacts.destroy', $contact) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa liên hệ này?');" style="display:inline-block;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash mr-1"></i> Xóa
            </button>
        </form>

        <a href="{{ route('admin.contacts.index') }}" class="btn btn-secondary ml-2">
            &laquo; Trở về danh sách
        </a>
    </div>
</div>
@endsection

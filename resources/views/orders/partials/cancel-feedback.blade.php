@if(session('status'))
    <div class="container mt-3"><p role="status" class="alert alert-success">{{ session('status') }}</p></div>
@endif
@if($errors->any())
    <div class="container mt-3"><div role="alert" class="alert alert-danger">@foreach($errors->all() as $error)<p class="mb-0">{{ $error }}</p>@endforeach</div></div>
@endif

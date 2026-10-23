@if(session('success'))
    <div class="rec-alert success"><i class="fa-solid fa-circle-check"></i><span>{{ session('success') }}</span></div>
@endif
@if($errors->any())
    <div class="rec-alert danger"><i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
@endif

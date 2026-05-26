@section('title')
    {{ __('Profile') }}
@endsection
<x-core::layouts.admin :$model>
    {!! BootForm::open()->put()->action(route('admin::update-profile'))->addClass('form') !!}

    {!! BootForm::bind($model->toArray()) !!}
    @include('admin::users._profile_form')
    {!! BootForm::close() !!}
</x-core::layouts.admin>

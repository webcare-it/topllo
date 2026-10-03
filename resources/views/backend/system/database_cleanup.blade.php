@extends('backend.layouts.app')

@section('content')

<h4 class="text-center text-muted">{{translate('Reset Demo Data')}}</h4>

<div class="row justify-content-center">
    <div class="col-lg-8">

        @if(!$enabled)
            <div class="alert alert-warning">
                {{ translate('This tool is currently disabled. Set') }} <code>ALLOW_DB_RESET=true</code> {{ translate('in the .env file to enable it.') }}
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('This will permanently delete:') }}</h5>
            </div>
            <div class="card-body">
                <ul class="mb-3">
                    @foreach($tables as $table)
                        <li>{{ $table }}</li>
                    @endforeach
                </ul>
                <div class="alert alert-danger mb-0">
                    {{ translate('This includes ALL products and categories — the storefront catalog will become empty. Admin/staff accounts, business settings, payment gateway configuration and brands are NOT touched. This action cannot be undone — make sure you have a database backup before proceeding.') }}
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Confirm reset') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('database-cleanup.run') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>{{ translate('Type') }} <strong>{{ $phrase }}</strong> {{ translate('to confirm') }}</label>
                        <input type="text" name="confirmation" class="form-control" autocomplete="off" required {{ $enabled ? '' : 'disabled' }}>
                    </div>

                    <div class="form-group">
                        <label>{{ translate('Your password') }}</label>
                        <input type="password" name="password" class="form-control" autocomplete="current-password" required {{ $enabled ? '' : 'disabled' }}>
                    </div>

                    <button type="submit" class="btn btn-danger" {{ $enabled ? '' : 'disabled' }}
                        onclick="return confirm('{{ translate('This will permanently delete demo data. Are you sure?') }}');">
                        {{ translate('Permanently delete demo data') }}
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection

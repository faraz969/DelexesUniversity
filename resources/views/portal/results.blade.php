@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Application Results</h3>
    @if($application)
        <p><strong>Status:</strong> {{ ucfirst(str_replace('_',' ', $application->status)) }}</p>
        @if($application->status === 'successful')
            <a href="#" class="btn btn-primary">Print Application</a>
        @endif
    @else
        <div class="alert alert-warning">No application found.</div>
    @endif
</div>
@endsection


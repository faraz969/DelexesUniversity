@extends('layouts.app')

@section('content')
<div class="container ">
    <h3>My Application</h3>
    <div class="alert alert-info">Fill the admission form and save as draft or submit.</div>
    <form method="post" action="{{ route('portal.application.save') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Form Type</label>
            <select class="form-select" name="form_type">
                <option value="undergraduate">Undergraduate</option>
                <option value="postgraduate">Postgraduate</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Applicant Type</label>
            <select class="form-select" name="applicant_type">
                <option value="regular">Regular</option>
                <option value="matured">Matured</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Additional Notes</label>
            <textarea class="form-control" name="notes" rows="4"></textarea>
        </div>
        <button type="submit" class="btn btn-secondary">Save as Draft</button>
    </form>
    <form class="mt-3" method="post" action="{{ route('portal.application.submit') }}">
        @csrf
        <button type="submit" class="btn btn-primary">Submit Application</button>
    </form>
</div>
@endsection


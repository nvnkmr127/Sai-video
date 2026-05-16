@extends('layouts.app')

@section('content')
<div class="card">
    <h1>Workshop Registration</h1>
    <p class="subtitle">Join our upcoming workshop. Fill in your details below to reserve your spot.</p>

    <form action="{{ route('registration.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" name="name" id="name" placeholder="John Doe" required value="{{ old('name') }}">
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" placeholder="john@example.com" required value="{{ old('email') }}">
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" name="phone" id="phone" placeholder="+1 (555) 000-0000" required value="{{ old('phone') }}">
        </div>

        <div class="form-group">
            <label for="organization">Organization / Company (Optional)</label>
            <input type="text" name="organization" id="organization" placeholder="ACME Inc." value="{{ old('organization') }}">
        </div>

        <div style="margin-top: 2rem;">
            <button type="submit" class="btn">Register Now</button>
        </div>
    </form>
</div>
@endsection

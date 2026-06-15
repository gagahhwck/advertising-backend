@extends('layouts.email')

@section('title', $subject)

@section('content')
<div style="padding: 1.5rem;">
    {!! $body !!}
</div>
@endsection

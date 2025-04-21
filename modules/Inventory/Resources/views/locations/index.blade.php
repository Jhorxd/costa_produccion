@extends('tenant.layouts.app')

@section('content')

    <location-index :type-user="{{ json_encode(auth()->user()->type) }}"></location-index>

@endsection
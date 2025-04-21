@extends('tenant.layouts.app')

@section('content')

    <inventory-transfers-index
        :type-user="{{json_encode(Auth::user()->type)}}"
    ></inventory-transfers-index>

@endsection
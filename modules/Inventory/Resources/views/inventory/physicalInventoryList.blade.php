@extends('tenant.layouts.app')

@section('content')

<physical-inventory-list
    :type-user="{{json_encode(Auth::user()->type)}}"
></physical-inventory-list>

@endsection
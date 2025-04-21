@extends('tenant.layouts.app')

@section('content')

<physical-inventory :inventory="{{ json_encode($inventory) }}"></physical-inventory>

@endsection
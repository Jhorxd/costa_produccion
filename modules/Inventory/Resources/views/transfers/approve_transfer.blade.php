@extends('tenant.layouts.app')

@section('content')

   <inventory-approve-transfer
        :resource-id="{{json_encode($resourceId)}}"
   ></inventory-approve-transfer>


@endsection
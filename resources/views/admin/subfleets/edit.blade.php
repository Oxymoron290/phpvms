@extends('admin.app')

@section('title', "Edit $subfleet->name")
@section('content')
   <div class="card border-blue-bottom">
       <div class="content">
           {!! Form::model($subfleet, ['route' => ['admin.subfleets.update', $subfleet->id], 'method' => 'patch']) !!}
            @include('admin.subfleets.fields')
           {!! Form::close() !!}
       </div>
   </div>

   <div class="card border-blue-bottom">
       <div class="content">
           @include('admin.subfleets.ranks')
       </div>
   </div>

   <div class="card border-blue-bottom">
       <div class="content">
           @include('admin.subfleets.fares')
       </div>
   </div>

   <div class="card border-blue-bottom">
       <div class="content">
           @include('admin.subfleets.expenses')
       </div>
   </div>
@endsection
@include('admin.subfleets.script')

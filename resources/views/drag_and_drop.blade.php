@extends('layouts.app')

@section('content')

<div class="container">
	<clone-component v-bind:lists="{{json_encode($lists)}}"></clone-component>
</div>
@endsection

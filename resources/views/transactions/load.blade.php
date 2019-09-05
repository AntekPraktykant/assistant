@extends('partials/master')

@section('content')
    <form action="{!! route('loadtransaction') !!}" method="post" enctype="multipart/form-data">
        {{ csrf_field() }}
        <label for="file">Load a html Interactive Brokers Daily Trade Report you wish to process</label>
        <input type="file" name="files[]" id="file" accept="text/html" multiple>
        <input type="submit" value="Wczytaj">
    </form>
@endsection
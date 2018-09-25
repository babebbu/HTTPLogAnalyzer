@extends('layouts.template')

@section('head')
    <script src="{{ url('/') }}/assets/js/plugins/codemirror/codemirror.js"></script>
    <link rel="stylesheet" href="{{ url('/') }}/assets/css/plugins/codemirror/codemirror.css">
    <script src="{{ url('/') }}/assets/js/plugins/codemirror/javascript.js"></script>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <select>
                <option>GET</option>
                <option>POST</option>
                <option>PUT</option>
                <option>PATCH</option>
                <option>DELETE</option>
                <option>OPTIONS</option>
            </select>
            <input type="text" name="endpoint" style="min-width: 69%;">
            <button>Run</button>
            <br><br>
            <small>ElasticSearch QueryString (Search API)</small>
            <div id="editor"></div>
        </div>
        <div class="col-lg-6">
            <div id="response">

            </div>
        </div>
    </div>

    <style type="text/css">
        .CodeMirror {
            border: 1px solid #ddd;
            min-height: 60px;
            height: auto;
        }
        #response{
            background: white;
            border: 1px solid #ddd;
            min-height: 600px;
        }
    </style>
@endsection

@section('script')
    <script>
        var editor = CodeMirror(document.getElementById("editor"), {
            lineNumbers: true
        });
    </script>
@endsection
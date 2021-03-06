<?php $nivel = '../' ?>
@extends('layouts.app')

@section('content')
<div class="contenedor">
  <div class="principal">
    <div class="titulo">
      <h3>
        Agregar Beacons
      </h3>
    </div>

    <div class="form">
      <form class="form-horizontal" role="form" method="POST" action="{{ route('store_beacon') }}">
        {{ csrf_field() }}
        {{ method_field('PUT') }}

        <div class="input no_icon {{ $errors->has('major') ? 'error' : '' }}">
          <input type="text" name="major" value="" required="">
          <label for="">
            <span class="text">Major</span>
          </label>
          <div class="help">
            <a href="#">
              <i class="material-icons">help_outline</i>
            </a>
            <div class="inf none hidden">
              <p>
                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris
              </p>
            </div>
          </div>
        </div>
        @if ($errors->has('major'))
          <div class="input_error">
              <span>{{ $errors->first('major') }}</span>
          </div>
        @endif
        <div class="input no_icon {{ $errors->has('minor') ? 'error' : '' }}">
          <input type="text" name="minor" value="" required="">
          <label for="">
            <span class="text">Minor</span>
          </label>
          <div class="help">
            <a href="#">
              <i class="material-icons">help_outline</i>
            </a>
            <div class="inf none hidden">
              <p>
                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris
              </p>
            </div>
          </div>
        </div>
        @if ($errors->has('minor'))
          <div class="input_error">
              <span>{{ $errors->first('minor') }}</span>
          </div>
        @endif
        <div class="button">
          <center>
            <button type="submit" name="button">
              <span>Guardar</span>
            </button>
            <a href="{{ route('all_beacons') }}" class="">
              <span>Cancelar</span>
            </a>
          </center>
        </div>
      </form>

    </div>
  </div>
</div>
@endsection

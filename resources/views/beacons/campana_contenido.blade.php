<?php $nivel = '../../../' ?>
@extends('layouts.app')

@section('content')

<div class="contenedor">
  <div class="principal">
    <div class="titulo">
      <h3>
        Agregar Contenido de Planificación
      </h3>
    </div>

    <div class="form">

      <form class="form-horizontal" role="form" method="POST" action="{{ route('store_campana_content', $campana_id) }}">
        {{ csrf_field() }}
        <input type="hidden" name="tigger_name_id" value="DWELL_TIME">



        <div class="input select {{ $errors->has('coupon_id') ? 'error' : '' }}">
          <select id="coupon_id" class="form-control icons" name="coupon_id" required>
            <option value="" disabled selected>Seleccione un Menú</option>
            @foreach($coupons as $c)
                <option value="{{$c->coupon_id}}">
                    @if( ! empty($c->coupon_translation[0]) )
                      {{$c->coupon_translation[0]->name}}
                    @endif</option>
            @endforeach
          </select>
        </div>
        @if ($errors->has('coupon_id'))
        <div class="input_error">
          <span>{{ $errors->first('coupon_id') }}</span>
        </div>
        @endif

        <div class="input select {{ $errors->has('timeframe_id') ? 'error' : '' }}">
          <select id="timeframe_id" class="form-control icons" name="timeframe_id" required>
            <option value="" disabled selected>Seleccione un Horario</option>
            <option value="ALL">All</option>
            @foreach($timeframes as $t)
                <option value="{{$t->timeframe_id}}">{{$t->name}}</option>
            @endforeach
          </select>
        </div>
        @if ($errors->has('timeframe_id'))
        <div class="input_error">
          <span>{{ $errors->first('timeframe_id') }}</span>
        </div>
        @endif

        <div class="input no_icon {{ $errors->has('xxxxxx') ? 'error' : '' }}">
          <input type="number" name="xxxxxx" min="0" value="" class="input_time number" required="true">
          <label for="">
            <!-- <span class="icon"><img src="img/icons/correo.png" alt=""></span> -->
            <span class="text">Minutos de espera</span>
          </label>
        </div>
        @if ($errors->has('xxxxxx'))
        <div class="input_error">
          <span>{{ $errors->first('xxxxxx') }}</span>
        </div>
        @endif

        <div class="button">
          <center>
            <button type="submit" name="button">
              <span>Guardar</span>
            </button>
            <a href="{{ route('show_campana') }}" class="">
              <span>Cancelar</span>
            </a>
          </center>
        </div>
      </form>

    </div>
  </div>
</div>

@endsection

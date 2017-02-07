@extends('layouts.app')

@section('content')
  <div class="contenedor">
    <div class="principal">
      <ul class="links">
        <li>
          <a href="{{ route('all_coupon') }}">
            <img src="img/icons/menu.png" title="Carta">
          </a>
        </li>
        <li>
          <a href="{{ route('all_timeframe')}}">
            <img src="img/icons/horarios.png" title="Horarios">
          </a>
        </li>
        <li>
          <a href="{{ route('all_campana')}}">
            <img src="img/icons/plan.png" title="Planificación">
          </a>
        </li>
        <li>
          <a href="{{ route('all_type_plate')}}">
            <img src="img/icons/servicio.png" title="Servicio">
          </a>
        </li>
        <li>
          <a href="{{ route('all_language') }}">
            <img src="img/icons/idiomas.png" title="Idiomas">
          </a>
        </li>
        <li>
          <a href="#" onclick="return false;">
            <img src="img/icons/promociones.png" title="Promociones">
          </a>
          <ul>
            <li><a href="{{ route('all_welcome_kit') }}"><span>Kit de <br>Bienvenida</span></a></li>
            <li><a href="{{ route('all_fidelity_kit') }}"><span>Kit de <br>Fidelidad</span></a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>

@endsection

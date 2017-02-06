<?php $nivel = '../../'?>

@extends('layouts.app')

@section('content')
<div class="contenedor">
	<div class="principal">
		<div class="titulo">
			<h3>
				Platos de la carta
			</h3>
		</div>

		<div class="agregar">
			<center>
				<a href="#platosMenu" class="waves-effect">
					<div class="">
						<span class="text">Agregar <br><strong>Platos de<br>la Carta</strong></span>
						<span class="icon"><i class="material-icons">add</i></span>
					</div>
				</a>
			</center>
		</div>

		<div class="beacons seccion">
			<div class="container">
				<div class="tabla">
					<table class="bordered centered">
						<thead>
							<tr>
								<th data-field="name">Nombre</th>
								<th data-field="type">Tipo</th>
								<th data-field="price">Precio</th>
								<th data-field="name" width="100px">Detalles</th>
								<th data-field="price" width="100px">Editar</th>
								<th data-field="price" width="100px">Eliminar</th>
								<th data-field="id" width="130px">Habilitado</th>

							</tr>
						</thead>
						<tbody>
							@foreach($menus as $m)
								<tr id='{{$m->id}}'>
									<td>
										@if( ! empty($m->menu_translation[0]) )
											{{$m->menu_translation[0]->name}}
										@endif
									</td>
									<td>{{$m->type}}</td>
									<td>
										@if( empty($coupon->price) )
											{{$m->price}} €
										@else
											{{$coupon->price}} €
										@endif
									</td>
									<td><a href="{{ route('show_plate', $m->id) }}"><i class="material-icons">input</i></a></td>

									<!-- <td><a href="#Idioma"><i class="material-icons">language</i></a></td> -->
									<td><a href="{{ route( 'edit_menu', array('menu_id' => $m->id) ) }}"><i class="material-icons">edit</i></a></td>
									<?php

									echo "<td onclick= \"modal_activate('".
									route( "destroy_menu", $m->id ).
									"' , '#eliminarPlato')\" >";

									?>
									<a href="#eliminarPlato"><i class="material-icons">clear</i></a>
								</td>
								<td>
									<div class="switch">
										<label>
											Si
											<input type="checkbox">
											<span class="lever"></span>
											No
										</label>
									</div>
								</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<div class="agregar regresar">
			<center>
				<a href="{{ route('all_section', $coupon->coupon_id) }}" class="waves-effect">
					<div class="">
						<span class="text">Regresar</span>
						<span class="icon"><i class="material-icons">reply</i></span>
					</div>
				</a>
			</center>
		</div>

	</div>
</div>
<div id="platosMenu" class="modal modal_">
	<div class="titulo">
		<h3>
			Agregar Plato
		</h3>
	</div>

	<div class="form">
		<form class="form-horizontal" role="form" method="POST" action="{{ route('store_menu') }}">
			{{ csrf_field() }}
			<input type="hidden" name="section_id" value="{{$section_id}}" required>



			<div class="input no_icon {{ $errors->has('name') ? 'error' : '' }}">
				<input type="text" name="name" value="" required="">
				<label for="">
					<span class="text">Nombre</span>
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
			@if ($errors->has('name'))
			<div class="input_error">
				<span>{{ $errors->first('name') }}</span>
			</div>
			@endif

			<div class="input select no_icon {{ $errors->has('type') ? 'error' : '' }}">
				<!-- <img src="img/icons/idioma.png" alt="" class="icon"> -->
				<select id="type" class="form-control icons" name="type">

				@if( !empty($type_plates) )
          <option value="" disabled selected>Seleccion un tipo de plato</option>
					@foreach ($type_plates as $type_plate)
						<option value="{{$type_plate->id}}">{{$type_plate->name}}</option>
					@endforeach
				@else
					<option value="" disabled selected>No hay tipos de platos registrados</option>
				@endif
				</select>
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
			@if ($errors->has('type'))
			<div class="error_input">
				<span>{{ $errors->first('type') }}</span>
			</div>
			@endif

			<div class="input no_icon {{ $errors->has('price') ? 'error' : '' }}">
				<input type="number" name="price" value="" required="" min="1" max="99999" step="any">
				<label for="">
					<span class="text">Precio 0,00 €</span>
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
			@if ($errors->has('price'))
				<div class="input_error">
						<span>{{ $errors->first('price') }}</span>
				</div>
			@endif
			<div class="button">
				<center>
					<button type="submit" name="button">
						<span>Guardar</span>
					</button>
					<a href="#" class="" onclick="$('#platosMenu').modal('close'); return false;">
						<span>Cancelar</span>
					</a>
				</center>
			</div>
		</form>
	</div>
</div>
<div id="Idioma" class="modal modal_">
	<div class="titulo">
		<h3>
			Agregar Plato
		</h3>
	</div>

	<div class="form">
		<form class="form-horizontal" role="form" method="POST" action="{{ route('store_menu') }}">
			{{ csrf_field() }}
			<input type="hidden" name="section_id" value="{{$section_id}}" required>


			<div class="input select {{ $errors->has('type') ? 'error' : '' }}">
				<!-- <img src="img/icons/idioma.png" alt="" class="icon"> -->
				<select id="type" class="form-control icons" name="type" required>
					<option value="" disabled selected>Seleccione un Idioma</option>

					<option value="vegetariana">vegetariana</option>
					<option value="sin gluten">sin gluten</option>
					<option value="bja caloria">baja caloria</option>
					<option value="picante">picante</option>
				</select>

				@if ($errors->has('type'))
				<span class="error_input">
					<strong>{{ $errors->first('type') }}</strong>
				</span>
				@endif
			</div>

			<div class="input no_icon {{ $errors->has('name') ? 'error' : '' }}">
				<input type="text" name="name" value="" required="" >
				<label for="">
					<span class="text">Traduccion Plato</span>
				</label>
				@if ($errors->has('name'))
					<span class="error_input">
							<strong>{{ $errors->first('name') }}</strong>
					</span>
				@endif
			</div>


			<!-- <div class="input no_icon {{ $errors->has('price') ? 'error' : '' }}">
				<input type="text" name="price" value="" required="">
				<label for="">
					<span class="text">Precio</span>
				</label>
				@if ($errors->has('price'))
					<span class="error_input">
							<strong>{{ $errors->first('price') }}</strong>
					</span>
				@endif
			</div> -->
			<div class="button">
				<center>
					<button type="submit" name="button">
						<span>Guardar</span>
					</button>
					<a href="#" class="" onclick="$('#Idioma').modal('close'); return false;">
						<span>Cancelar</span>
					</a>
				</center>
			</div>
		</form>
	</div>
</div>
<div id="eliminarPlato" class="modal modal_">
	<div class="titulo">
		<h3>
			Esta seguro que desea eliminar Plato
		</h3>
	</div>
	<div class="form">
		<form class="form-horizontal" role="form" method="POST">
			{{ csrf_field() }}
			<input type="hidden" name="_method" value="DELETE">
			<div class="button">
				<center>
					<button type="submit" name="button">
						<span>Si</span>
					</button>
					<a href="#" class="" onclick="$('#eliminarPlato').modal('close'); return false;">
						<span>No</span>
					</a>
				</center>
			</div>
		</form>
	</div>
</div>


@endsection
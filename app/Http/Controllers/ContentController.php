<?php

namespace Beacon\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Beacon\Campana;
use Beacon\Content;
use Beacon\Coupon;
use Beacon\Promotion;
use Beacon\Tag;
use Beacon\Timeframe;
use Beacon\User;
use Illuminate\Support\Facades\Input;

class ContentController extends Controller
{
	/**
	 * @return token crud
	 */
	public function crud()
	{
		// Nuevo cliente con un url base
		$client = new Client();

		//Token Crud
		$response_crud = $client->request('POST', 'https://connect.onyxbeacon.com/oauth/client', [
				'form_params' => [
						'client_id' => 'af1cd006576dc09b7cf7660d4e010fbf434ad4bf',
						'client_secret' => '335c77e0ff4a4d36b97e8464ef880cdef30fb795',
						'scope' => 'crud'
				]
		]);

		$json_c = $response_crud->getBody();

		$token_crud = json_decode($json_c);

		return $token_crud->access_token;
	}


	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index($id)
	{

		$user = User::where( 'id', '=', Auth::user()->id )->first();

		$content = new Content;

		$contents = $content->where([
			['user_id', '=', $user->user_id],
			['campana_id', '=', $id],
		])->get();

		foreach ($contents as $key => $content) {
			$content->timeframes();

			$content_timeframes = DB::table('timeframes')
				->join('content_timeframes', 'timeframes.timeframe_id', '=', 'content_timeframes.timeframe_id')
				->join('contents', 'contents.id', '=', 'content_timeframes.content_id')
				->select('timeframes.*')
				->where('content_timeframes.content_id', '=', $content->id)
				->get();

				if ( count($content_timeframes) == 0 ) {
					$content->timeframes = null;
				}
				else
				{
					$content->timeframes = $content_timeframes;
				}

		}

		// echo "<pre>";	var_dump($contents);	echo "</pre>";

		$coupon = new Coupon;

		$coupons = $coupon->where([
			['user_id', '=', $user->user_id],
		])->get();

		foreach ($coupons as $key => $coupon) {
			$coupon->coupon_translation;
		}
		//$coupon->coupon_translation;
		//echo "<pre>";var_dump($coupon);echo "</pre>";

		$tags = Tag::where('user_id', '=', $user->user_id)->get();

		$timeframes = Timeframe::where('user_id', '=', $user->user_id)->get();

		return view('contents.content',
					[
						'coupons' => $coupons,
						'contents' => $contents,
						'tags' => $tags,
						'timeframes' => $timeframes,
						'campana_id' => $id,
						'location' => $user->location,
					]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request, $campana_id)
	{
		$content = new Content();

		//$content->beginTransaction();

		$user = User::where( 'id', '=', Auth::user()->id )->first();

		$location = $user->location;


		// var_dump($location);

		// Nuevo cliente con un url base
		$client = new Client();

		//Token Crud
		$crud = ContentController::crud();


		// formate el $timeframe al formato de la api: "1,2,3,..."
		if ( empty($request->timeframe_id) ) {

			$timeframes = null;
		} else {

			$count = count($request->timeframe_id);
			$timeframes = $request->timeframe_id[0];
			for ($i=1; $i < $count; $i++) {
				$timeframes .= ",".$request->timeframe_id[$i];
			}
		}

		// leo el id del tag para asignarlo al beacon
		//
		$tag_api = $client->get('https://connect.onyxbeacon.com/api/v2.5/tags/'.$location->name, [
			// un array con la data de los headers como tipo de peticion, etc.
			'headers' => ['Authorization' => 'Bearer '.$crud ],
		]);

		//Json parse
		$json_b = $tag_api->getBody();

		$tag_response = json_decode($json_b);

		// echo "<pre>";	var_dump($tag_response);	echo "</pre>";
		// return;
		$tag_id = strval($tag_response->tags[0]->id);


		$parameters = array(
					'headers' => ['Authorization' => 'Bearer '.$crud ],
					'form_params' => [
							'coupon' => intval($request->coupon_id),
							'timeframes' => $timeframes,
							'trigger_name' => 'ENTRY',
							'trigger_entity' => 'tag',
							'tag' => $tag_id
						]
					);

		//Location
		$content_api = $client->post('https://connect.onyxbeacon.com/api/v2.5/campaigns/'.$campana_id.'/contents', $parameters);

		//Json parse
		$json_c = $content_api->getBody();

		$content_response = json_decode($json_c);


		if( $content_response->status_code === 200 ):

			$user = User::where( 'id', '=', Auth::user()->id )->first();

			$coupon = Coupon::where([
				['coupon_id', '=', $request->coupon_id],
				['user_id', '=', $user->user_id]
			])->first();

			$coupon->coupon_translation;

			foreach ($content_response as $key => $value) {
				if ($key == "campaign-content") {
					$content_api = $value;
				}
			}

			// echo "<pre>";	var_dump($content_api);	echo "</pre>";
			// return;

			$cam_c = new Content();
			$cam_c->content_id = $content_api->id;
			$cam_c->user_id = $user->user_id;
			//	coupon_translation[0] posicion [0] es en español idioma por defecto

			$cam_c->coupon = $coupon->coupon_translation[0]->name;
			$cam_c->coupon_id = $coupon->coupon_id;


			//	$cam_c->tag = $request->tag_id;
			$cam_c->tag = $tag_id;
			$cam_c->campana_id = $campana_id;
			$cam_c->trigger_name = $content_api->trigger_name;
			$cam_c->save();

			$content = Content::find($cam_c->id);

			$content->timeframes()->sync($request->timeframe_id);

			$coupon = Coupon::where('coupon_id', '=', $request->coupon_id)->first();

			// echo "<pre>";	var_dump($content);	echo "</pre>";
			// return;

			$campana = Campana::where('campana_id', '=', $campana_id)->first();

			//Location
			$campana_api = $client->post('https://connect.onyxbeacon.com/api/v2.5/campaigns/'.$campana_id.'/update', [
					// un array con la data de los headers como tipo de peticion, etc.
					'headers' => ['Authorization' => 'Bearer '.$crud ],
					// array de datos del formulario
					'form_params' => [
							'name' => $request->name,
							'description' => $coupon->description,
							'start_time' => date('Y-m-d h:m:s', strtotime($campana->start_time)),
							'end_time' => date('Y-m-d h:m:s', strtotime($campana->end_time)),
					]
			]);

			//Json parse
			$json_campana = $campana_api->getBody();

			$campana_response = json_decode($json_campana);

			if ($campana_response->status_code == 200 )
			{
				
				$campana->description = $coupon->coupon_translation[0]->description;
				$campana->update();
				$campana_process = true;
			}
			else
			{
				$campana_process = false;
				// echo "<pre>"; print_r( $campana_response ); echo "</pre>";
				// return;
			}

			//Carga el coupon en el beacon
			$url = 'http://dementecreativo.com/prueba/final/movil/campanas/'.$campana_id;
			$coupon_api = $client->post('https://connect.onyxbeacon.com/api/v2.5/coupons/'.$request->coupon_id.'/update', [
					// un array con la data de los headers como tipo de peticion, etc.
					'headers' => ['Authorization' => 'Bearer '.$crud ],
					// array de datos del formulario
					'form_params' => [
							'name' => $request->name,
							'description' => $coupon->description,
							'message' => $request->name,
							'type' => 'url',
							'url' =>  $url,
					]
			]);

			//Json parse
			$json_c = $coupon_api->getBody();

			$coupon_response = json_decode($json_c);

			if ($coupon_response->status_code == 200 )
			{

					$coupon = Coupon::where('coupon_id', '=', $request->coupon_id)->first();
					$coupon->url = $coupon_response->coupon->url;
					$coupon->update();
					$coupon_process = true;
			}
			else
			{
				$coupon_process = false;
				// echo "<pre>"; print_r( $coupon_response ); echo "</pre>";
				// return;
			}

			if ( $campana_process && $coupon_process ) {

				$campana->img = $url;
				$campana->save();

				
				return redirect()->route('all_content', array('campana_id' => $campana_id ) )->with(['status' => 'Se ha creado el contenido exitosamente', 'type' => 'success']);
			} else {

				echo "<pre>campana_process: ";	var_dump($campana_process);	echo "</pre>";
				echo "<pre>coupon_process: ";	var_dump($coupon_process);	echo "</pre>";
				return;
				
				return redirect()->route('all_content', array('campana_id' => $campana_id ) )->with(['status' => 'Error al ingresar el contenido', 'type' => 'error']);
			}

		else:
			echo "<pre>";	var_dump($content_response);	echo "</pre>";
			return;			
			return redirect()->route('all_content', array('campana_id' => $campana_id ) )->with(['status' => 'Error al ingresar el contenido', 'type' => 'error']);

		endif;
	}



	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($campana_id,$content_id)
	{
		//consulta

		$user = User::where( 'id', '=', Auth::user()->id )->first();

		$coupons = Coupon::where([
			['user_id', '=', $user->user_id],
		])->get();

		$tags = Tag::where('user_id', '=', $user->user_id)->get();

		$timeframes = Timeframe::where('user_id', '=', $user->user_id)->get();

		$content = Content::where([
								['user_id', '=', $user->user_id],
								['content_id', '=', $content_id]
							])->first();

		$content_timeframes = DB::table('timeframes')
			->join('content_timeframes', 'timeframes.timeframe_id', '=', 'content_timeframes.timeframe_id')
			->join('contents', 'contents.id', '=', 'content_timeframes.content_id')
			->select('timeframes.*')
			->where('content_timeframes.content_id', '=', $content->id)
			->get();

		//	echo "<pre>";var_dump($content_timeframes);echo "</pre>";
		// return;

		return view('contents.content_edit', [
					'campana_id' => $campana_id,
					'content' => $content,
					'coupons' => $coupons,
					'timeframes' => $timeframes,
					'content_timeframes' => $content_timeframes,
					'location' => $user->location,
				]);
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $campana_id, $content_id)
	{
		// Nuevo cliente con un url base
		$client = new Client();

		//Token Crud
		$crud = ContentController::crud();

		$user = User::where( 'id', '=', Auth::user()->id )->first();

		$location = $user->location;

		// leo el id del tag para asignarlo al beacon
		//
		$tag_api = $client->get('https://connect.onyxbeacon.com/api/v2.5/tags/'.$location->name, [
			// un array con la data de los headers como tipo de peticion, etc.
			'headers' => ['Authorization' => 'Bearer '.$crud ],
		]);

		//Json parse
		$json_b = $tag_api->getBody();

		$tag_response = json_decode($json_b);

		$tag_id = strval($tag_response->tags[0]->id);


		// formate el $timeframe al formato de la api: "1,2,3,..."
		if ( empty($request->timeframe_id) ) {
			$timeframes = null;
		} else {
			$count = count($request->timeframe_id);
			$timeframes = $request->timeframe_id[0];
			for ($i=1; $i < $count; $i++) {
				$timeframes .= ",".$request->timeframe_id[$i];
			}
		}

		$parameters = array(
					'headers' => ['Authorization' => 'Bearer '.$crud ],
					'form_params' => [
							'coupon' => $request->coupon_id,
							'timeframes' => $timeframes,
							'trigger_name' => 'ENTRY',
							'trigger_entity' => 'tag',
							'tag' => $tag_id
						]
					 );

		//Location
		$campana_content = $client->post('https://connect.onyxbeacon.com/api/v2.5/campaigns/'.$campana_id.'/contents/'.$content_id.'/update', $parameters);

		//Json parse
		$json_c = $campana_content->getBody();

		$campana_c = json_decode($json_c);

		foreach ($campana_c as $key => $value) {
			if ($key == "campaign-content") {
				$content_api = $value;
			}
		}

		 // echo "<pre>";  var_dump($content_api); echo "</pre>";

		 // return;

		if( $campana_c->status_code === 200 ):

			$user = User::where( 'id', '=', Auth::user()->id )->first();

			$coupon = Coupon::where([
								['coupon_id', '=', $request->coupon_id],
								['user_id', '=', $user->user_id]
							])->first();

			$coupon->coupon_translation;

			$cam_c = Content::where([
								['content_id', '=', $content_id ]
							])->first();


			$cam_c->content_id = $content_api->id;
			$cam_c->user_id = $user->user_id;
			//coupon_translation[0] posicion [0] es en español idioma por defecto
			$cam_c->coupon = $coupon->coupon_translation[0]->name;
			$cam_c->coupon_id = $coupon->coupon_id;
		//    $cam_c->tag = $request->tag_id;
			$cam_c->tag = $tag_id;
			$cam_c->campana_id = $campana_id;
			$cam_c->trigger_name = $content_api->trigger_name;
			$cam_c->save();

			$content = Content::find($cam_c->id);

			$content->timeframes()->sync($request->timeframe_id);

			return redirect()->route('all_content', array('campana_id' => $campana_id ) );

		else:

			 return redirect()->route('edit_content', $campana_id)->with(['status' => 'Error al ingresar la Campana', 'type' => 'error']);

		endif;
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy( $campana_id, $content_id )
	{
		// // Nuevo cliente con un url base
		// $client = new Client();

		// //Token Crud
		// $crud = ContentController::crud();

		// $content_ = $client->post('https://connect.onyxbeacon.com/api/v2.5/campaigns/'.$campana_id.'/content/'.$content_id.'/delete', [
		// 		// un array con la data de los headers como tipo de peticion, etc.
		// 		'headers' => ['Authorization' => 'Bearer '.$crud ]
		// ]);

		// //Json parse
		// $json_c = $content_->getBody();

		// $content = json_decode($json_c);

		// if ($content->status_code === 200 ):

			$user = User::where( 'id', '=', Auth::user()->id )->first();

			$content =  Content::where([
									['user_id', '=', $user->user_id],
									['content_id', '=', $content_id]
								])->first();

			$content_timeframes = DB::table('timeframes')
				->join('content_timeframes', 'timeframes.timeframe_id', '=', 'content_timeframes.timeframe_id')
				->join('contents', 'contents.id', '=', 'content_timeframes.content_id')
				->select('timeframes.*')
				->where('content_timeframes.content_id', '=', $content->id)
				->get();

			foreach ($content_timeframes as $key => $timeframe) {
				$content->timeframes()->detach($timeframe->timeframe_id);
			}

			// var_dump($content);
			// return;

			$content->delete();

			return redirect()->route('all_content', array('campana_id' => $campana_id ) )
					->with(['status' => 'Se ha Eliminado el contenido con éxito', 'type' => 'success']);

		// else:

		// 	//echo "<pre>"; var_dump($campaña); echo "</pre>";

		// 	return redirect()->route('all_content')->with(['status' => 'Error al eliminar el contenido', 'type' => 'error']);

		// endif;
	}

}

<?php

namespace Beacon\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Beacon\Campana;
use Beacon\Content;
use Beacon\Coupon;
use Beacon\Tag;
use Beacon\Timeframe;
use Illuminate\Support\Facades\Input;
use Log;

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

		Log::info('This is some useful information.');

		return $token_crud->access_token;
	}


	/**
	 * @return token analytics
	 */
	public function analytics()
	{
		// Nuevo cliente con un url base
		$client = new Client();

		//Token analytics
		$response_analytics = $client->request('POST', 'https://connect.onyxbeacon.com/oauth/client', [
				'form_params' => [
						'client_id' => '89b88a5f9eaec9ab9b059a56c51e37413be4e043',
						'client_secret' => '7e58c94dafd3751f90b0e4b4de871be7e8b7ae44',
						'scope' => 'analytics'
				]
		]);

		$json_a = $response_analytics->getBody();

		$token_analytics = json_decode($json_a);

		return $token_analytics->access_token;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index($id)
	{
		$content = new Content;

		$contents = $content->where([
			['user_id', '=', Auth::user()->id],
			['campana_id', '=', $id],
		])->get();

		foreach ($contents as $key => $content) {
			$content->timeframe;
		}

		$coupon = new Coupon;

		$coupons = $coupon->where([
			['user_id', '=', Auth::user()->id],
		])->get();

		foreach ($coupons as $key => $coupon) {
			$coupon->coupon_translation;
		}
		//$coupon->coupon_translation;
		//echo "<pre>";var_dump($coupon);echo "</pre>";

		$tags = Tag::where('user_id', '=', Auth::user()->id)->get();

		$timeframes = Timeframe::where('user_id', '=', Auth::user()->id)->get();

		return view('contents.content',
					[
						'coupons' => $coupons,
						'contents' => $contents,
						'tags' => $tags,
						'timeframes' => $timeframes,
						'campana_id' => $id
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

        // Nuevo cliente con un url base
        $client = new Client();

        //Token Crud
        $crud = ContentController::crud();

        $parameters = array(
                    'headers' => ['Authorization' => 'Bearer '.$crud ],
                    'form_params' => [
                            'coupon' => intval($request->coupon_id),
                            'timeframes' => $request->timeframe_id,
                            'trigger_name' => 'DWELL_TIME',
                            'trigger_entity' => 'tag',
                            'dwell_time' => intval($request->dwell_time)
                        ]
                     );

        //Location
        $campana_content = $client->post('https://connect.onyxbeacon.com/api/v2.5/campaigns/'.$campana_id.'/contents', $parameters);

        //Json parse
        $json_c = $campana_content->getBody();

        $campana_c = json_decode($json_c);


        if( $campana_c->status_code === 200 ):

            $coupons = Coupon::where([
                ['coupon_id', '=', $request->coupon_id],
                ['user_id', '=', Auth::user()->id]
            ])->get();

            foreach ($coupons as $key => $coupon) {
                $coupon->coupon_translation;
            }
						foreach ($campana_c as $key => $value) {
							if ($key == "campaign-content") {
								$content_api = $value;
							}
						}

						// echo "<pre>";	var_dump($content_api);	echo "</pre>";
						// return;

            $cam_c = new Content();
            $cam_c->content_id = $content_api->id;
            $cam_c->user_id = Auth::user()->id;
            //coupon_translation[0] posicion [0] es en español idioma por defecto

                $cam_c->coupon = $coupons[0]->coupon_translation[0]->name;
                $cam_c->coupon_id = $coupons[0]->coupon_id;


        //    $cam_c->tag = $request->tag_id;
            $cam_c->tag = 1;
            $cam_c->campana_id = $campana_id;
            $cam_c->timeframe_id = $content_api->timeframes[0]->id;
            $cam_c->trigger_name = $content_api->trigger_name;
            $cam_c->dwell_time = $content_api->dwell_time;
            $cam_c->save();



            //Carga el coupon en el beacon
            $coupon_ = $client->post('https://connect.onyxbeacon.com/api/v2.5/coupons/'.$request->coupon_id.'/update', [
                    // un array con la data de los headers como tipo de peticion, etc.
                    'headers' => ['Authorization' => 'Bearer '.$crud ],
                    // array de datos del formulario
                    'form_params' => [
                            'name' => $request->name,
                            'description' => $request->description,
                            'message' => 'newMessage',
                            'type' => 'url',
                            'url' =>  'http://dementecreativo.com/prueba/final/movil/campanas/'.$campana_id,
                    ]
            ]);

            //Json parse
            $json_c = $coupon_->getBody();

            $coupon = json_decode($json_c);

            if ($coupon->status_code != 200 )
            {
                echo "<pre>"; print_r( $coupon ); echo "</pre>";
                return;
            }

            return redirect()->route('all_content', array('campana_id' => $campana_id ) )->with(['status' => 'Se ha creado el contenido exitosamente', 'type' => 'success']);



			else:

            return redirect()->route('show_content', $campana_id)->with(['status' => 'Error al ingresar la Campana', 'type' => 'error']);

        endif;
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function storejose(Request $request, $campana_id)
	{
		// Nuevo cliente con un url base
        $client = new Client();

        //Token Crud
        $crud = ContentController::crud();

        $parameters = array(
                    'headers' => ['Authorization' => 'Bearer '.$crud ],
                    'form_params' => [
                            'coupon' => intval($request->coupon_id),
                            'timeframes' => $request->timeframe_id,
                            'trigger_name' => 'DWELL_TIME',
                            'trigger_entity' => 'tag',
                            'dwell_time' => intval($request->dwell_time)
                        ]
                     );

        //Location
        $campana_content = $client->post('https://connect.onyxbeacon.com/api/v2.5/campaigns/'.$campana_id.'/contents', $parameters);

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

            $coupons = Coupon::where([
                ['coupon_id', '=', $request->coupon_id],
                ['user_id', '=', Auth::user()->id]
            ])->get();

            foreach ($coupons as $key => $coupon) {
                $coupon->coupon_translation;
            }
            $cam_c = new Content();
        	$cam_c->content_id = $content_api->id;
            $cam_c->user_id = Auth::user()->id;
            //coupon_translation[0] posicion [0] es en español idioma por defecto
            $cam_c->coupon = $coupons[0]->coupon_translation[0]->name;
            $cam_c->coupon_id = $coupons[0]->coupon_id;
        //    $cam_c->tag = $request->tag_id;
            $cam_c->tag = 1;
            $cam_c->campana_id = $campana_id;
            $cam_c->timeframe_id = $content_api->timeframes[0]->id;
            $cam_c->trigger_name = $content_api->trigger_name;
            $cam_c->dwell_time = $content_api->dwell_time;
            $cam_c->save();

            return redirect()->route('all_content', array('campana_id' => $campana_id ) );

        else:

             return redirect()->route('show_content', $campana_id)->with(['status' => 'Error al ingresar la Campana', 'type' => 'error']);

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

		$coupon = new Coupon;

		$coupons = $coupon->where([
			['user_id', '=', Auth::user()->id],
		])->get();

		$tags = Tag::where('user_id', '=', Auth::user()->id)->get();

		$timeframes = Timeframe::where('user_id', '=', Auth::user()->id)->get();

		$content = Content::where([
								['user_id', '=', Auth::user()->id],
								['content_id', '=', $content_id]
							])->first();

		// echo "<pre>";var_dump($content->coupons);echo "</pre>";
		// return;

		return view('contents.content_edit', ['campana_id' => $campana_id, 'content' => $content, 'coupons' => $coupons, 'timeframes' => $timeframes]);
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

        $parameters = array(
                    'headers' => ['Authorization' => 'Bearer '.$crud ],
                    'form_params' => [
                            'coupon' => $request->coupon_id,
                            'timeframes' => $request->timeframe_id,
                            'trigger_name' => 'DWELL_TIME',
                            'trigger_entity' => 'tag',
                            'dwell_time' => $request->dwell_time
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

            $coupons = Coupon::where([
                ['coupon_id', '=', $request->coupon_id],
                ['user_id', '=', Auth::user()->id]
            ])->get();

            foreach ($coupons as $key => $coupon) {
                $coupon->coupon_translation;
            }
            $cam_c = Content::where([
			            		['content_id', '=', $content_id ]
			            	])->first();
        	$cam_c->content_id = $content_api->id;
            $cam_c->user_id = Auth::user()->id;
            //coupon_translation[0] posicion [0] es en español idioma por defecto
            $cam_c->coupon = $coupons[0]->coupon_translation[0]->name;
            $cam_c->coupon_id = $coupons[0]->coupon_id;
        //    $cam_c->tag = $request->tag_id;
            $cam_c->tag = 1;
            $cam_c->campana_id = $campana_id;
            $cam_c->timeframe_id = $content_api->timeframes[0]->id;
            $cam_c->trigger_name = $content_api->trigger_name;
            $cam_c->dwell_time = $content_api->dwell_time;
            $cam_c->save();

            return redirect()->route('all_content', array('campana_id' => $campana_id ) );

        else:

             return redirect()->route('show_content', $campana_id)->with(['status' => 'Error al ingresar la Campana', 'type' => 'error']);

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

			$content =  Content::where([
									['user_id', '=', Auth::user()->id],
									['content_id', '=', $content_id]
								])->first();

			$content->delete();

			return redirect()->route('all_content', array('campana_id' => $campana_id ) )
					->with(['status' => 'Se ha Eliminado el contenido con éxito', 'type' => 'success']);

		// else:

		// 	//echo "<pre>"; var_dump($campaña); echo "</pre>";

		// 	return redirect()->route('all_campana')->with(['status' => 'Error al eliminar el contenido', 'type' => 'error']);

		// endif;
	}

}

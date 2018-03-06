<?php

namespace App\Http\Controllers;

use \DB;
use \Auth;
use \Schema;
use Carbon\Carbon;
use \Validator AS Validator;	
use \Illuminate\Http\Request;

class CrudController extends Controller
{	

	public $model;

	public $primary_key = 'id';

  	public $table = '';

	public $rules = [];

	public $errors = [];

  	public $messages = [

   	 'name' => 'required'

  	];

  	public $table_columns;

	public function __construct () {
		// set App\Model to $this->model
	    $this->model = 'App\\' . $this->model;
	    $this->model = new $this->model();

	    // Get the column listing for a given table
      	$this->table_columns = Schema::getColumnListing( $this->table );

	}

	/**
	 * Personalized query
	 * @param  mixed $model 
	 * @return $model 
	 */
	public function preList($model){
    	$model->whereNull('deleted_at');
        return $model;
    }

    /**
     * Get all the data
     * @return object 
     */
	public function get() {
		
		$model = $this->model;
		$model = $this->preList($model);
    	return $model->get();
	}

	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
    	$this->request = $request;
   		$data = $this->get();

	    return response()->json($data)->withHeaders([
	      'Access-Control-Allow-Origin' => '*', 
	      'Access-Control-Allow-Headers' => 'X-Requested-With, Content-Type, Accept, Origin, Authorization',
	      'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS'
    	]);
  }

  /**
   * Display the specified resource.
   * 
   * @param  int $id
   * @return \Illuminate\Http\Response
   */
  public function show($id) 
  {
  	$data = [];
    $data['data'] = $this->model->where($this->primary_key, $id)->whereNull('deleted_at')->first();
  	
  	if ($data['data'] == null) {
  		return response()->json([
	        'message' => 'Record not found',
	    ], 404);
  	}

  	return response()->json($data)->withHeaders([
      'Access-Control-Allow-Origin' => '*', 
      'Access-Control-Allow-Headers' => 'X-Requested-With, Content-Type, Accept, Origin, Authorization',
      'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS'
	]);

  }

  /**
   * Additional query for store
   * @param  array $data 
   * @return $data
   */
  public function create($data = null)  {
    return $data;
  }

  public function store(Request $request) 
  {	

  	$this->validate($request, $this->rules);
  	$errors = [];
  	if ( !$errors ) {
  		$data = [];	

  		$data = $this->create($data);
  		// add the authenticated user to created_by field
	  	$request->request->add(['created_by' => Auth::user()->id]);	
	  	// dd($request->all());exit;
  		$data['data'] = $this->model->create($request->all());
  		$data['message'] = 'Record is successfully added.';
   	}

    $data['errors'] = $errors ? $errors : [];

  	return $errors ? $errors : response()->json($data)->withHeaders([
      'Access-Control-Allow-Origin' => '*', 
      'Access-Control-Allow-Headers' => 'X-Requested-With, Content-Type, Accept, Origin, Authorization',
      'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS'
	]);
  }

  /**
   * Additional query for update
   *
   * @param  int  $id
   * @param  array  $data
   * @return \Illuminate\Http\Response
   */
  public function edit($id, $data = []) {
    return $data;
  }

  public function update(Request $request, $id) {

    $errors = $this->validate($request, $this->rules);

  	$errors = [];
  	
  	if ( !$errors ) {
  		$data = [];	

  		$data['data'] = $request->all();

  		$data = $this->edit($id, $data);
  		
  		$update_data = $this->model->where($this->primary_key, $id);

  		$update_results = [];

  		foreach($this->table_columns AS $column_table){
          foreach($data['data'] AS $column_index => $value){
            if($column_index == $column_table){
              if(!empty($value)){
                $update_results[$column_table] = $value;
              }
            }
          }
        }

        $update_results['updated_by'] = Auth::user()->id;

        $update_results['updated_at'] = DB::raw('NOW()');

        $update_data->update($update_results);

        $data['data'] = $this->model->where($this->primary_key, $id)->first();

  		$data['message'] = 'Record is successfully updated.';
   	}

    $data['errors'] = $errors ? $errors : [];

  	return $errors ? $data['errors'] : response()->json($data)->withHeaders([
      'Access-Control-Allow-Origin' => '*', 
      'Access-Control-Allow-Headers' => 'X-Requested-With, Content-Type, Accept, Origin, Authorization',
      'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS'
	]);
  }
  
  /**
   * Additional query for destroy
   * @param  int $id   
   * @param  array  $data 
   * @return $data      
   */
  public function delete($id, $data = []) {
    return $data;
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\Test  $test
   * @return \Illuminate\Http\Response
   */
    public function destroy($id)
    {	
   		$data = [];

   		$this->delete($id);

   		$this->model->where($this->primary_key, $id)->update([
   			'deleted_at' => Carbon::now(), 
   			'deleted_by' => Auth::user()->id
   		]);

   		$data['data'] = $this->model->where($this->primary_key, $id)->first();

   		$data['message'] = 'You have successfully deleted the record';

   		return response()->json($data)->withHeaders([
	      'Access-Control-Allow-Origin' => '*', 
	      'Access-Control-Allow-Headers' => 'X-Requested-With, Content-Type, Accept, Origin, Authorization',
	      'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS'
	    ]);

    }

}

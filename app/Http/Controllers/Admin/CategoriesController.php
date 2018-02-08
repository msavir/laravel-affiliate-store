<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use App\Base\Controllers\AdminController;
use App\Http\Requests\Admin\CategoryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Facades\Image;
use Yajra\DataTables\DataTables;

class CategoriesController extends AdminController
{
      /**
       * Create a new controller instance.
       *
       */
      public function __construct()
      {
        $this->middleware('auth');
      }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $categories = Category::all();

      return view('admin.categories.index',compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.categories.create');
    }

  /**
   * Store a newly created resource in storage.
   *
   * @param \App\Http\Controllers\Admin\CategoryRequest|CategoryRequest $request
   * @return \Illuminate\Http\Response
   */
    public function store(CategoryRequest $request)
    {
      $data = [
        'name' => $request->name,
      ];

      $category = Category::create($data);
      $public_dir = public_path('uploads/categories/'.$category->id.'/');

      if (!file_exists($public_dir)) {
        mkdir($public_dir, 0777, true);
      }

      if($request->file('image')){

        $img = Image::make($request->file('image'))->fit(220, 180);
        $data['image'] = time().$img->basename.'.jpg';
        $img->save($public_dir.$data['image']);

      }

      $category->update($data);

      return Redirect::route('admin.categories.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int $id
   * @param Request $request
   * @return \Illuminate\Http\Response
   */
    public function edit($id, Request $request)
    {
        $category = Category::findOrFail($id);
        $products = $category->products()->get();

      if ($request->ajax()){
        return Datatables::of($products)
          ->editColumn('link', function ($product){
            return '<a href="'.$product->link.'">Product Link</a>';
          })
          ->addColumn('action', function ($product){
            return '<a class="btn btn-sm btn-warning" href="'.route('admin.products.edit',$product->id).'">Edit</a>';
          })
          ->rawColumns(['link','action'])
          ->make(true);
      }
        return view('admin.categories.edit', compact('category','products'));
    }

  /**
   * Update the specified resource in storage.
   *
   * @param CategoryRequest|Request $request
   * @param  int $id
   * @return \Illuminate\Http\Response
   */
    public function update(CategoryRequest $request, $id)
    {

      $data = [
        'name' => $request->name,
      ];
      $category = Category::findOrFail($id);
      $public_dir = public_path('uploads/categories/'.$id.'/');
      if (!file_exists($public_dir)) {
        mkdir($public_dir, 0777, true);
      }

      if($request->file('image')){
        $img = Image::make($request->file('image'))->fit(220, 180);
        $data['image'] = time().$img->basename.'.jpg';
        $img->save($public_dir.$data['image']);
      }

      $category->update($data);

      return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        $category->delete();

        return Redirect::route('admin.categories.index');
    }
}

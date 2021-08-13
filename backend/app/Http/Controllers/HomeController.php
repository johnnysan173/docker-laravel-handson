<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Memo;
use App\Models\App;
use App\Models\Tag;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {   
        $user = \Auth::user();
        $memos = Memo::where('user_id', $user['id'])->where('status', 1)->orderBy('updated_at', 'DESC')->get();
        // dd($memos);
        return view('home', compact('user', 'memos'));
    }

    public function create()
    {
        $user = \Auth::user();
        // dd($user);
        $memos = Memo::where('user_id', $user['id'])->where('status', 1)->orderBy('updated_at', 'DESC')->get();
        return view('create', compact('user', 'memos'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        // dd($data);
        // POSTされたデータをDB（memosテーブル）に挿入
        // MEMOモデルにDBへ保存する命令を出す
        $exist_tag = Tag::where('name', $data['tag'])->where('user_id', $data['user_id'])->first();
        // dd($exist_tag);
        if($exist_tag != null){
            $tag_id = $exist_tag['id'];
        } else {
            $tag_id = Tag::insertGetId(['name' => $data['tag'], 'user_id' => $data['user_id']]);
        }
        // dd($tag_id);
        //タグのIDが判明する
        // タグIDをmemosテーブルに入れてあげる
        $memo_id = Memo::insertGetId([
            'content' => $data['content'],
             'user_id' => $data['user_id'], 
             'tag_id' => $tag_id,
             'status' => 1
        ]);
        
        // リダイレクト処理
        return redirect()->route('home');
    }

    public function edit($id){
        // 該当するIDのメモをデータベースから取得
        $user = \Auth::user();
        $memos = Memo::where('user_id', $user['id'])->where('status', 1)->orderBy('updated_at', 'DESC')->get();
        $memo = Memo::where('status', 1)->where('id', $id)->where('user_id', $user['id'])
          ->first();
        $tags = tag::where('user_id', $user['id'])->get();
        // dd($tags);
        //取得したメモをViewに渡す
        return view('edit',compact('user', 'memo', 'memos', 'tags'));
    }

    public function update(Request $request, $id){
        $input = $request->all();
        // dd($input);
        
        Memo::where('id', $id)->update(['content' => $input['content'], 'tag_id' => $input['tag_id']]);
        return redirect()->route('home');
    }

    public function delete(Request $request, $id){
        $input = $request->all();
        // dd($input);
        
        Memo::where('id', $id)->update(['status' => 2]);
        return redirect()->route('home')->with('success', 'メモの削除完了');
    }


}

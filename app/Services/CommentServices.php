<?php

namespace App\Services;

use App\Constant;
use App\Models\Comment;
use App\Services\BaseServices;
use App\Services\Goods\GoodsServices;
use App\Services\User\UserServices;
use Illuminate\Support\Arr;

const DEF_ID = 1;

// 6-9
class CommentServices extends BaseServices
{
    public function getCommentByGoodsId($goodsId, $page = 1, $limit = 2) {
        return Comment::query()
            ->where('value_id', $goodsId)
            ->where('type', Constant::COMMENT_TYPE_GOODS)
            ->where('deleted', 0)
            ->paginate($limit, ['*'], 'page', $page);
             // ->get()
            ;
    }
    public function getCommentWithUserInfo($goodsId, $page = 1, $limit = 2) {
        $comments = $this->getCommentByGoodsId($goodsId, $page, $limit);
        $userIds = Arr::pluck($comments->items(), 'user_id');
        // $userIds = array_unique($userIds);// 去重
        // dd($comments);// 
        // dd($userIds);// 
        $users = UserServices::getInstance()->getUsers($userIds)->keyBy('id');// 转成 key  value 形式 
        // tems返回是个数组 使用collect包裹一下转化为结合
        $data = collect($comments->items())->map(function (Comment $comment) use ($users) {
            $user = $users->get($comment->user_id);
            
            return [ 
                'id' => $comment->id,
                'addTime' => $comment->add_time,
                'content' => $comment->content,
                'adminContent' => $comment->admin_content,
                'picList' => $comment->pic_list,
                'nickname' => $user->nickname,
                'avatar' => $user->avatar,
            ];  
        });


        return [ 
            'count' => $comments->total(),
            'data' => $data,
        ];  

    }
}

<?php

namespace App\Http\Controllers\API\V1\Users\Follow;
use App\Http\Controllers\API\V1\BaseCRUDController;
use Illuminate\Http\Request;
use App\Models\Follow;
class FollowController extends BaseCRUDController
{
    protected function setModel(){
        $this->model = Follow::class;
    }

    protected function rules($id = null)
    {
        return [
            'follower_id' => 'required',
            'following_id' => 'required',
        ];
    }
    public function handleToggleFollow($followerId, $followingId)
    {
    $follow = \App\Models\Follow::firstOrCreate(
        [
            'follower_id' => $followerId,
            'following_id' => $followingId
        ],
        [
            'status' => 'inactive'
        ]
    );
    $newStatus = ($follow->status === 'active') ? 'inactive' : 'active';
    \App\Models\Follow::where('follower_id', $followerId)
        ->where('following_id', $followingId)
        ->update(['status' => $newStatus]);

    return $newStatus;
    }

    //Xử lý
    public function toggleFollow(Request $request)
    {
        $request->validate([
            'follower_id' => 'required',
            'following_id' => 'required',
        ]);

        $followerId = $request->follower_id;
        $followingId = $request->following_id;

        if ($followerId == $followingId) {
            return $this->sendError('Bạn không thể tự theo dõi chính mình.');
        }

        // Gọi hàm logic đã viết ở trên
        $status = $this->handleToggleFollow($followerId, $followingId);

        return $this->sendResponse(
            ['status' => $status], 
            $status === 'active' ? 'Đã theo dõi' : 'Đã hủy theo dõi'
        );
    }

}

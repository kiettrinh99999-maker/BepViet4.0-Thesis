<?php

namespace App\Http\Controllers\API\V1\Users\Profile;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Collection;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Lấy thông tin người dùng đang đăng nhập
     * GET: /api/profile
     */
    public function index(Request $request)
    {
        // Lấy user (Ưu tiên token, nếu không có thì lấy User ID 2 để test như bạn muốn)
        $user = $request->user() ?? User::find(2); 

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ], 401);
        }

        // --- LẤY SỐ LIỆU THỐNG KÊ THẬT TỪ DB ---
        
        // 1. Đếm số công thức của user
        $recipesCount = Recipe::where('user_id', $user->id)
        ->where('status', 'active')
        ->count();

        // 2. Đếm số bài blog của user
        $blogsCount = Blog::where('user_id', $user->id)
        ->where('status', 'active')->count();

        // 3. Đếm người theo dõi (Followers)
        // Bảng follows: follower_id (người đi theo dõi) -> following_id (người được theo dõi)
        // Mình là người ĐƯỢC theo dõi => đếm cột following_id = id của mình
        $followersCount = DB::table('follows')
            ->where('following_id', $user->id)
            ->where('status', 'active')
            ->count();

        // 4. Đếm đang theo dõi (Following)
        // Mình là người đi theo dõi => đếm cột follower_id = id của mình
        $followingCount = DB::table('follows')
            ->where('follower_id', $user->id)
            ->where('status', 'active')
            ->count();

        $stats = [
            'recipes_count'   => $recipesCount,
            'blogs_count'     => $blogsCount,
            'followers_count' => $followersCount,
            'following_count' => $followingCount
        ];

        // --- LẤY DANH SÁCH CÔNG THỨC THẬT ---
        $recipesList = Recipe::where('user_id', $user->id)
            ->with('difficulty')
            ->with('recipe_category')
            ->withCount('rates')
            ->withAvg('rates', 'score')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc') // Mới nhất lên đầu
            ->get(); // Lấy hết các trường

        // lấy danh sách blog
        $blogsList = Blog::where('user_id', $user->id)
            ->with('blog_category') 
           ->with('user.profile')
            ->where('status', 'active') // Chỉ lấy bài đang active
            ->orderBy('created_at', 'desc')
            ->get();
        
        $collectionsList = Collection::where('user_id', $user->id)
            ->withCount(['recipes' => function ($query) {
                // Thay vì wherePivot, hãy dùng where + tên bảng trung gian
                $query->where('recipe_collections.status', 'active');
            }])
            ->where('status', 'active') 
            ->orderBy('created_at', 'desc')
            ->get();
        $profile = $user->profile;
            
        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin thành công',
            'data'    => [
                'id'     => $user->id,
                'name'   => $profile->name,
                'phone'  => $profile->phone,
                'avatar' => $profile->image_path, 
                'stats'  => $stats,     // Trả về số liệu thật
                'recipes'=> $recipesList, // Trả về danh sách thật
                'blogs'   => $blogsList,
                'collections' => $collectionsList,
            ]
        ]);
    }

    /**
     * Cập nhật hồ sơ (Giữ nguyên logic cũ của bạn)
     */
    // public function update(Request $request)
    // {
    //     $user = $request->user() ?? User::find(2); // Test với user 2

    //     if (!$user) {
    //         return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'name'   => 'required|string|max:255',
    //         'phone'  => 'nullable|string|max:20',
    //         'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Dữ liệu không hợp lệ',
    //             'errors'  => $validator->errors()
    //         ], 422);
    //     }

    //     if ($request->has('name')) $user->name = $request->name; 
      
        
    //     if ($request->has('phone')) $user->phone = $request->phone;

    //     if ($request->hasFile('avatar')) {
    //         if ($user->avatar) {
    //             $oldPath = str_replace('/storage/', '', $user->avatar);
    //             if (Storage::disk('public')->exists($oldPath)) {
    //                 Storage::disk('public')->delete($oldPath);
    //             }
    //         }

    //         $file = $request->file('avatar');
    //         $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
    //         $file->storeAs('uploads/avatars', $filename, 'public');
            
    //         $user->avatar = 'uploads/avatars/' . $filename;
    //     }

    //     $user->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Cập nhật hồ sơ thành công',
    //         'data'    => $user
    //     ]);
    // }
public function update(Request $request)
    {
       
            // 1. Lấy user (Ưu tiên từ token, fallback sang ID 2 để test)
            $user = $request->user() ?? User::find(2);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found or Unauthorized'], 401);
            }

            // 2. Validate dữ liệu
            $validator = Validator::make($request->all(), [
                'name'   => 'required|string|max:255',
                'phone'  => 'nullable|string|max:20',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Dữ liệu không hợp lệ', 
                    'errors' => $validator->errors()
                ], 422);
            }

            // 3. Xử lý Profile (Tạo mới nếu chưa có)
            $profile = $user->profile;
            if (!$profile) {
                // Sử dụng username hoặc email làm tên mặc định nếu chưa có tên
                $defaultName = $user->username ?? explode('@', $user->email)[0];
                $profile = $user->profile()->create([
                    'name' => $defaultName
                ]);
            }

            // 4. Cập nhật thông tin
            if ($request->has('name')) $profile->name = $request->name;
            if ($request->has('phone')) $profile->phone = $request->phone;

            // 5. Xử lý Upload Avatar
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                
                // Xóa ảnh cũ nếu tồn tại
                if ($profile->image_path) {
                    $oldPath = str_replace('/storage/', '', $profile->image_path);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                // Lưu ảnh mới
                $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('uploads/avatars', $filename, 'public');
                
                // Lưu đường dẫn (kiểm tra xem frontend cần có /storage/ hay không)
                $profile->image_path = 'uploads/avatars/' . $filename;
            }

            $profile->save();

            // 6. Trả về kết quả
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thành công',
                'data'    => [
                    'name'   => $profile->name,
                    'phone'  => $profile->phone,
                    'avatar' => $profile->image_path,
                ]
            ]);

    }
  
}


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
        $user = $request->user();
        $profile = $user->profile;
        
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
                $query->where('recipe_collections.status', 'active');
            }])
            ->where('status', 'active') 
            ->orderBy('created_at', 'desc')
            ->get();
            
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
    public function update(Request $request)
    {   
            $user = $request->user();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found or Unauthorized'], 401);
            }

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

           $profile = $user->profile()->firstOrCreate(
            ['user_id' => $user->id],
            ['name' => $user->name ?? 'User'] 
        );

            if ($request->has('name')) $profile->name = $request->name;
            if ($request->has('phone')) $profile->phone = $request->phone;

            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                
                if ($profile->image_path) {
                    $oldPath = str_replace('/storage/', '', $profile->image_path);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('uploads/avatars', $filename, 'public');
                
                $profile->image_path = 'uploads/avatars/' . $filename;
            }

            $profile->save();

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
    /**
     * Xóa công thức (soft delete)
     */
   public function destroyRecipe(Request $request, $id)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $recipe = Recipe::find($id);

        if (!$recipe) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy công thức'], 404);
        }
        if ($recipe->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa công thức này'], 403);
        }
        $recipe->update(['status' => 'inactive']); 

        return response()->json(['success' => true, 'message' => 'Xóa thành công']);
    }

    /**
     * Xóa Blog (Soft delete) - Có xác thực chính chủ
     * DELETE: /api/auth/profile/blogs/{id}
     */
    public function destroyBlog(Request $request, $id)
    {
        $user = $request->user();

        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy bài viết'], 404);
        }

        if ($blog->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa bài viết này'], 403);
        }

        $blog->update(['status' => 'inactive']); 

        return response()->json(['success' => true, 'message' => 'Xóa bài viết thành công']);
    }
  
}


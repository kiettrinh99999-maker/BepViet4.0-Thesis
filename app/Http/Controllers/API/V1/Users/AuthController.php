<?php

namespace App\Http\Controllers\API\V1\Users;

use App\Models\User;
use App\Models\Profile;
use App\Http\Controllers\API\V1\BaseCRUDController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends BaseCRUDController
{
    protected function setModel()
    {
        $this->model = User::class;
    }

    protected function rules($id = null)
    {
        $rules = [
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'region_id' => 'nullable|exists:regions,id',
        ];

        if (!$id) {
            $rules['password'] .= '|confirmed';
        }

        return $rules;
    }

    /**
     * Đăng ký tài khoản mới
     */
   public function register(Request $request){
    $validator = Validator::make($request->all(), $this->rules());
    
    if ($validator->fails()) {
        return $this->sendError('Lỗi xác thực', $validator->errors(), 422);
    }

    try {
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'member',
            'status' => 'active',
        ]);
        $profile = new Profile();
        $profile->user_id = $user->id;
        $profile->name = $request->name||" ";
        $profile->phone = $request->phone;
        $profile->status = 'active';
        $profile->image_path = 'null';
        
        if ($request->filled('region_id')) {
            $profile->region_id = $request->region_id;
        }
        
        $profile->save();
        $token = null;
        if (method_exists($user, 'createToken')) {
            $token = $user->createToken('auth_token')->plainTextToken;
        }
        $responseData = [
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'profile' => [
                    'name' => $profile->name,
                    'phone' => $profile->phone,
                    'image_path' => $profile->image_path,
                    'region_id' => $profile->region_id,
                ]
            ],
            'message' => 'Đăng ký thành công!'
        ];

        if ($token) {
            $responseData['access_token'] = $token;
            $responseData['token_type'] = 'Bearer';
        }

        return $this->sendResponse($responseData, 'Đăng ký thành công!', 201);

    } catch (\Exception $e) {
        return $this->sendError('Đăng ký thất bại: ' . $e->getMessage(), [], 500);
    }
    }
    /**
     * Đăng nhập
     */
    public function login(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'login' => 'required|string',
        'password' => 'required|string',
    ]);
    if ($validator->fails()) {
        return $this->sendError('Lỗi xác thực', $validator->errors(), 422);
    }
    $login = $request->input('login');
    $password = $request->input('password');
    // Tìm user bằng email hoặc username (tự động nhận diện)
    $user = User::where(function ($query) use ($login) {
        $query->where('email', $login)
              ->orWhere('username', $login);
    })->first();
    // Kiểm tra thông tin đăng nhập
    if (!$user || !Hash::check($password, $user->password)) {
        return $this->sendError('Thông tin đăng nhập không đúng', [], 401);
    }
    // Kiểm tra trạng thái tài khoản
    if ($user->status !== 'active') {
        return $this->sendError('Tài khoản chưa được kích hoạt', [], 403);
    }
    // Load profile
    $user->load('profile');
    // Tạo token
    $token = null;
    if (method_exists($user, 'createToken')) {
        $tokenResult = $user->createToken(
            name: 'auth_token',
            expiresAt: now()->addMinutes(config('sanctum.expiration', 10080))
        );
        $tokenResult->accessToken->update([
            'expires_at' => now()->addMinutes(config('sanctum.expiration', 10080))
        ]);
        $token = $tokenResult->plainTextToken;
    }
    // Cập nhật last login
    $user->update(['updated_at' => now()]);
    $responseData = [
        'user' => [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'profile' => $user->profile ? [
                'name' => $user->profile->name,
                'phone' => $user->profile->phone,
                'image_path' => $user->profile->image_path,
                'region_id' => $user->profile->region_id,
            ] : null
        ],
        'message' => 'Đăng nhập thành công!'
    ];
    if ($token) {
        $responseData['access_token'] = $token;
        $responseData['token_type'] = 'Bearer';
    }
    return $this->sendResponse($responseData, 'Đăng nhập thành công!');
    }
    /**
     * Đăng xuất
     */
    public function logout(Request $request)
    {
        if (method_exists($request->user(), 'currentAccessToken')) {
            $request->user()->currentAccessToken()->delete();
        }
        
        return $this->sendResponse([], 'Đăng xuất thành công!');
    }

    /**
     * Lấy thông tin user hiện tại
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return $this->sendError('Không tìm thấy thông tin người dùng', [], 401);
        }

        // Load profile
        $user->load('profile');

        $responseData = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'profile' => $user->profile ? [
                'name' => $user->profile->name,
                'phone' => $user->profile->phone,
                'image_path' => $user->profile->image_path,
                'region_id' => $user->profile->region_id,
            ] : null
        ];

        return $this->sendResponse($responseData, 'Lấy thông tin thành công');
    }

    /**
     * Cập nhật profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'region_id' => 'sometimes|nullable|exists:regions,id',
            'profile_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Lỗi xác thực', $validator->errors(), 422);
        }

        try {
            $profileData = [];
            
            // Cập nhật thông tin cơ bản
            if ($request->filled('name')) {
                $profileData['name'] = $request->name;
            }
            if ($request->filled('phone')) {
                $profileData['phone'] = $request->phone;
            }
            if ($request->filled('region_id')) {
                $profileData['region_id'] = $request->region_id;
            }

            // Xử lý upload ảnh
            if ($request->hasFile('profile_image')) {
                $image = $request->file('profile_image');
                $imageName = 'profile_' . $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('profiles', $imageName, 'public');
                
                $profileData['image_path'] = '/uploads/' . $imagePath;
            }

            // Cập nhật profile
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileData
            );

            // Load lại profile
            $user->load('profile');

            return $this->sendResponse([
                'profile' => [
                    'name' => $user->profile->name,
                    'phone' => $user->profile->phone,
                    'image_path' => $user->profile->image_path,
                    'region_id' => $user->profile->region_id,
                ]
            ], 'Cập nhật profile thành công!');

        } catch (\Exception $e) {
            return $this->sendError('Cập nhật thất bại: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Đổi mật khẩu
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Lỗi xác thực', $validator->errors(), 422);
        }

        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->sendError('Mật khẩu hiện tại không đúng', [], 401);
        }

        // Đổi mật khẩu
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return $this->sendResponse([], 'Đổi mật khẩu thành công!');
    }

/**
 * Đăng nhập admin với token expiration 8 tiếng
 */
    public function login_admin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Lỗi xác thực', $validator->errors(), 422);
        }

        $login = $request->input('login');
        $password = $request->input('password');
        $user = User::where(function ($query) use ($login) {
            $query->where('email', $login)
                ->orWhere('username', $login);
        })->first();
        if (!$user || !Hash::check($password, $user->password)) {
            return $this->sendError('Thông tin đăng nhập không đúng', [], 401);
        }
        if ($user->status !== 'active') {
            return $this->sendError('Tài khoản chưa được kích hoạt', [], 403);
        }
        if ($user->role !== 'admin') {
            return $this->sendError('Không có quyền truy cập admin', [], 403);
        }
        $user->load('profile');
        $token = null;
        if (method_exists($user, 'createToken')) {
            $expiresAt = now()->addHours(1);
            $token = $user->createToken(
                'admin_auth_token', 
                ['*'], 
                $expiresAt
            )->plainTextToken;
        }
        $user->update(['updated_at' => now()]);
        $responseData = [
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'profile' => $user->profile ? [
                    'name' => $user->profile->name,
                    'phone' => $user->profile->phone,
                    'image_path' => $user->profile->image_path,
                    'region_id' => $user->profile->region_id,
                ] : null
            ],
            'message' => 'Đăng nhập admin thành công!',
            'expires_in' => 8 * 60 * 60,
            'expires_at' => $expiresAt->toISOString()
        ];

        if ($token) {
            $responseData['access_token'] = $token;
            $responseData['token_type'] = 'Bearer';
        }

        return $this->sendResponse($responseData, 'Đăng nhập admin thành công!');
    }

/**
 * Middleware kiểm tra token admin
 */
public function checkAdminToken(Request $request)
{
    $user = $request->user();
    
    if (!$user) {
        return $this->sendError('Không tìm thấy thông tin người dùng', [], 401);
    }
    if ($user->role !== 'admin') {
        return $this->sendError('Không có quyền truy cập', [], 403);
    }
    $token = $user->currentAccessToken();
    if ($token->expires_at && $token->expires_at->isPast()) {
        $token->delete();
        return $this->sendError('Token admin đã hết hạn', [], 401);
    }
    $remainingTime = null;
    if ($token->expires_at) {
        $remainingSeconds = now()->diffInSeconds($token->expires_at, false);
        $remainingTime = $remainingSeconds > 0 ? $remainingSeconds : 0;
    }
    
    $user->load('profile');
    
    return $this->sendResponse([
        'user' => [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'profile' => $user->profile ? [
                'name' => $user->profile->name,
                'phone' => $user->profile->phone,
                'image_path' => $user->profile->image_path,
                'region_id' => $user->profile->region_id,
            ] : null
        ],
        'token_info' => [
            'expires_at' => $token->expires_at ? $token->expires_at->toISOString() : null,
            'remaining_seconds' => $remainingTime,
            'remaining_hours' => $remainingTime ? round($remainingTime / 3600, 2) : null
        ]
    ], 'Token admin hợp lệ');
}
}
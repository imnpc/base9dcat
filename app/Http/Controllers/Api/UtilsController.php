<?php

/**
 * 工具类接口
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Storage;

class UtilsController extends Controller
{
    /**
     * 统一文件图片上传接口
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $user = $request->user(); // 获取登录用户信息

        $image = file_ext(); // 上传文件类型

        $request->validate([
            'file' => 'required|mimes:'.$image, // 允许的上传文件类型
            'type' => 'required|in:0,1', // 类型 0-图片 1-视频文件
        ]);

        // 处理上传类型
        if ($request->type == 1) {
            $type = 'file';
        } else {
            $type = 'image';
        }

        $file = upload_images($request->file('file'), $user->id, $type);
        $data['file_path'] = $file->path;
        $data['file_url'] = Storage::disk(config('filesystems.default'))->url($file->path);

        $data['message'] = "文件上传成功";
        return response()->json($data, 200);
    }

    /**
     * 生成 JSSDK 签名
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function jsSdkSign(Request $request)
    {
        $app = app('easywechat.official_account');
        $utils = $app->getUtils();
        $data = $utils->buildJsSdkConfig(
            url: $request->url, // 提交的网址,需要在微信公众号授权 URL地址 中
        );

        return response()->json($data, 200);
    }

    public function weixinCode(Request $request)
    {
        $data = Socialite::driver('weixin')->getAccessTokenResponse($request->code);

        return response()->json($data, 200);
    }

}

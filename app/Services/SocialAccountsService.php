<?php

namespace App\Services;

use App\Models\User;
use App\Models\LinkedSocialAccount;
use Laravel\Socialite\Two\User as ProviderUser;
use Laravel\Socialite\One\User as ProviderUserOne;

class SocialAccountsService
{
    /**
     * OAuth 1.0  (twitter)
     * OAuth 1.0 协议 目前是 Twitter
     *
     * @param  ProviderUser  $providerUser
     * @param  string  $provider
     *
     * @return User
     */
    public function one(ProviderUserOne $providerUser, string $provider): User
    {
        $user = $this->findOrCreate($providerUser, $provider);
        return $user;
    }

    /**
     * OAuth 2.0 (google,facebook,weixin)
     * OAuth 2.0 协议，目前是 google facebook weixin 使用
     *
     * @param  ProviderUser  $providerUser
     * @param  string  $provider
     *
     * @return User
     */
    public function two(ProviderUser $providerUser, string $provider): User
    {
        $user = $this->findOrCreate($providerUser, $provider);
        return $user;
    }

    /**
     * Find or create user instance by provider user instance and provider name.
     * 通过提供者用户实例和提供者名称查找或创建用户实例
     *
     * @param $providerUser
     * @param  string  $provider
     *
     * @return User
     */
    public function findOrCreate($providerUser, string $provider): User
    {
        $linkedSocialAccount = LinkedSocialAccount::where('provider_name', $provider)
            ->where('provider_id', $providerUser->getId())
            ->first();

        if ($linkedSocialAccount) {
            $linkedSocialAccount->update(['token' => $providerUser->token]);
            return $linkedSocialAccount->user;
        } else {
            $user = null;
            $name = $providerUser->getName() ? $providerUser->getName() : $providerUser->getNickName();
            // 拼音化中文昵称
            if (isAllChinese($name)) {
                $name = app('pinyin')->sentence($name);
                $name = pinyin_sentence($name);
            }
            if (!$user) {
                // 自动分配生成昵称
                $nick_name = strtolower($name);
                $nick_name = preg_replace('# #', '', $nick_name);
                $check = User::where('name', '=', $nick_name)->first();
                if ($check) {
                    $nick_name = $nick_name.$user->id;
                }

                $user = User::create([
                    'name' => $nick_name,
                    'nickname' => $nick_name,
                    'avatar' => $providerUser->getAvatar(),
                    'password' => bcrypt('1234567890'),
                ]);
//                $user->update(['name' => $nick_name]);
            }
            // 第三方登录库
            $user->linkedSocialAccounts()->create([
                'provider_id' => $providerUser->getId(),
                'provider_name' => $provider,
                'token' => $providerUser->token,
            ]);

            // 更新用户最后登录时间 登录IP
            //            $attributes['last_login_at'] = now();
            //            $attributes['last_login_ip'] = request()->getClientIp();
            //            $user->update($attributes);

            return $user;
        }
    }
}

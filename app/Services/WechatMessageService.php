<?php

namespace App\Services;

use Illuminate\Http\Request;

class WechatMessageService
{
    /**
     * 发送微信模版通知
     * @param  string  $openid  用户 openid
     * @param  string  $template_id  消息通知模版 ID
     * @param  array  $data  消息数组
     * @param  string  $url  消息问链接
     * @return void
     */
    public function sendWechatMessage($openid, $template_id, $data, $url = '')
    {
        $format_data = [];
        $format_data['touser'] = $openid;
        $format_data['template_id'] = $template_id;
        $format_data['url'] = $url;
        $format_data['data']['first']['value'] = $data['first']['value'];
        $format_data['data']['first']['color'] = $data['first']['color'];
        $format_data['data']['keyword1']['value'] = $data['keyword1'];
        $format_data['data']['keyword2']['value'] = $data['keyword2'];
        if (isset($data['keyword3'])) {
            $format_data['data']['keyword3']['value'] = $data['keyword3'];
        }
        if (isset($data['keyword4'])) {
            $format_data['data']['keyword4']['value'] = $data['keyword4'];
        }
        if (isset($data['keyword5'])) {
            $format_data['data']['keyword5']['value'] = $data['keyword5'];
        }
        $format_data['data']['remark']['value'] = $data['remark'];
        //        $format_data = json_encode($format_data);
        $app = app('easywechat.official_account');
        $api = $app->getClient();
        $response = $api->postJson('/cgi-bin/message/template/send', $format_data);
        //        file_put_contents('./0.WechatMessage.txt', var_export($response->getContent(), true));
        //        file_put_contents('./0.$response.txt', var_export($response, true));
        $data['status'] = 0;
        $data['message'] = '发送失败';
        if ($response->isSuccessful()) {
            //            file_put_contents('./0.isSuccessful.txt', $response->isSuccessful());
            $data['status'] = 1;
            $data['message'] = '发送成功';
        } elseif ($response->isFailed()) {
            //            file_put_contents('./0.isFailed.txt', $response->isFailed());
            $data['status'] = 0;
        }

        return $data;
    }

    /**
     * 获取草稿列表
     * @param int $offset 从全部素材的该偏移位置开始返回，0表示从第一个素材返回
     * @param int $count 返回素材的数量，取值在1到20之间
     * @param int $no_content 1 表示不返回 content 字段，0 表示正常返回，默认为 0
     * @return mixed
     */
    public function draftBatchGet($offset = 0, $count = 20, $no_content = 1)
    {
        $app = app('easywechat.official_account');
        $api = $app->getClient();
        $response = $api->postJson('/cgi-bin/draft/batchget', [
            "offset" => $offset,
            "count" => $count,
            "no_content" => $no_content,
        ]);

        return $response;
    }

    /**
     * 获取草稿
     * @param string $media_id 要获取的草稿的 media_id
     * @return mixed
     */
    public function getDraft($media_id)
    {
        $app = app('easywechat.official_account');
        $api = $app->getClient();
        $response = $api->postJson('/cgi-bin/draft/get', [
            "media_id" => $media_id,
        ]);

        return $response;
    }

    /**
     * 获取成功发布列表
     * @param int $offset 从全部素材的该偏移位置开始返回，0表示从第一个素材返回
     * @param int $count 返回素材的数量，取值在1到20之间
     * @param int $no_content 1 表示不返回 content 字段，0 表示正常返回，默认为 0
     * @return mixed
     */
    public function freePublishBatchGet($offset = 0, $count = 20, $no_content = 0)
    {
        $app = app('easywechat.official_account');
        $api = $app->getClient();
        $response = $api->postJson('/cgi-bin/freepublish/batchget', [
            "offset" => $offset,
            "count" => $count,
            "no_content" => $no_content,
        ]);

        return $response;
    }

    /**
     * 通过 article_id 获取已发布文章
     * @param string $article_id 要获取的草稿的article_id
     * @return mixed
     */
    public function getArticle($article_id)
    {
        $app = app('easywechat.official_account');
        $api = $app->getClient();
        $response = $api->postJson('/cgi-bin/freepublish/getarticle', [
            "article_id" => $article_id,
        ]);

        return $response;
    }
}

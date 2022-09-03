<?php
/**
 * 辅助函数文件
 *
 */

use App\Gateways\QxtGateway;
use Overtrue\EasySms\EasySms;

/**
 * 上传图片
 * @param $file 文件
 * @param $type 类型 avatar,passport,cert,edu,course,banner,other
 * @param $user_id 用户 id
 * @param string $disk 磁盘名称
 * @return \App\Http\Resources\ImageResource
 */
function upload_images($file, $type, $user_id, $disk = "oss")
{
    $path = Storage::disk($disk)->putFile($type . '/' . date('Y/m/d'), $file);
    $image = new App\Models\Image();
    $image->type = $type; //上传类型 参见 ImageRequest
    $image->path = $path;// URL 路径
    $image->disk = $disk; //上传磁盘
    $image->size = $file->getSize();// 获取文件大小
    $image->size_kb = number_fixed($image->size / 1024, 2);// 获取文件大小 k
    $image->user_id = $user_id;
    $image->save();

    return new App\Http\Resources\ImageResource($image);
}

/**
 *  允许上传图像类型
 * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed|string
 */
function image_ext()
{
    if (config('upload.image_ext')) {
        $ext = config('upload.image_ext');
    } else {
        $ext = "gif,bmp,jpeg,png"; // 默认上传图像类型
    }

    return $ext;
}

/**
 * 隐藏银行卡号
 * @param $number
 * @param string $maskingCharacter
 * @return string
 */
function addMaskCC($number, $maskingCharacter = '*')
{
    return substr($number, 0, 4) . str_repeat($maskingCharacter, strlen($number) - 8) . substr($number, -4);
}

/**
 * 保留几位小数 默认 5
 * @param float $number 数字
 * @param int $precision 保留位数
 * @return float|int
 */
function number_fixed($num, $precision = 5)
{
    return intval($num * pow(10, $precision)) / pow(10, $precision);
}

/**
 * 获取数组内的 id
 * @param array $data
 * @param string $key
 * @return array
 */
function get_array_ids(array $data, string $key = 'id'): array
{
    $ids = [];
    foreach ($data as $item) {
        $id = $item[$key] ?? false;
        if ($id === false) {
            continue;
        }
        $ids[$id] = 0;
    }
    return array_keys($ids);
}


/**
 * 获取客户端IP(非用户服务器IP)
 * @return string
 */
function get_ip(): string
{
    $ip = 'members.3322.org/dyndns/getip';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $ip);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    return trim($data);
}

/**
 * 发送短信
 * @param string $mobile 手机号
 * @param int $code 验证码
 * @throws \Overtrue\EasySms\Exceptions\InvalidArgumentException
 * @throws \Overtrue\EasySms\Exceptions\NoGatewayAvailableException
 */
function send_sms($mobile, $code): array
{
    $sign = config('easysms.sms_sign_name');
    $easySms = new EasySms(config('easysms'));
    // 注册
    $easySms->extend('qxt', function ($gatewayConfig) {
        // $gatewayConfig 来自配置文件里的 `gateways.mygateway`
        return new QxtGateway($gatewayConfig);
    });
    $text = '【' . $sign . '】您的验证码是：' . $code . '。请不要把验证码泄露给其他人。';
    $result = $easySms->send($mobile, $text);

    return $result;
}

/**
 * @param $num         科学计数法字符串  如 2.1E-5
 * @param  int  $double  小数点保留位数 默认5位
 * @return string
 */
function sctonum($num, $double = 5)
{
    if (false !== stripos($num, "e")) {
        $a = explode("e", strtolower($num));
        return bcmul($a[0], bcpow(10, $a[1], $double), $double);
    }

    return $num;
}

/**
 * 生成唯一订单号
 * @param  string  $model  模型名称,首字母大写
 * @param  string  $field  订单号查询字段
 * @return bool|string
 */
function createNO($model, $field)
{
    // 订单流水号前缀
    $prefix = date('YmdHis');
    for ($i = 0; $i < 10; $i++) {
        // 随机生成 6 位的数字
        $sn = $prefix.str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        // 查询该模型是否已经存在对应订单号
        $modelName = '\\App\\Models\\'.$model;
        $MODEL = new $modelName;
        if (!$MODEL::query()->where($field, $sn)->exists()) {
            return $sn;
        }
    }
    \Log::warning('生成单号失败-'.$modelName);

    return false;
}

/**
 * 判断是否都是中文
 * @param $str
 * @return int
 */
function isAllChinese($str)
{
    $len = preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $str);
    if ($len) {
        return true;
    }
    return false;
}

/**
 * 格式化数字
 */
function float_number($number)
{
    $length = strlen($number);  //数字长度
    if ($length > 8) { //亿单位
        $str = substr_replace(floor($number * 0.0000001), '.', -1, 0)."亿";
    } elseif ($length > 4) { //万单位
        //截取前俩为
        $str = floor($number * 0.001) * 0.1 ."万";
    } else {
        return $number;
    }
    return $str;
}

/**
 * 二维数组根据某个字段排序
 * @param array $array 要排序的数组
 * @param string $keys 要排序的键字段
 * @param string $sort 排序类型  SORT_ASC     SORT_DESC
 * @return array 排序后的数组
 */
function arraySort($array, $keys, $sort = SORT_DESC)
{
    $keysValue = [];
    foreach ($array as $k => $v) {
        $keysValue[$k] = $v[$keys];
    }
    array_multisort($keysValue, $sort, $array);
    return $array;
}

/**
 * 二分查找法
 * @param $num 数量
 * @param $filter 对应集合
 * @return array
 */
function priceSearch($num, $filter)
{
    if (count($filter) == 1) {
        return $filter;
    }
    $half = floor(count($filter) / 2); // 取出中间数

    // 判断数量在哪个区间
    if ($num < $filter[$half]['number']) {
        $filter = array_slice($filter, 0, $half);
    } else {
        $filter = array_slice($filter, $half, count($filter));
    }
    //print_r($filter);
    // 继续递归直到只剩一个元素
    if (count($filter) > 1) {
        $filter = priceSearch($num, $filter);
    }

    return $filter;
}

/**
 * 获取区域名称
 * @param $id
 * @return mixed
 */
function getAreaName($id)
{
    $area = \App\Models\Area::where('id', $id)->first()->toArray();

    return $area['name'];
}

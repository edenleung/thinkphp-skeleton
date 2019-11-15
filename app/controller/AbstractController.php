<?php

declare(strict_types=1);
/**
 * This file is part of ThinkPHP.
 * @link     https://www.thinkphp.cn
 * @document https://www.kancloud.cn/manual/thinkphp6_0
 * @contact  group@thinkphp.cn
 * @license  https://github.com/top-think/think/blob/6.0/LICENSE.txt
 */

namespace app\controller;

use think\annotation\Inject;
use think\App;
use think\exception\ValidateException;
use think\Request;
use think\Validate;

abstract class AbstractController
{
    /**
     * App应用.
     * @Inject
     * @var App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件.
     * @var array
     */
    protected $middleware = [];

    /**
     * Request实例.
     * @Inject
     * @var Request
     */
    protected $request;

    /**
     * Validate实例.
     * @Inject
     * @var Validate
     */
    protected $validate;

    public function __construct()
    {
        $this->initialize();
    }

    /**
     * 初始化.
     */
    protected function initialize()
    {
    }

    /**
     * 验证数据.
     * @param array $data 数据
     * @param array|string $validate 验证器名或者验证规则数组
     * @param array $message 提示信息
     * @param bool $batch 是否批量验证
     * @throws ValidateException
     * @return array|string|true
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $this->validate->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = strpos($validate, '\\') !== false ? $validate : $this->app->parseClass('validate', $validate);
            $this->validate = new $class();
            if (! empty($scene)) {
                $this->validate->scene($scene);
            }
        }

        $this->validate->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $this->validate->batch(true);
        }

        return $this->validate->failException(true)->check($data);
    }
}

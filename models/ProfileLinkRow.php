<?php
/**
 * 用途：链接编辑行，保存输入和独立的字段错误；不接收客户端父键。
 * 作者：zhixiu-feng
 */

namespace app\models;

use yii\base\Model;

class ProfileLinkRow extends Model
{
    public $id;
    public $title = '';
    public $url = '';
    public $sort_order = 0;
    public $delete = '0';

    public function formName()
    {
        return 'Links';
    }

    public function rules()
    {
        return [
            [['title', 'url'], 'trim'],
            [['title', 'url'], 'required'],
            ['title', 'string', 'max' => 100],
            ['url', 'string', 'max' => 2048],
            ['url', 'url', 'validSchemes' => ['http', 'https']],
            ['sort_order', 'default', 'value' => 0],
            ['sort_order', 'integer', 'min' => 0, 'max' => 4294967295],
        ];
    }

    public function attributeLabels()
    {
        return ['title' => '链接标题', 'url' => '链接地址', 'sort_order' => '顺序', 'delete' => '删除此链接'];
    }

    public function isDeleted(): bool
    {
        return $this->delete === true || $this->delete === 1 || $this->delete === '1';
    }

    public function isBlank(): bool
    {
        return !$this->id && trim((string) $this->title) === '' && trim((string) $this->url) === '';
    }
}

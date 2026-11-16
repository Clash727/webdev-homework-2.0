<?php
/**
 * 用途：按字段白名单接收团队或成员资料，事务保存父资料和显式提交的链接行。
 * 作者：zhixiu-feng
 */

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\web\BadRequestHttpException;

class ProfileEditor extends Model
{
    public ActiveRecord $record;
    /** @var ProfileLinkRow[] */
    public array $rows = [];
    private string $linkClass;
    private string $foreignKey;
    private array $fields;

    public function __construct(ActiveRecord $record, array $config = [])
    {
        if (!($record instanceof ProjectTeam) && !($record instanceof ProjectMember)) {
            throw new \InvalidArgumentException('Unsupported profile type.');
        }
        $this->record = $record;
        $isTeam = $record instanceof ProjectTeam;
        $this->linkClass = $isTeam ? ProjectTeamLink::class : MemberLink::class;
        $this->foreignKey = $isTeam ? 'project_team_id' : 'project_member_id';
        $this->fields = $isTeam ? ['name', 'summary'] : ['display_name', 'role', 'bio', 'sort_order'];
        parent::__construct($config);
        foreach ($this->queryLinks()->all() as $link) {
            $this->rows[] = new ProfileLinkRow($link->getAttributes(['id', 'title', 'url', 'sort_order']));
        }
        $this->appendRow();
    }

    public function appendRow(): void
    {
        $this->rows[] = new ProfileLinkRow();
    }

    public function loadSubmission($profile, $links): void
    {
        if (!is_array($profile) || !is_array($links) || array_diff(array_keys($profile), $this->fields)) {
            throw new BadRequestHttpException('提交包含不允许修改的资料字段。');
        }
        foreach ($profile as $value) {
            if (!is_scalar($value) && $value !== null) {
                throw new BadRequestHttpException('资料字段格式不正确。');
            }
        }
        $this->record->setAttributes($profile);
        $this->rows = [];
        $seen = [];
        foreach (array_values($links) as $data) {
            if (!is_array($data) || array_diff(array_keys($data), ['id', 'title', 'url', 'sort_order', 'delete'])) {
                throw new BadRequestHttpException('链接行格式不正确。');
            }
            foreach ($data as $value) {
                if (!is_scalar($value) && $value !== null) {
                    throw new BadRequestHttpException('链接字段格式不正确。');
                }
            }
            $row = new ProfileLinkRow($data);
            if ($row->id !== null && $row->id !== '') {
                if (!preg_match('/^[1-9][0-9]{0,9}$/D', (string) $row->id) || (float) $row->id > 4294967295
                    || isset($seen[(string) $row->id])) {
                    throw new BadRequestHttpException('链接标识无效或重复。');
                }
                $seen[(string) $row->id] = true;
            }
            if (!in_array($row->delete, ['0', '1', 0, 1, false, true], true)) {
                throw new BadRequestHttpException('删除标记格式不正确。');
            }
            $this->rows[] = $row;
        }
        if ($this->rows === []) {
            $this->appendRow();
        }
    }

    public function save(): bool
    {
        $db = $this->record->getDb();
        $transaction = $db->beginTransaction();
        try {
            $ownerExists = $db->createCommand('SELECT id FROM ' . $this->record::tableName() . ' WHERE id=:id FOR UPDATE',
                [':id' => $this->record->id])->queryScalar();
            if ($ownerExists === false) {
                $this->addError('record', '资料已经变化，请刷新后重试。');
                $transaction->rollBack();
                return false;
            }
            $db->createCommand('SELECT id FROM ' . $this->linkClass::tableName() . ' WHERE [[' . $this->foreignKey . ']]=:owner FOR UPDATE',
                [':owner' => $this->record->id])->queryColumn();
            $existing = $this->queryLinks()->indexBy('id')->all();
            $valid = $this->record->validate();
            foreach ($this->rows as $row) {
                if ($row->id && !isset($existing[$row->id])) {
                    throw new BadRequestHttpException('链接不属于当前资料，或已被删除。');
                }
                if (!$row->isDeleted() && !$row->isBlank()) {
                    $valid = $row->validate() && $valid;
                }
            }
            if (!$valid) {
                $transaction->rollBack();
                return false;
            }
            if (!$this->record->save(false)) {
                $this->addError('record', '资料保存失败，未作任何修改。');
                $transaction->rollBack();
                return false;
            }
            foreach ($this->rows as $row) {
                if ($row->isDeleted()) {
                    if ($row->id) { $existing[$row->id]->delete(); }
                    continue;
                }
                if ($row->isBlank()) { continue; }
                $link = $row->id ? $existing[$row->id] : new $this->linkClass();
                $link->{$this->foreignKey} = $this->record->id;
                $link->title = $row->title;
                $link->url = $row->url;
                $link->sort_order = $row->sort_order;
                if (!$link->save()) {
                    foreach ($link->getErrors() as $attribute => $messages) {
                        foreach ($messages as $message) { $row->addError($attribute, $message); }
                    }
                    $transaction->rollBack();
                    return false;
                }
            }
            $transaction->commit();
            return true;
        } catch (\Throwable $error) {
            if ($transaction->isActive) { $transaction->rollBack(); }
            if ($error instanceof \yii\db\Exception) {
                Yii::error('Profile save failed: ' . $error->getMessage(), __METHOD__);
                $this->addError('record', '保存失败，资料未修改，请稍后重试。');
                return false;
            }
            throw $error;
        }
    }

    public function errorModels(): array
    {
        return array_merge([$this, $this->record], $this->rows);
    }

    private function queryLinks()
    {
        return $this->linkClass::find()->where([$this->foreignKey => $this->record->id])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }
}

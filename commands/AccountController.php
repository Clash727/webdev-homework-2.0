<?php
/**
 * 用途：交互式创建管理员，凭证仅经终端输入，重复账号不覆盖。
 * 作者：Clash727（W05）
 */

namespace app\commands;

use app\models\Account;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class AccountController extends Controller
{
    public function actionCreateAdmin(): int
    {
        $account = new Account();
        $account->username = Console::input('用户名：');
        $account->display_name = Console::input('展示名：');
        $account->auth_key = Yii::$app->security->generateRandomString(32);

        $password = $this->readPassword('密码（8–72 字节）：');
        $confirmation = $this->readPassword('再次输入密码：');
        if (strlen($password) < 8 || strlen($password) > 72 || str_contains($password, "\0")) {
            $this->stderr("密码须为 8–72 字节，不能包含空字节。\n");
            return ExitCode::DATAERR;
        }
        if ($password !== $confirmation) {
            $this->stderr("两次密码不一致，未创建账号。\n");
            return ExitCode::DATAERR;
        }

        $account->setPassword($password);
        if (!$account->save()) {
            $this->stderr(implode(PHP_EOL, $account->getFirstErrors()) . PHP_EOL);
            return ExitCode::DATAERR;
        }

        $this->stdout('管理员已创建：' . $account->username . PHP_EOL);
        return ExitCode::OK;
    }

    private function readPassword(string $prompt): string
    {
        $this->stderr($prompt);
        if (PHP_OS_FAMILY === 'Windows') {
            $script = '[Console]::OutputEncoding = [Text.UTF8Encoding]::new($false); '
                . '$secret = Read-Host -AsSecureString; '
                . '$pointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secret); '
                . 'try { [Console]::Out.Write([Runtime.InteropServices.Marshal]::PtrToStringBSTR($pointer)) } '
                . 'finally { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($pointer) }';
            $process = proc_open(['powershell.exe', '-NoProfile', '-Command', $script],
                [0 => STDIN, 1 => ['pipe', 'w'], 2 => STDERR], $pipes);
            $password = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            proc_close($process);
        } else {
            $settings = rtrim(shell_exec('stty -g'));
            shell_exec('stty -echo');
            try {
                $password = rtrim(fgets(STDIN), "\r\n");
            } finally {
                shell_exec('stty ' . escapeshellarg($settings));
            }
        }
        $this->stderr(PHP_EOL);
        return $password;
    }
}

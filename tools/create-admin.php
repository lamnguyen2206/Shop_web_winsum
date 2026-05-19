<?php
/**
 * Tạo / cập nhật tài khoản admin trong config/admin.php
 *
 * CLI:  php tools/create-admin.php [username] [password]
 * VD:   php tools/create-admin.php admin admin123
 */
declare(strict_types=1);

$username = trim($argv[1] ?? 'admin');
$password = (string) ($argv[2] ?? 'admin123');

if ($username === '' || $password === '') {
    fwrite(STDERR, "Usage: php tools/create-admin.php <username> <password>\n");
    exit(1);
}

if (strlen($password) < 6) {
    fwrite(STDERR, "Mật khẩu phải có ít nhất 6 ký tự.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$configPath = dirname(__DIR__) . '/config/admin.php';

$content = <<<PHP
<?php
/**
 * Tài khoản quản trị — không commit file này lên repo công khai.
 * Tạo lại: php tools/create-admin.php <username> <password>
 */
return [
    'username' => %s,
    'password_hash' => %s,
];

PHP;

$fileBody = sprintf(
    $content,
    var_export($username, true),
    var_export($hash, true)
);

if (file_put_contents($configPath, $fileBody) === false) {
    fwrite(STDERR, "Không ghi được file: {$configPath}\n");
    exit(1);
}

echo "Đã tạo tài khoản admin.\n";
echo "  File: config/admin.php\n";
echo "  User: {$username}\n";
echo "  Pass: {$password}\n";
echo "  Đăng nhập: index.php?view=admin-login\n";

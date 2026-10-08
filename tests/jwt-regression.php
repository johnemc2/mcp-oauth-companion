<?php
/** Standalone JWT regression tests. Run: php tests/jwt-regression.php */
declare(strict_types=1);
function wp_json_encode($value) { return json_encode($value, JSON_THROW_ON_ERROR); }
require dirname(__DIR__) . '/inc/Auth/JWT.php';
use WPMedia\MCP\OAuth\Auth\JWT;
$secret = 'local-test-key-not-for-production';
$ok = 0;
function check(bool $condition, string $name): void {
    global $ok;
    if (!$condition) { fwrite(STDERR, "FAIL: $name\n"); exit(1); }
    $ok++;
    echo "PASS: $name\n";
}
$valid = JWT::encode(['sub'=>'user-1','exp'=>time()+60],$secret);
check(JWT::decode($valid,$secret)['sub']==='user-1','valid signed token');
check(JWT::decode($valid,'wrong-key')===null,'wrong signature rejected');
check(JWT::decode($valid.'x',$secret)===null,'tampered signature rejected');
check(JWT::decode('invalid',$secret)===null,'malformed token rejected');
$expired = JWT::encode(['exp'=>time()-60],$secret);
check(JWT::decode($expired,$secret)===null,'expired token rejected');
check(is_array(JWT::decode($expired,$secret,false)),'expiry bypass explicitly supported for revocation');
$noexp = JWT::encode(['sub'=>'user-1'],$secret);
check(is_array(JWT::decode($noexp,$secret)),'KNOWN LIMITATION: decoder accepts missing exp; enforcement must be at transport boundary');
echo "Passed $ok tests. WordPress integration and authorization tests still required.\n";

<?php
/** Standalone JWT security regression checks: php tests/jwt-security.php */
declare(strict_types=1);
function wp_json_encode($value) { return json_encode($value, JSON_THROW_ON_ERROR); }
require dirname(__DIR__) . '/inc/Auth/JWT.php';
use WPMedia\MCP\OAuth\Auth\JWT;
$secret = 'test-secret-not-for-production';
$failures = 0;
function assert_claim(bool $pass, string $name): void {
    global $failures;
    echo ($pass ? 'PASS ' : 'FAIL ') . $name . PHP_EOL;
    if (!$pass) { ++$failures; }
}
$now = time();
$valid = JWT::encode(['sub'=>'42','exp'=>$now + 300], $secret);
assert_claim(is_array(JWT::decode($valid,$secret)), 'valid token');
assert_claim(null === JWT::decode($valid,'incorrect'), 'wrong key');
assert_claim(null === JWT::decode($valid.'x',$secret), 'tampered signature');
assert_claim(null === JWT::decode('not.a.jwt',$secret), 'malformed token');
$expired = JWT::encode(['exp'=>$now - 60],$secret);
assert_claim(null === JWT::decode($expired,$secret), 'expired token');
assert_claim(is_array(JWT::decode($expired,$secret,false)), 'revocation can inspect expired token');
assert_claim(null === JWT::decode(JWT::encode(['sub'=>'42'],$secret),$secret), 'missing expiration rejected');
assert_claim(null === JWT::decode(JWT::encode(['exp'=>'tomorrow'],$secret),$secret), 'noninteger expiration rejected');
assert_claim(null === JWT::decode(JWT::encode(['exp'=>$now],$secret),$secret), 'expiration at current second rejected');
exit($failures ? 1 : 0);

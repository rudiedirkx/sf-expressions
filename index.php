<?php

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

require 'vendor/autoload.php';

header('Content-type: text/plain; charset=utf-8');

class User {
	public $admin;
	public $name;
	public $age;
	public $numPerms;
	function __construct($name, $age, $numPerms) {
		$this->admin = rand(0, 1) == 0;
		$this->name = $name;
		$this->age = $age;
		$this->numPerms = $numPerms;
	}
	function __get($name) {
		return $this->$name = call_user_func([$this, "get_$name"]);
	}
	function get_permissions() {
		usleep(100000);
		return array_map(function() {
			return rand();
		}, array_fill(0, $this->numPerms, 0));
	}
	function get_num_permissions() {
		return count($this->permissions);
	}
	function __toString() {
		return $this->name;
	}
}

$user = new User('Jeff', 15, 13);
print_r($user);

echo "\n";

$_time = microtime(1);

$expression = 'user.admin or (user.num_permissions > 0 and user.num_permissions < 3243 and user.age in ages)';
$params = [
	'user' => $user,
	'ages' => [10, 15, 20],
];

$language = new ExpressionLanguage();
var_dump($language->evaluate($expression, $params));

echo "\n" . number_format((microtime(1) - $_time) * 1000, 1) . " ms (with 100 ms sleep?)\n\n";

// print_r($language->parse($expression, array_keys($params)));
var_dump($language->compile($expression, array_keys($params)));

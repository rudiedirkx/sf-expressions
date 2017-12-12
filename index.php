<?php

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

require 'vendor/autoload.php';

ini_set('html_errors', 0);
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
	function get_group() {
		return new Group('Whatevers', new Source(!$this->admin));
	}
	function __toString() {
		return $this->name;
	}
}

class Group {
	public $name;
	public $source;
	function __construct($name, Source $source) {
		$this->name = $name;
		$this->source = $source;
	}
}

class Source {
	public $editable;
	function __construct($editable) {
		$this->editable = $editable;
	}
}

$user = new User('Jeff', 15, 13);
print_r($user);

echo "\n";

$_time = microtime(1);

$expression = '
	user.group.name matches "/^[A-Z]/" and
	(
		user.group.source.editable or
		user.admin or
		(user.num_permissions > 0 and user.num_permissions < 3243 and user.age in ages)
	)
';
$params = [
	'user' => $user,
	'ages' => [10, 15, 20],
];

$language = new ExpressionLanguage();

var_dump($language->evaluate($expression, $params));

echo "\n" . number_format((microtime(1) - $_time) * 1000, 1) . " ms (with 100 ms sleep?)\n\n";
$_time = microtime(1);

// print_r($language->parse($expression, array_keys($params)));
var_dump($language->compile($expression, array_keys($params)));

echo "\n" . number_format((microtime(1) - $_time) * 1000, 1) . " ms (with 100 ms sleep?)\n\n";

echo "\n\n\n\n\n\n";



//$language = new ExpressionLanguage();
//
//$expression = 'x contains y';
//$params = ['x' => "James O'brien", 'y' => "'"];
//var_dump($language->evaluate($expression, $params));
//var_dump($language->compile($expression, array_keys($params)));

//echo "\n\n\n\n\n\n";



$language = new ExpressionLanguage();
$language->register('contains', function($haystack, $needle) {
    return sprintf('is_int(strpos(%s, %s))', $haystack, $needle);
}, function($args, $haystack, $needle) {
    return is_int(strpos($haystack, $needle));
});

$expression = 'contains(x, y) or contains(y, x)';
$params = ['x' => "abc", 'y' => "b"];
var_dump($language->evaluate($expression, $params));
var_dump($language->compile($expression, array_keys($params)));

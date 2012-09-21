<?php

function apiAuth($token=null,$token_type='slave_token'){
	if(!$token) throw new Exception('API Auth Failure, missing token');
	if($token != Config::get('api',$token_type)) throw new Exception('API Auth Failure, invalid token');
	return true;
}

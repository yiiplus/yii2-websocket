<?php

class UserController extends UserController
{
	public function actionLogin()
	{
		...
		$websocketClient = \Yii::$app->websocket;
		$websocketClient->connect();
		$websocketClient->send('xxx 登陆成功！');
	}
}